<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== form_entries columns ===\n";
foreach (DB::select('SHOW COLUMNS FROM form_entries') as $c) {
    echo "  {$c->Field} ({$c->Type})\n";
}

echo "\n=== All tables ===\n";
foreach (DB::select('SHOW TABLES') as $row) {
    echo "  " . reset($row) . "\n";
}
