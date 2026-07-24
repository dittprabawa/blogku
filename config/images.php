<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi resize & optimasi gambar upload
    |--------------------------------------------------------------------------
    |
    | Dipakai oleh App\Services\ImageUploadService setiap kali ada gambar
    | yang diupload (misal featured image post). Gambar yang lebih besar
    | dari max_width/max_height akan di-downscale sambil mempertahankan
    | rasio aspek (gambar yang lebih kecil TIDAK akan diperbesar), lalu
    | di-encode ulang dengan tingkat kompresi "quality" untuk memperkecil
    | ukuran file.
    |
    */

    'max_width' => (int) env('IMAGE_MAX_WIDTH', 1600),

    'max_height' => (int) env('IMAGE_MAX_HEIGHT', 1600),

    // Kualitas kompresi 0-100, dipakai untuk JPEG & WebP.
    'quality' => (int) env('IMAGE_QUALITY', 80),

];
