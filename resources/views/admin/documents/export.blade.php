@extends('exports.pdf.layout')

@section('pdf-title', $document->doc_no.' - '.$document->title)

@push('pdf-styles')
    .document-meta th { width: 22%; }
    .document-signatures { page-break-inside: avoid; }
@endpush

@section('pdf-content')
    <p class="pdf-eyebrow">{{ $document->doc_type ?: 'Dokumen Terkendali' }}</p>
    <h1 class="pdf-title">{{ $document->title }}</h1>
    <p class="pdf-subtitle">{{ $document->doc_no }} · Revisi {{ $document->revision_no ?? 0 }}</p>

    <table class="pdf-table document-meta"><tbody>
        <tr><th>Nomor dokumen</th><td>{{ $document->doc_no ?: '-' }}</td><th>Status</th><td>{{ ucfirst($document->controlled_status ?? 'controlled') }}</td></tr>
        <tr><th>Department</th><td>{{ $document->department?->name ?? $document->dept_code ?? '-' }}</td><th>Project</th><td>{{ $document->project_code ?? '-' }}</td></tr>
        <tr><th>Pemilik</th><td>{{ $document->owner?->name ?? '-' }}</td><th>Tanggal berlaku</th><td>{{ $document->effective_date?->format('d/m/Y') ?? '-' }}</td></tr>
        <tr><th>Klasifikasi</th><td>{{ $document->class ?? '-' }}</td><th>Dicetak</th><td>{{ now()->format('d/m/Y H:i') }} WIB</td></tr>
    </tbody></table>

    @foreach(($document->sections ?? []) as $section)
        <h2 class="pdf-section-title">{{ $section['title'] ?? $section['label'] ?? 'Bagian dokumen' }}</h2>
        @if(($section['type'] ?? 'text') === 'table')
            @php $rows = max(1, (int) ($section['rows'] ?? 1)); $cols = max(1, (int) ($section['cols'] ?? 1)); @endphp
            <table class="pdf-table"><tbody>
                @for($row = 0; $row < $rows; $row++)
                    <tr>@for($col = 0; $col < $cols; $col++)<td>{{ $section['cells'][$row * $cols + $col] ?? '-' }}</td>@endfor</tr>
                @endfor
            </tbody></table>
        @else
            @if(!empty($section['subtitle']))<p class="pdf-meta">{{ $section['subtitle'] }}</p>@endif
            @php
                $sectionHtml = preg_replace('/<\/(p|div|li|tr|h[1-6])>/i', "\n", (string) ($section['html'] ?? ''));
                $sectionText = trim(html_entity_decode(strip_tags($sectionHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            @endphp
            <div class="pdf-section-copy">{{ $sectionText ?: '-' }}</div>
        @endif
    @endforeach

    @if($document->signatures->isNotEmpty())
        <h2 class="pdf-section-title">Persetujuan</h2>
        <table class="pdf-table document-signatures"><thead><tr><th>Peran</th><th>Nama</th><th>Jabatan</th><th>Status</th><th>Tanggal</th></tr></thead><tbody>
            @foreach($document->signatures as $signature)
                <tr><td>{{ $signature->role ?? '-' }}</td><td>{{ $signature->signedBy?->name ?? $signature->name ?? '-' }}</td><td>{{ $signature->position_title ?? '-' }}</td><td>{{ $signature->signed_at ? 'Ditandatangani' : 'Menunggu' }}</td><td>{{ $signature->signed_at?->format('d/m/Y H:i') ?? '-' }}</td></tr>
            @endforeach
        </tbody></table>
    @endif
@endsection
