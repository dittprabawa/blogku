<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menangani upload gambar: resize (kalau melebihi ukuran maksimal),
 * kompres, lalu simpan ke disk. Dipakai oleh controller mana pun yang
 * punya field upload gambar (misalnya featured image post), supaya
 * logikanya nggak diduplikasi di banyak tempat dan gampang di-test.
 *
 * Gambar yang ukurannya sudah di bawah batas TIDAK diperbesar, cuma
 * di-kompres ulang. Kalau ekstensi GD nggak tersedia di server, service
 * ini fallback ke menyimpan file asli apa adanya supaya upload tetap
 * jalan (nggak sampai error 500).
 */
class ImageUploadService
{
    protected int $maxWidth;

    protected int $maxHeight;

    protected int $quality;

    public function __construct(?int $maxWidth = null, ?int $maxHeight = null, ?int $quality = null)
    {
        $this->maxWidth = $maxWidth ?? (int) config('images.max_width', 1600);
        $this->maxHeight = $maxHeight ?? (int) config('images.max_height', 1600);
        $this->quality = $quality ?? (int) config('images.quality', 80);
    }

    /**
     * Resize & optimasi gambar lalu simpan ke disk. Mengembalikan path
     * relatif (disimpan ke kolom DB), sama seperti UploadedFile::store().
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $path = trim($directory, '/').'/'.Str::random(40).'.'.$this->targetExtension($file);

        Storage::disk($disk)->put($path, $this->processedContents($file));

        return $path;
    }

    /**
     * Hapus gambar lama (kalau ada) lalu simpan gambar baru. Ini pola yang
     * paling sering dipakai di form update (ganti gambar lama dengan baru).
     */
    public function replace(?string $oldPath, UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $this->delete($oldPath, $disk);

        return $this->store($file, $directory, $disk);
    }

    /**
     * Hapus gambar dari disk. Aman dipanggil walau path-nya null/kosong.
     */
    public function delete(?string $path, string $disk = 'public'): void
    {
        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Proses resize + kompresi, kembalikan isi file (binary string) yang
     * siap ditulis ke disk.
     */
    protected function processedContents(UploadedFile $file): string
    {
        if (! $this->gdAvailable()) {
            return (string) file_get_contents($file->getRealPath());
        }

        $imageInfo = @getimagesize($file->getRealPath());

        if ($imageInfo === false) {
            return (string) file_get_contents($file->getRealPath());
        }

        [$originalWidth, $originalHeight, $type] = $imageInfo;

        $source = $this->createResource($file->getRealPath(), $type);

        if (! $source) {
            return (string) file_get_contents($file->getRealPath());
        }

        $source = $this->applyExifOrientation($source, $file, $type);

        [$targetWidth, $targetHeight] = $this->targetDimensions(
            imagesx($source),
            imagesy($source)
        );

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        // Jaga transparansi untuk PNG/GIF/WebP supaya nggak jadi background hitam.
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            imagesx($source),
            imagesy($source)
        );

        $contents = $this->encode($canvas, $type);

        imagedestroy($source);
        imagedestroy($canvas);

        return $contents;
    }

    /**
     * Hitung dimensi target: downscale kalau lebih besar dari batas,
     * tapi jangan pernah upscale gambar yang sudah lebih kecil.
     *
     * @return array{0: int, 1: int}
     */
    protected function targetDimensions(int $width, int $height): array
    {
        if ($width <= $this->maxWidth && $height <= $this->maxHeight) {
            return [$width, $height];
        }

        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }

    /**
     * @return \GdImage|false
     */
    protected function createResource(string $path, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    /**
     * JPEG dari kamera/HP sering punya EXIF orientation (foto kepotret
     * miring/kesamping tanpa ini). Putar otomatis biar tampil tegak.
     *
     * @param  \GdImage  $source
     * @return \GdImage
     */
    protected function applyExifOrientation($source, UploadedFile $file, int $type)
    {
        if ($type !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $source;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = $exif['Orientation'] ?? null;

        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => null,
        };

        if ($angle === null) {
            return $source;
        }

        $rotated = imagerotate($source, $angle, 0);
        imagedestroy($source);

        return $rotated;
    }

    /**
     * @param  \GdImage  $resource
     */
    protected function encode($resource, int $type): string
    {
        ob_start();

        match ($type) {
            IMAGETYPE_PNG => imagepng($resource, null, 6),
            IMAGETYPE_GIF => imagegif($resource),
            IMAGETYPE_WEBP => function_exists('imagewebp')
                ? imagewebp($resource, null, $this->quality)
                : imagejpeg($resource, null, $this->quality),
            default => imagejpeg($resource, null, $this->quality),
        };

        return (string) ob_get_clean();
    }

    protected function targetExtension(UploadedFile $file): string
    {
        $type = (@getimagesize($file->getRealPath()))[2] ?? null;

        return match ($type) {
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
            IMAGETYPE_WEBP => 'webp',
            IMAGETYPE_JPEG => 'jpg',
            default => strtolower($file->getClientOriginalExtension()) ?: 'jpg',
        };
    }

    protected function gdAvailable(): bool
    {
        return function_exists('imagecreatetruecolor') && extension_loaded('gd');
    }
}
