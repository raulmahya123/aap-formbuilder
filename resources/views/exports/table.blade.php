<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>{{ $title }}</title>
<style>
@page { margin: 28px; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #18181b; }
.brand { font-size: 14px; font-weight: bold; color: #713f12; }
h1 { font-size: 17px; margin: 12px 0 6px; }
p { color: #52525b; margin: 0 0 16px; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
thead { display: table-header-group; }
th, td { border: 1px solid #d4d4d8; padding: 6px; text-align: left; vertical-align: top; overflow-wrap: break-word; word-wrap: break-word; }
th { background: #f4f4f5; font-weight: bold; }
tr { page-break-inside: avoid; }
</style></head><body>
<div class="brand">MANDALA</div>
<h1>{{ $title }}</h1>
<p>Data pada halaman aktif · {{ count($rows) }} baris · Dicetak {{ $exportedAt->format('d/m/Y H:i') }}</p>
<table><thead><tr>@foreach($columns as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
<tbody>@foreach($rows as $row)<tr>@foreach($row as $value)<td>{{ $value ?? '—' }}</td>@endforeach</tr>@endforeach</tbody></table>
</body></html>
