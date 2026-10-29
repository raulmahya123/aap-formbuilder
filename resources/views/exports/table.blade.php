@extends('exports.pdf.layout')

@section('pdf-title', $title)

@push('pdf-styles')
    .table-export { table-layout: fixed; }
    .table-export th, .table-export td { font-size: {{ count($columns) > 5 ? '6.5pt' : '8pt' }}; }
    .table-export th:nth-child(1) { width: {{ number_format(100 / count($columns), 2, '.', '') }}%; }
@endpush

@section('pdf-content')
    <p class="pdf-eyebrow">Laporan Data Mandala</p>
    <h1 class="pdf-title">{{ $title }}</h1>
    <p class="pdf-subtitle">Data pada halaman aktif · {{ count($rows) }} baris</p>
    <p class="pdf-meta">Dibuat {{ $exportedAt->format('d/m/Y H:i') }} WIB</p>
    <table class="pdf-table table-export {{ count($columns) > 5 ? 'pdf-table--compact' : '' }}">
        <thead><tr>@foreach($columns as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach($rows as $row)
                <tr>@foreach($row as $value)<td>{{ $value ?? '-' }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
@endsection
