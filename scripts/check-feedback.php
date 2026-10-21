<?php
// Render representative feedback states without reading or writing business records.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app['view']->share('errors', new Illuminate\Support\ViewErrorBag);
foreach (['success' => 'Data berhasil disimpan.', 'error' => 'Data gagal disimpan. Periksa koneksi lalu coba lagi.', 'warning' => 'Periksa kembali data sebelum melanjutkan.', 'info' => 'Filter telah diperbarui.'] as $type => $message) session()->flash($type, $message);
$content = <<<'BLADE'
<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Feedback Mandala</x-ui.page-title><x-ui.page-description>Pemeriksaan komponen bersama dengan data contoh.</x-ui.page-description></div></x-ui.page-header>
<div class="grid gap-5 md:grid-cols-2">
<x-ui.panel><x-ui.section-title>Validasi formulir</x-ui.section-title><form class="mt-5 space-y-5" action="#" method="GET"><x-ui.form-field name="sample_name" label="Nama" required helper="Contoh isian wajib."><x-ui.form-input id="sample_name" name="sample_name" required /></x-ui.form-field><x-ui.form-field name="sample_email" label="Email" required><x-ui.form-input id="sample_email" name="sample_email" type="email" required /></x-ui.form-field><x-ui.button type="submit" variant="primary">Periksa formulir</x-ui.button></form></x-ui.panel>
<x-ui.panel><x-ui.section-title>Memuat data</x-ui.section-title><x-ui.form-skeleton class="mt-5" :fields="2" /></x-ui.panel>
<x-ui.panel><x-ui.empty-state kind="search" /></x-ui.panel><x-ui.panel><x-ui.error-state kind="network" retry-url="#" /></x-ui.panel>
</div><x-ui.toast-provider />
BLADE;
$html = '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'.Illuminate\Support\Facades\Vite::useBuildDirectory('build')(['resources/css/app.css', 'resources/js/app.js']).'</head><body><main class="p-6 max-w-6xl mx-auto">'.Illuminate\Support\Facades\Blade::render($content).'</main></body></html>';
foreach (['ui-toast--success', 'ui-toast--error', 'ui-toast--warning', 'ui-toast--info', 'ui-skeleton-form', 'Pencarian tidak ditemukan', 'Koneksi terputus'] as $expected) {
    if (!str_contains($html, $expected)) throw new RuntimeException('Missing feedback state: '.$expected);
}
if (in_array('--preview', $argv, true)) file_put_contents(public_path('__ui-feedback-preview.html'), $html);
echo 'Feedback components rendered successfully.'.PHP_EOL;
