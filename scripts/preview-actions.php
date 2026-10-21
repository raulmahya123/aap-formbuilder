<?php
$argv = [__FILE__];
require __DIR__.'/check-ui.php';
$source = <<<'BLADE'
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">@vite(['resources/css/app.css','resources/js/app.js'])<title>Pratinjau aksi Mandala</title></head>
<body class="ui-shell"><main>
<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Standar aksi tabel</x-ui.page-title><x-ui.page-description>Pratinjau komponen bersama menggunakan data contoh.</x-ui.page-description></div><x-ui.page-actions><x-ui.button variant="primary" icon="plus" type="button">Tambah data</x-ui.button></x-ui.page-actions></x-ui.page-header>
<x-ui.table><thead><tr><th>Contoh data</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<tr><td>Dokumen operasional</td><td><x-ui.badge variant="success">Aktif</x-ui.badge></td><td><x-ui.table-actions><x-ui.icon-button action="view" href="#view"/><x-ui.icon-button action="edit" href="#edit"/><form method="POST" action="#preview-only" data-confirm="Hapus dokumen contoh ini?"><input type="hidden" name="_method" value="DELETE"><x-ui.icon-button action="delete" type="submit"/></form></x-ui.table-actions></td></tr>
<tr><td>Contoh pengguna</td><td><x-ui.badge>Nonaktif</x-ui.badge></td><td><x-ui.table-actions><x-ui.icon-button action="activate"/><x-ui.icon-button action="deactivate"/><x-ui.icon-button action="reset-password" data-reset-url="#preview-only" data-reset-user="Contoh Pengguna"/></x-ui.table-actions></td></tr>
<tr><td>Lima aksi tersedia</td><td><x-ui.badge variant="info">Dokumen</x-ui.badge></td><td><x-ui.table-actions><x-ui.icon-button action="view" href="#view"/><x-ui.icon-button action="edit" href="#edit"/><x-ui.icon-button action="duplicate"/><x-ui.icon-button action="download" href="#download"/><x-ui.icon-button action="history"/></x-ui.table-actions></td></tr>
<tr><td>Department</td><td><x-ui.badge>Operasional</x-ui.badge></td><td><x-ui.table-actions><x-ui.icon-button action="members"/><x-ui.icon-button action="assign"/><x-ui.icon-button action="upload"/><x-ui.icon-button id="denied-action" action="delete" ability="ui-preview-denied"/></x-ui.table-actions></td></tr>
</tbody></x-ui.table>
<x-ui.action-dialogs />
</main></body></html>
BLADE;
$html = Illuminate\Support\Facades\Blade::render($source);
if (str_contains($html, 'id="denied-action"')) throw new RuntimeException('A denied action was rendered.');
if (substr_count($html, 'class="ui-action-menu"') !== 1) throw new RuntimeException('Overflow was not generated for five actions.');
file_put_contents(__DIR__.'/../public/__ui-actions-preview.html', $html);
echo "Rendered action preview; denied action hidden and overflow generated.".PHP_EOL;
