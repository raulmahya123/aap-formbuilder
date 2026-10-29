@extends('exports.pdf.layout')

@section('pdf-title', $number.' - '.$title)

@push('pdf-styles')
    .pdf-details th { width: 31%; }
    .pdf-checklist th:nth-child(1) { width: 43%; }
    .pdf-checklist th:nth-child(2) { width: 24%; }
    .pdf-checklist th:nth-child(3) { width: 33%; }
    .pdf-print-tools { margin-bottom: 4mm; text-align: right; }
    .pdf-print-tools button { padding: 2mm 4mm; border: 0; border-radius: 2mm; background: #1D4ED8; color: #fff; font: 600 9pt 'DejaVu Sans', sans-serif; }
    @media print { .pdf-print-tools { display: none; } }
@endpush

@section('pdf-content')
    @if($format === 'print')
        <div class="pdf-print-tools"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>
    @endif
    <p class="pdf-eyebrow">Laporan Operasional SHE</p>
    <h1 class="pdf-title">{{ $title }}</h1>
    <p class="pdf-meta"><strong>{{ $number }}</strong> · Dicetak {{ now()->format('d/m/Y H:i') }} WIB</p>

    <table class="pdf-table pdf-details"><tbody>
        @foreach($details as $label => $value)
            <tr><th>{{ $label }}</th><td>{{ $value ?? '-' }}</td></tr>
        @endforeach
    </tbody></table>

    @foreach($sections as $section)
        <h2 class="pdf-section-title">{{ $section['title'] }}</h2>
        <table class="pdf-table pdf-checklist">
            <thead><tr>@foreach($section['columns'] as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
            <tbody>
                @forelse($section['rows'] as $row)
                    <tr>@foreach($row as $value)<td>{{ $value ?? '-' }}</td>@endforeach</tr>
                @empty
                    <tr><td colspan="{{ count($section['columns']) }}" class="pdf-muted">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
@endsection
