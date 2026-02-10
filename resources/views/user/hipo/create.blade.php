@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl mx-auto">

<h1 class="text-xl font-semibold mb-4">⚠️ Form Laporan HIPO / Nearmiss</h1>

{{-- ERROR MESSAGE --}}
@if ($errors->any())
<div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
    <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ route('user.hipo.store') }}"
      enctype="multipart/form-data"
      class="space-y-4 bg-white border rounded-xl p-6">

@csrf

{{-- Jobsite --}}
<div>
    <label class="text-sm font-medium">Jobsite</label>
    <select name="jobsite" class="w-full border rounded-lg px-3 py-2" required>
        <option value="">-- Pilih Jobsite --</option>
        @foreach (['AAP-BGG','AAP-SBS','ABN-DBK','ABC-POS'] as $j)
            <option value="{{ $j }}" @selected(old('jobsite')==$j)>
                {{ $j }}
            </option>
        @endforeach
    </select>
</div>

{{-- Nama Pelapor --}}
<div>
    <label class="text-sm font-medium">Nama Pelapor</label>
    <input class="w-full border rounded-lg px-3 py-2 bg-gray-50"
           value="{{ auth()->user()->name }}"
           readonly>
</div>

{{-- Waktu --}}
<div>
    <label class="text-sm font-medium">Waktu Pelaporan</label>
    <input type="datetime-local"
           name="report_time"
           value="{{ old('report_time') }}"
           class="w-full border rounded-lg px-3 py-2"
           required>
</div>

{{-- Shift --}}
<div>
    <label class="text-sm font-medium">Shift</label>
    <select name="shift" class="w-full border rounded-lg px-3 py-2" required>
        <option value="">-- Pilih --</option>
        <option @selected(old('shift')=='Shift 1')>Shift 1</option>
        <option @selected(old('shift')=='Shift 2')>Shift 2</option>
    </select>
</div>

{{-- Sumber --}}
<div>
    <label class="text-sm font-medium">Sumber Laporan</label>
    <select name="source" class="w-full border rounded-lg px-3 py-2" required>
        <option value="">-- Pilih --</option>
        @foreach (['Hazard Report','Safety Inspection','PTO'] as $s)
            <option @selected(old('source')==$s)>{{ $s }}</option>
        @endforeach
    </select>
</div>

{{-- Kategori --}}
<div>
    <label class="text-sm font-medium">Kategori</label>
    <select name="category" class="w-full border rounded-lg px-3 py-2" required>
        <option value="">-- Pilih --</option>
        <option @selected(old('category')=='High Potential Hazard')>
            High Potential Hazard
        </option>
        <option @selected(old('category')=='Nearmiss')>
            Nearmiss
        </option>
    </select>
</div>

{{-- Risk Level --}}
<div>
    <label class="text-sm font-medium">Risk Level</label>
    <select name="risk_level" class="w-full border rounded-lg px-3 py-2" required>
        <option value="">-- Pilih --</option>
        @foreach (['Low','Medium','High','Extreme'] as $r)
            <option @selected(old('risk_level')==$r)>{{ $r }}</option>
        @endforeach
    </select>
</div>

<hr>

<h2 class="font-semibold text-lg">Rincian Kejadian (WAJIB)</h2>

{{-- Jenis HIPO / Nearmiss --}}
<div>
    <label class="text-sm font-medium">Jenis HIPO / Nearmiss</label>
    <select name="jenis_hipo" class="w-full border rounded-lg px-3 py-2" required>
        <option value="">-- Pilih --</option>
        <option @selected(old('jenis_hipo')=='HIPO')>HIPO</option>
        <option @selected(old('jenis_hipo')=='Nearmiss')>Nearmiss</option>
    </select>
</div>

{{-- KTA --}}
<div>
    <label class="text-sm font-medium">KTA (Kondisi Tidak Aman)</label>
    <textarea name="kta"
              rows="2"
              class="w-full border rounded-lg px-3 py-2"
              required>{{ old('kta') }}</textarea>
</div>

{{-- TTA --}}
<div>
    <label class="text-sm font-medium">TTA (Tindakan Tidak Aman)</label>
    <textarea name="tta"
              rows="2"
              class="w-full border rounded-lg px-3 py-2"
              required>{{ old('tta') }}</textarea>
</div>

{{-- Deskripsi --}}
<div>
    <label class="text-sm font-medium">Deskripsi Kejadian</label>
    <textarea name="description"
              rows="3"
              class="w-full border rounded-lg px-3 py-2"
              required>{{ old('description') }}</textarea>
</div>

<hr>

<h2 class="font-semibold text-lg">Kontrol Risiko (WAJIB)</h2>

@foreach ([
    'engineering' => 'Rekayasa Engineering',
    'administrative' => 'Administratif',
    'work_practice' => 'Praktek Kerja',
    'ppe' => 'APD'
] as $key => $label)

<div class="border rounded-lg p-4 space-y-2">
    <h3 class="font-semibold">{{ $label }}</h3>

    <textarea name="control_{{ $key }}"
              class="w-full border rounded-lg px-3 py-2"
              required>{{ old("control_$key") }}</textarea>

    <input type="file"
           name="evidence_{{ $key }}"
           class="w-full"
           accept="image/*"
           required>
</div>

@endforeach

<hr>

{{-- PIC UMUM --}}
<div>
    <label class="text-sm font-medium">PIC (Penanggung Jawab Utama)</label>
    <input name="pic"
           value="{{ old('pic') }}"
           class="w-full border rounded-lg px-3 py-2"
           required>
</div>

<div class="pt-4 flex justify-end gap-2">
    <a href="{{ route('user.hipo.index') }}"
       class="px-4 py-2 border rounded-lg">
        Batal
    </a>
    <button class="px-4 py-2 bg-maroon-700 text-white rounded-lg">
        Kirim Laporan
    </button>
</div>

</form>
</div>
@endsection
