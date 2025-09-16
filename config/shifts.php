<?php
return [
    'timezone' => 'Asia/Jakarta',

    // Atur 2 shift: [start, end]
    'windows'  => [
        1 => ['start' => '06:00', 'end' => '10:00'],
        2 => ['start' => '01:00', 'end' => '04:00'],
    ],

    // toleransi keterlambatan (menit)
    'grace_minutes' => 0,
];
