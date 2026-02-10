@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6">

{{-- HEADER --}}
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">🧯 Detail Critical Control Management</h1>
    <a href="{{ route('ccm-reports.index') }}"
       class="px-4 py-2 rounded-lg border hover:bg-gray-100">← Kembali</a>
</div>

{{-- INFO UMUM --}}
<div class="bg-white rounded-xl p-6 border">
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
    $val = ($value === null || $value === '') ? '-' : e($value);
    return "
    <div class='grid grid-cols-3 gap-4 py-2 border-b text-sm'>
        <div class='text-gray-500'>{$label}</div>
        <div class='col-span-2 font-medium whitespace-pre-line'>{$val}</div>
    </div>";
}

function evidence($path) {
    if (!$path) {
        return "<div class='text-xs text-gray-400 py-1'>Tidak ada evidence</div>";
    }
    $url = asset('storage/'.$path);
    return "
    <div class='py-1'>
        <a href='{$url}' target='_blank'
           class='text-blue-600 underline text-sm'>📎 Lihat Evidence</a>
    </div>";
}
@endphp

{{-- ================= SECTION CCM ================= --}}
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
<div class="bg-white rounded-xl p-6 border space-y-3">
    <h2 class="font-semibold text-lg">{{ $title }}</h2>

    {!! row('Ada Kegiatan', $report->{$key.'_ada'} ? 'Ada' : 'Tidak Ada') !!}
    {!! row('Ringkasan Kegiatan', $report->{$key.'_ringkasan'} ?? null) !!}
    {!! row('Pekerjaan Kritis', $report->{$key.'_pekerjaan_kritis'} ?? null) !!}
    {!! row('Prosedur Terkait', $report->{$key.'_prosedur'} ?? null) !!}
    {!! row('Pelanggaran Prosedur', $report->{$key.'_pelanggaran'} ?? null) !!}

    <div class="pt-2 font-semibold text-sm">Kontrol Risiko</div>

    {{-- ENGINEERING --}}
    {!! row('Engineering', $report->{$key.'_engineering'} ?? null) !!}
    {!! evidence($report->{$key.'_engineering_evidence'} ?? null) !!}

    {{-- ADMINISTRATIF --}}
    {!! row('Administratif', $report->{$key.'_administratif'} ?? null) !!}
    {!! evidence($report->{$key.'_administratif_evidence'} ?? null) !!}

    {{-- PRAKTEK KERJA --}}
    {!! row('Praktek Kerja', $report->{$key.'_praktek_kerja'} ?? null) !!}
    {!! evidence($report->{$key.'_praktek_kerja_evidence'} ?? null) !!}

    {{-- APD --}}
    {!! row('APD', $report->{$key.'_apd'} ?? null) !!}
    {!! evidence($report->{$key.'_apd_evidence'} ?? null) !!}
</div>
@endforeach

</div>
@endsection
