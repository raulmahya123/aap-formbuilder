<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SapRouteReferencesTest extends TestCase
{
    public function test_sap_links_and_redirects_use_registered_routes(): void
    {
        $files = [resource_path('views/layouts/navigation.blade.php')];
        foreach ([resource_path('views/hse'), app_path('Http/Controllers/Hse')] as $directory) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) $files[] = $file->getPathname();
            }
        }
        $checked = 0;
        foreach ($files as $file) {
            $source = file_get_contents($file);
            $this->assertStringNotContainsString('hse.sap.', $source, $file);
            preg_match_all('/[\x22\x27](hse\.dashboard\.sap\.[a-z_.]+)[\x22\x27]/', $source, $matches);
            foreach (array_unique($matches[1]) as $name) {
                $this->assertTrue(Route::has($name), "Unregistered route {$name} in {$file}");
                $checked++;
            }
        }
        $this->assertGreaterThan(30, $checked);
    }
}
