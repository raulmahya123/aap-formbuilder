<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — MANDALA</title>
    <style>
        @page { margin: 22px 24px; }
        body { color: #18181b; font-family: 'DejaVu Sans', sans-serif; font-size: 9px; }
        .brand { color: #713f12; font-size: 12px; font-weight: 700; letter-spacing: .8px; }
        h1 { font-size: 20px; margin: 8px 0 4px; }
        .subtitle { color: #52525b; font-size: 10px; margin: 0 0 12px; }
        .meta { border: 1px solid #d4d4d8; border-collapse: collapse; margin: 0 0 16px; width: 100%; }
        .meta th, .meta td { padding: 6px 8px; text-align: left; }
        .meta th { background: #f4f4f5; font-weight: 600; width: 90px; }
        h2 { border-bottom: 1px solid #d4d4d8; font-size: 12px; margin: 18px 0 6px; padding-bottom: 5px; }
        table.data { border-collapse: collapse; table-layout: auto; width: 100%; }
        table.data thead { display: table-header-group; }
        table.data th, table.data td { border: 1px solid #d4d4d8; padding: 5px 6px; text-align: left; vertical-align: top; overflow-wrap: break-word; }
        table.data th { background: #f4f4f5; font-size: 8px; font-weight: 700; }
        table.data td { font-size: 8px; }
        tr { page-break-inside: avoid; }
        .muted { color: #52525b; }
        .empty { border: 1px solid #d4d4d8; color: #52525b; padding: 12px; }
    </style>
</head>
<body>
    <div class="brand">MANDALA · MANAGEMENT SYSTEM</div>
    <h1>{{ $title }}</h1>
    <p class="subtitle">Laporan resmi SHE · Data mengikuti cakupan akses dan filter yang dipilih.</p>

    <table class="meta">
        <tr><th>Periode</th><td>{{ $filters['label'] }}</td><th>Dibuat</th><td>{{ $generatedAt->format('d/m/Y H:i') }} WIB</td></tr>
        @if(!empty($filters['site_id']) || !empty($filters['company_id']) || !empty($filters['department_id']))
            <tr><th>Filter</th><td colspan="3">
                @if(!empty($filterLabels['site'])) Site: {{ $filterLabels['site'] }} @endif
                @if(!empty($filterLabels['company'])) · Perusahaan: {{ $filterLabels['company'] }} @endif
                @if(!empty($filterLabels['department'])) · Department: {{ $filterLabels['department'] }} @endif
            </td></tr>
        @endif
    </table>

    @if($kpis)
        <h2>Ringkasan KPI</h2>
        <table class="data">
            <thead><tr><th>KPI</th><th>Nilai</th><th>Sebelumnya</th><th>Perubahan</th><th>Target</th><th>Satuan</th><th>Keterangan</th></tr></thead>
            <tbody>
                @foreach($kpis as $kpi)
                    <tr>
                        <td>{{ $kpi['label'] ?? '—' }}</td>
                        <td>{{ $kpi['value'] ?? '—' }}</td>
                        <td>{{ $kpi['previous'] ?? '—' }}</td>
                        <td>{{ isset($kpi['change']) && $kpi['change'] !== null ? $kpi['change'].'%' : '—' }}</td>
                        <td>{{ $kpi['target'] ?? '—' }}</td>
                        <td>{{ $kpi['unit'] ?? '—' }}</td>
                        <td>{{ $kpi['formula'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @forelse($sections as $section)
        <h2>{{ $section['title'] }}</h2>
        <table class="data">
            <thead><tr>@foreach($section['columns'] as $column)<th>{{ Illuminate\Support\Str::headline($column) }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach($section['rows'] as $row)
                    <tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach</tr>
                @endforeach
            </tbody>
        </table>
    @empty
        @unless($kpis)
            <p class="empty">Belum ada data pada cakupan periode dan filter ini.</p>
        @endunless
    @endforelse
</body>
</html>
