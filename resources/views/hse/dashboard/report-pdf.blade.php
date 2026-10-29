@extends('exports.pdf.layout')

@section('pdf-title', $title)

@push('pdf-styles')
    .pdf-report-meta th { width: 18%; }
    .pdf-report-kpi th:nth-child(1) { width: 15%; }
    .pdf-report-kpi th:nth-child(2), .pdf-report-kpi th:nth-child(3), .pdf-report-kpi th:nth-child(4), .pdf-report-kpi th:nth-child(5), .pdf-report-kpi th:nth-child(6) { width: 9%; }
    .pdf-report-kpi th:nth-child(7) { width: 40%; }
    .pdf-report-data td, .pdf-report-data th { font-size: 7pt; }
@endpush

@section('pdf-content')
    <p class="pdf-eyebrow">Laporan Resmi SHE</p>
    <h1 class="pdf-title">{{ $title }}</h1>
    <p class="pdf-subtitle">Ringkasan dan data SHE sesuai cakupan akses dan filter terpilih.</p>

    <table class="pdf-table pdf-report-meta">
        <tbody>
            <tr><th>Periode</th><td>{{ $filters['label'] }}</td><th>Dibuat</th><td>{{ $generatedAt->format('d/m/Y H:i') }} WIB</td></tr>
            @if(!empty($filters['site_id']) || !empty($filters['company_id']) || !empty($filters['department_id']))
                <tr><th>Filter</th><td colspan="3">
                    @if(!empty($filterLabels['site'])) Site: {{ $filterLabels['site'] }} @endif
                    @if(!empty($filterLabels['company'])) · Perusahaan: {{ $filterLabels['company'] }} @endif
                    @if(!empty($filterLabels['department'])) · Department: {{ $filterLabels['department'] }} @endif
                </td></tr>
            @endif
        </tbody>
    </table>

    @if($kpis)
        <h2 class="pdf-section-title">Ringkasan KPI</h2>
        <table class="pdf-table pdf-report-kpi">
            <thead><tr><th>KPI</th><th>Nilai</th><th>Sebelumnya</th><th>Perubahan</th><th>Target</th><th>Satuan</th><th>Keterangan</th></tr></thead>
            <tbody>
                @foreach($kpis as $kpi)
                    <tr>
                        <td>{{ $kpi['label'] ?? '-' }}</td><td>{{ $kpi['value'] ?? '-' }}</td><td>{{ $kpi['previous'] ?? '-' }}</td>
                        <td>{{ isset($kpi['change']) && $kpi['change'] !== null ? $kpi['change'].'%' : '-' }}</td><td>{{ $kpi['target'] ?? '-' }}</td>
                        <td>{{ $kpi['unit'] ?? '-' }}</td><td>{{ $kpi['formula'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @forelse($sections as $section)
        <h2 class="pdf-section-title">{{ $section['title'] }}</h2>
        <table class="pdf-table pdf-report-data">
            <thead><tr>@foreach($section['columns'] as $column)<th>{{ Illuminate\Support\Str::headline($column) }}</th>@endforeach</tr></thead>
            <tbody>@foreach($section['rows'] as $row)<tr>@foreach($row as $value)<td>{{ $value ?? '-' }}</td>@endforeach</tr>@endforeach</tbody>
        </table>
    @empty
        @unless($kpis)<p class="pdf-empty">Belum ada data pada cakupan periode dan filter ini.</p>@endunless
    @endforelse
@endsection
