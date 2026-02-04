@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">

{{-- ================= JUDUL ================= --}}
<div>
    <h1 class="text-2xl font-bold">🧯 Critical Control Management (CCM)</h1>
    <p class="text-sm text-gray-500">Form Input Pengendalian Risiko Kritis</p>
</div>

<form method="POST"
      action="{{ route('ccm-reports.store') }}"
      enctype="multipart/form-data"
      class="space-y-6">
@csrf

{{-- ================= SECTION 1 : UMUM ================= --}}
<div class="bg-white p-6 rounded-xl shadow">
    <h2 class="font-semibold mb-4">1. Informasi Umum</h2>

    <div class="grid md:grid-cols-3 gap-4">
        <div>
            <label class="text-sm">Waktu Pelaporan</label>
            <input type="date" name="waktu_pelaporan"
                   class="w-full border rounded px-3 py-2"
                   required>
        </div>

        <div>
            <label class="text-sm">Jobsite</label>
            <select name="jobsite" class="w-full border rounded px-3 py-2" required>
                <option value="">-- pilih --</option>
                <option>AAP-BGG</option>
                <option>AAP-SBS</option>
                <option>ABN-DBK</option>
                <option>ABC-POS</option>
            </select>
        </div>

        <div>
            <label class="text-sm">Nama Pelapor</label>
            <input type="text" name="nama_pelapor"
                   class="w-full border rounded px-3 py-2"
                   required>
        </div>
    </div>
</div>

{{-- ================= TEMPLATE SECTION ================= --}}
@php
$sections = [
    'kendaraan' => 'Pengoperasian Kendaraan & Alat Berat',
    'izin_kerja' => 'Izin Kerja',
    'tebing' => 'Tebing / Disposal',
    'air_lumpur' => 'Air & Lumpur',
    'chainsaw' => 'Chainsaw',
    'loto' => 'LOTO',
    'lifting' => 'Lifting',
    'blasting' => 'Blasting',
    'kritis_baru' => 'Pekerjaan Kritis Baru',
];
@endphp

@foreach($sections as $key => $title)
<div class="bg-white p-6 rounded-xl shadow">
    <h2 class="font-semibold mb-4">{{ $title }}</h2>

    <div class="grid md:grid-cols-2 gap-4">

        {{-- ADA KEGIATAN --}}
        <div>
            <label class="text-sm">Ada kegiatan?</label>
            <select name="{{ $key }}_ada" class="w-full border rounded px-3 py-2">
                <option value="">-- pilih --</option>
                <option value="1">Ada</option>
                <option value="0">Tidak Ada</option>
            </select>
        </div>

        {{-- ALASAN TIDAK ADA --}}
        <div>
            <label class="text-sm">Alasan jika tidak ada</label>
            <input type="text"
                   name="{{ $key }}_tidak_ada_alasan"
                   class="w-full border rounded px-3 py-2"
                   placeholder="Wajib jika Tidak Ada">
        </div>

        <div>
            <label class="text-sm">Pekerjaan Kritis</label>
            <input type="text"
                   name="{{ $key }}_pekerjaan_kritis"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- KONTROL RISIKO --}}
        @foreach(['engineering','administratif','praktek_kerja','apd'] as $ctrl)
        <div>
            <label class="text-sm">{{ ucwords(str_replace('_',' ',$ctrl)) }}</label>
            <input type="text"
                   name="{{ $key }}_{{ $ctrl }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="text-sm">Evidence {{ ucwords($ctrl) }}</label>
            <input type="file"
                   name="{{ $key }}_{{ $ctrl }}_evidence"
                   class="w-full border rounded px-3 py-2"
                   accept="image/*">
        </div>
        @endforeach
    </div>
</div>
@endforeach

{{-- ================= ACTION ================= --}}
<div class="flex gap-3">
    <button type="submit"
            class="px-6 py-2 rounded-lg bg-maroon-700 text-white">
        💾 Simpan CCM
    </button>

    <a href="{{ route('ccm-reports.index') }}"
       class="px-6 py-2 rounded-lg border">
        Batal
    </a>
</div>

</form>
</div>
@endsection
