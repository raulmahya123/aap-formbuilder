<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $configPath = config_path('shifts.php');

        if (! file_exists($configPath)) {
            file_put_contents($configPath, $this->defaultConfig());
        }
    }

    private function defaultConfig(): string
    {
        return <<<'PHP'
<?php

return [
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'windows' => [
        1 => ['start' => '06:00', 'end' => '10:00'],
        2 => ['start' => '01:00', 'end' => '04:00'],
    ],
    'grace_minutes' => (int) env('SHIFT_GRACE_MINUTES', 0),
];
PHP;
    }
}
