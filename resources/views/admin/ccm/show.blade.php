@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6">

{{-- HEADER --}}
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">🧯 Detail Critical Control Management</h1>
    <a href="{{ route('ccm-reports.index') }}"
       class="px-4 py-2 rounded-lg border">← Kembali</a>
</div>

{{-- INFO UMUM --}}
<div class="bg-white rounded-xl p-6 shadow">
    <h2 class="font-semibold mb-4">Informasi Umum</h2>
    <div class="grid md:grid-cols-3 gap-4 text-sm">
        <div>
            <div class="text-gray-500">Waktu Pelaporan</div>
            <div class="font-medium">{{ $report->waktu_pelaporan }}</div>
        </div>
        <div>
            <div class="text-gray-500">Jobsite</div>
            <div class="font-medium">{{ $report->jobsite }}</div>
        </div>
        <div>
            <div class="text-gray-500">Nama Pelapor</div>
            <div class="font-medium">{{ $report->nama_pelapor }}</div>
        </div>
    </div>
</div>

{{-- ================= HELPER ================= --}}
@php
function row($label, $value) {
    if ($value === null || $value === '') return '';
    return "
    <div class='grid grid-cols-3 gap-4 py-2 border-b text-sm'>
        <div class='text-gray-500'>{$label}</div>
        <div class='col-span-2 font-medium'>" . e($value) . "</div>
    </div>";
}

function evidence($path) {
    if (!$path) return '';
    $url = asset('storage/'.$path);
    return "
    <div class='py-2 text-sm'>
        <a href='{$url}' target='_blank'
           class='text-blue-600 underline'>📎 Lihat Evidence</a>
    </div>";
}
@endphp

{{-- ================= TEMPLATE SECTION ================= --}}
@php
$sections = [
    'kendaraan' => '🚚 Kendaraan & Alat Berat',
    'izin_kerja' => '📄 Izin Kerja',
    'tebing' => '⛰️ Tebing / Disposal',
    'air_lumpur' => '💧 Air & Lumpur',
    'chainsaw' => '🪚 Chainsaw',
    'loto' => '🔒 LOTO & Ban',
    'lifting' => '🏗️ Lifting',
    'blasting' => '💥 Blasting',
    'kritis_baru' => '🆕 Pekerjaan Kritis Baru',
];
@endphp

@foreach($sections as $key => $title)
<div class="bg-white rounded-xl p-6 shadow">
    <h2 class="font-semibold mb-4">{{ $title }}</h2>

    {!! row('Ada Kegiatan', $report->{$key.'_ada'} ? 'Ada' : 'Tidak Ada') !!}

    @if(!$report->{$key.'_ada'})
        {!! row('Alasan Tidak Ada', $report->{$key.'_tidak_ada_alasan'}) !!}
    @endif

    {!! row('Pekerjaan Kritis', $report->{$key.'_pekerjaan_kritis'} ?? null) !!}
    {!! row('Prosedur', $report->{$key.'_prosedur'} ?? null) !!}
    {!! row('Pelanggaran', $report->{$key.'_pelanggaran'} ?? null) !!}

    {{-- KONTROL RISIKO --}}
    @foreach(['engineering','administratif','praktek_kerja','apd'] as $ctrl)
        {!! row(ucwords(str_replace('_',' ',$ctrl)), $report->{$key.'_'.$ctrl} ?? null) !!}
        {!! evidence($report->{$key.'_'.$ctrl.'_evidence'} ?? null) !!}
    @endforeach
</div>
@endforeach

</div>
@endsection
