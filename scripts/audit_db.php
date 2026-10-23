<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== DATABASE CONNECTION ===\n";
echo "DB: " . config('database.default') . "\n";
echo "Database name: " . DB::connection()->getDatabaseName() . "\n\n";

echo "=== TABLES ===\n";
$tables = DB::select('SHOW TABLES');
$tableNames = [];
foreach ($tables as $table) {
    $name = array_values((array)$table)[0];
    $tableNames[] = $name;
    echo "- $name\n";
}

echo "\n=== FORMS IN DATABASE ===\n";
if (Schema::hasTable('forms')) {
    $forms = DB::table('forms')->select('id', 'name', 'slug', 'is_published', 'created_at')->get();
    foreach ($forms as $f) {
        $subCount = Schema::hasTable('form_submissions') ? DB::table('form_submissions')->where('form_id', $f->id)->count() : 0;
        echo "ID: {$f->id} | Name: {$f->name} | Slug: {$f->slug} | Published: {$f->is_published} | Submissions: {$subCount}\n";
    }
} else {
    echo "No 'forms' table found!\n";
}
