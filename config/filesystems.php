<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        // === default local (biasa untuk internal laravel) ===
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        // === disk private khusus kontrak / dokumen sensitif ===
        'private' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'visibility' => 'private',
            'throw' => false,
        ],

        // === public untuk file yang bisa diakses via URL ===
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // === upload Mandala/FormBuilder ke NAS via FTP ===
        'mandala_uploads' => [
            'driver' => env('MANDALA_DISK_DRIVER', 'local'),
            'host' => env('NAS_FTP_HOST', '160.19.165.217'),
            'port' => (int) env('NAS_FTP_PORT', 21),
            'username' => env('NAS_FTP_USER', 'artha'),
            'password' => env('NAS_FTP_PASS', 'OT6gfpTp'),
            'root' => env('NAS_FTP_ROOT', '/uploads'),
            'visibility' => 'private',
            'timeout' => 30,
            'passive' => true,
            'ssl' => false,
            'throw' => false,
        ],

        // === local fallback (for dev / offline) ===
        'mandala_local' => [
            'driver' => 'local',
            'root' => env('UPLOAD_DIR', storage_path('app/public')),
            'visibility' => 'private',
            'throw' => false,
        ],

        // === AWS S3 (opsional) ===
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
