@extends('exports.pdf.layout')

@section('pdf-title', $entry->form->title.' #'.$entry->id)

@push('pdf-styles')
    .entry-meta th { width: 20%; }
    .entry-data th { width: 30%; }
    .entry-signatures { page-break-inside: avoid; }
@endpush

@section('pdf-content')
    <p class="pdf-eyebrow">Data Formulir</p>
    <h1 class="pdf-title">{{ $entry->form->title }}</h1>
    <p class="pdf-subtitle">Entri #{{ $entry->id }} · {{ $entry->created_at->format('d/m/Y H:i') }} WIB</p>

    <table class="pdf-table entry-meta"><tbody>
        <tr><th>Pengguna</th><td>{{ $entry->user->name }} ({{ $entry->user->email }})</td></tr>
        <tr><th>Tanggal</th><td>{{ $entry->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><th>Status</th><td>{{ strtoupper($entry->status) }}</td></tr>
    </tbody></table>

    <h2 class="pdf-section-title">Jawaban Formulir</h2>
    <table class="pdf-table entry-data"><tbody>
        @foreach($entry->data as $key => $value)
            @php
                $label = ucfirst(str_replace('_', ' ', $key));
                $text = is_array($value)
                    ? implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item, JSON_UNESCAPED_UNICODE), $value))
                    : (is_bool($value) ? ($value ? 'Ya' : 'Tidak') : (string) $value);
            @endphp
            <tr><th>{{ $label }}</th><td>{{ trim($text) !== '' ? $text : '-' }}</td></tr>
        @endforeach
    </tbody></table>

    @php $latestApproval = $entry->approvals()->with('actor')->latest()->first(); @endphp
    <div class="entry-signatures">
        <p class="pdf-meta" style="text-align:right">Jakarta, {{ now()->format('d M Y') }}</p>
        <table class="pdf-signature-grid"><tr>
            <td><div>Diajukan Oleh,</div><div class="pdf-signature-space"></div><div class="pdf-signature-line">{{ $entry->user->name }}</div><div class="pdf-muted">{{ $entry->user->email }}</div></td>
            <td><div>Disetujui Oleh,</div><div class="pdf-signature-space"></div><div class="pdf-signature-line">{{ data_get($latestApproval, 'actor.name', '-') }}</div><div class="pdf-muted">{{ strtoupper(data_get($latestApproval, 'action', 'Pemberi Persetujuan')) }}</div></td>
        </tr></table>
    </div>
@endsection
