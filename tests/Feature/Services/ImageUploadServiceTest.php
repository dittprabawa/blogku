<?php

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('gambar yang lebih besar dari batas maksimal di-resize turun', function () {
    $service = new ImageUploadService(maxWidth: 800, maxHeight: 800, quality: 80);

    $file = UploadedFile::fake()->image('foto.jpg', 3000, 2000);

    $path = $service->store($file, 'posts');

    Storage::disk('public')->assertExists($path);

    $contents = Storage::disk('public')->get($path);
    [$width, $height] = getimagesizefromstring($contents);

    expect($width)->toBeLessThanOrEqual(800)
        ->and($height)->toBeLessThanOrEqual(800);
});

test('rasio aspek gambar tetap terjaga setelah di-resize', function () {
    $service = new ImageUploadService(maxWidth: 800, maxHeight: 800, quality: 80);

    // rasio asli 3000:1500 = 2:1
    $file = UploadedFile::fake()->image('foto.jpg', 3000, 1500);

    $path = $service->store($file, 'posts');
    $contents = Storage::disk('public')->get($path);
    [$width, $height] = getimagesizefromstring($contents);

    expect(round($width / $height, 1))->toBe(2.0);
});

test('gambar yang sudah lebih kecil dari batas tidak diperbesar', function () {
    $service = new ImageUploadService(maxWidth: 1600, maxHeight: 1600, quality: 80);

    $file = UploadedFile::fake()->image('kecil.jpg', 200, 150);

    $path = $service->store($file, 'posts');
    $contents = Storage::disk('public')->get($path);
    [$width, $height] = getimagesizefromstring($contents);

    expect($width)->toBe(200)
        ->and($height)->toBe(150);
});

test('file disimpan di direktori yang diminta dengan disk yang benar', function () {
    $service = new ImageUploadService;

    $file = UploadedFile::fake()->image('foto.jpg', 500, 500);

    $path = $service->store($file, 'posts');

    expect($path)->toStartWith('posts/');
    Storage::disk('public')->assertExists($path);
});

test('replace menghapus gambar lama dan menyimpan gambar baru', function () {
    $service = new ImageUploadService;

    $old = $service->store(UploadedFile::fake()->image('lama.jpg', 500, 500), 'posts');
    Storage::disk('public')->assertExists($old);

    $new = $service->store(UploadedFile::fake()->image('baru.jpg', 500, 500), 'posts');
    $path = $service->replace($old, UploadedFile::fake()->image('pengganti.jpg', 500, 500), 'posts');

    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($path);
    expect($path)->not->toBe($old);

    // memastikan store lain (tidak terkait replace ini) tidak ikut kehapus
    Storage::disk('public')->assertExists($new);
});

test('delete menghapus file dari disk', function () {
    $service = new ImageUploadService;

    $path = $service->store(UploadedFile::fake()->image('foto.jpg', 400, 400), 'posts');
    Storage::disk('public')->assertExists($path);

    $service->delete($path);

    Storage::disk('public')->assertMissing($path);
});

test('delete aman dipanggil dengan path null tanpa error', function () {
    $service = new ImageUploadService;

    $service->delete(null);

    expect(true)->toBeTrue();
});

test('gambar png tetap ekstensi png setelah diproses', function () {
    $service = new ImageUploadService(maxWidth: 800, maxHeight: 800);

    $file = UploadedFile::fake()->image('foto.png', 1000, 900);

    $path = $service->store($file, 'posts');

    expect($path)->toEndWith('.png');

    $contents = Storage::disk('public')->get($path);
    [, , $type] = getimagesizefromstring($contents);
    expect($type)->toBe(IMAGETYPE_PNG);
});
