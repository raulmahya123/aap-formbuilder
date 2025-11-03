<?php

return [
    // Zona waktu perhitungan shift
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    // Daftar window shift (boleh lintas hari; contoh ini dua shift harian)
    // Kunci integer bebas (1,2,3,...)
    'windows'  => [
        1 => ['start' => '06:00', 'end' => '10:00'],
        2 => ['start' => '01:00', 'end' => '04:00'],
    ],

    // Toleransi menit di tepi window (ditambahkan ke start/end)
    'grace_minutes' => (int) env('SHIFT_GRACE_MINUTES', 0),
];
