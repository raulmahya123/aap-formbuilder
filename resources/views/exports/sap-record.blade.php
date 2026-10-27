<!doctype html><html lang="id"><head><meta charset="utf-8"><title>{{ $number }} — {{ $title }}</title>
<style>
@page { margin: 28px; } body { font: 11px 'DejaVu Sans', Arial, sans-serif; color: #18181b; }
.brand { font-size: 18px; font-weight: bold; color: #713f12; } h1 { font-size: 20px; margin-bottom: 6px; }
h2 { font-size: 14px; margin-top: 24px; } p { color: #52525b; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 16px; }
thead { display: table-header-group; } tr { page-break-inside: avoid; }
td, th { padding: 8px; border: 1px solid #d4d4d8; text-align: left; vertical-align: top; word-wrap: break-word; white-space: pre-line; }
th { background: #f4f4f5; } .label { width: 28%; } .tools { display: flex; justify-content: flex-end; margin: 16px 0; }
.tools button { padding: 10px 16px; background: #1d4ed8; color: white; border: 0; border-radius: 8px; cursor: pointer; }
@media print { .tools { display: none; } }
</style></head><body>
@if($format === 'print')<div class="tools"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>@endif
<div class="brand">MANDALA</div><h1>{{ $title }}</h1><p>{{ $number }} · Dicetak {{ now()->format('d/m/Y H:i') }}</p>
<table><tbody>@foreach($details as $label=>$value)<tr><th class="label">{{ $label }}</th><td>{{ $value ?? '—' }}</td></tr>@endforeach</tbody></table>
@foreach($sections as $section)
<h2>{{ $section['title'] }}</h2>
<table><thead><tr>@foreach($section['columns'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead><tbody>
@forelse($section['rows'] as $row)<tr>@foreach($row as $value)<td>{{ $value ?? '—' }}</td>@endforeach</tr>
@empty<tr><td colspan="{{ count($section['columns']) }}">Belum ada data.</td></tr>@endforelse
</tbody></table>
@endforeach
</body></html>
