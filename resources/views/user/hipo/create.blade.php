@extends('layouts.app')

@section('content')
<x-ui.page class="w-full min-w-0 ">

<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Form Laporan HIPO / Nearmiss</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan laporan HIPO dan Nearmiss.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="page-primary-form" variant="primary" >
        Kirim Laporan
    </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

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

<form id="page-primary-form" method="POST"
      action="{{ route('user.hipo.store') }}"
      enctype="multipart/form-data"
      class="ui-card">
  @csrf

{{-- Jobsite --}}
<div>
    <label >Jobsite</label>
    <select name="site_id" class="w-full ui-input" required>
        <option value="">-- Pilih Jobsite --</option>
        @foreach (($sites ?? []) as $site)
            <option value="{{ $site['id'] }}" @selected(old('site_id')==$site['id'])>
                {{ $site['label'] }}
            </option>
        @endforeach
    </select>
</div>

{{-- Nama Pelapor --}}
<div>
    <label >Nama Pelapor</label>
    <input class="w-full ui-input"
           value="{{ auth()->user()->name }}"
           readonly>
</div>

{{-- Waktu --}}
<div>
    <label >Waktu Pelaporan</label>
    <input type="datetime-local"
           name="report_time"
           value="{{ old('report_time') }}"
           class="w-full ui-input"
           required>
</div>

{{-- Shift --}}
<div>
    <label >Shift</label>
    <select name="shift" class="w-full ui-input" required>
        <option value="">-- Pilih --</option>
        <option @selected(old('shift')=='Shift 1')>Shift 1</option>
        <option @selected(old('shift')=='Shift 2')>Shift 2</option>
    </select>
</div>

{{-- Sumber --}}
<div>
    <label >Sumber Laporan</label>
    <select name="source" class="w-full ui-input" required>
        <option value="">-- Pilih --</option>
        @foreach (['Hazard Report','Safety Inspection','PTO'] as $s)
            <option @selected(old('source')==$s)>{{ $s }}</option>
        @endforeach
    </select>
</div>

{{-- Kategori --}}
<div>
    <label >Kategori</label>
    <select name="category" class="w-full ui-input" required>
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
    <label >Risk Level</label>
    <select name="risk_level" class="w-full ui-input" required>
        <option value="">-- Pilih --</option>
        @foreach (['Low','Medium','High','Extreme'] as $r)
            <option @selected(old('risk_level')==$r)>{{ $r }}</option>
        @endforeach
    </select>
</div>

{{-- Stop Work --}}
<div class="flex items-center gap-2">
    <input type="checkbox"
           name="stop_work"
           value="1"
           @checked(old('stop_work'))>
    <label >Stop Work</label>
</div>

<hr>

<x-ui.section-title >Rincian Kejadian (WAJIB)</x-ui.section-title>

{{-- KTA --}}
<div>
    <label >KTA (Kondisi Tidak Aman)</label>
    <textarea name="kta"
              rows="2"
              class="w-full ui-input"
              required>{{ old('kta') }}</textarea>
</div>

{{-- TTA --}}
<div>
    <label >TTA (Tindakan Tidak Aman)</label>
    <textarea name="tta"
              rows="2"
              class="w-full ui-input"
              required>{{ old('tta') }}</textarea>
</div>

{{-- Deskripsi --}}
<div>
    <label >Deskripsi Kejadian</label>
    <textarea name="description"
              rows="3"
              class="w-full ui-input"
              required>{{ old('description') }}</textarea>
</div>

{{-- Konsekuensi Potensial --}}
<div>
    <label >Konsekuensi Potensial</label>
    <select name="potential_consequence" class="w-full ui-input" required>
        <option value="">-- Pilih --</option>
        @foreach (['Fatality','LTI','Injury Non LTI','Property Damage','Environment Accident'] as $consequence)
            <option @selected(old('potential_consequence')==$consequence)>{{ $consequence }}</option>
        @endforeach
    </select>
</div>

<hr>

<x-ui.section-title >Kontrol Risiko (WAJIB)</x-ui.section-title>

@foreach ([
    'engineering' => 'Rekayasa Engineering',
    'administrative' => 'Administratif',
    'work_practice' => 'Praktek Kerja',
    'ppe' => 'APD'
] as $key => $label)

<div class="border rounded-lg p-4 space-y-2">
    <x-ui.card-title >{{ $label }}</x-ui.card-title>

    <textarea name="control_{{ $key }}"
              class="w-full ui-input"
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
    <label >PIC (Penanggung Jawab Utama)</label>
    <x-ui.user-name-select name="pic" :users="$picUsers" :value="old('pic')" aria-label="PIC Utama" required />
</div>

<div class="pt-4 flex justify-end gap-2">
    <x-ui.link variant="secondary" href="{{ route('user.hipo.index') }}"
       >
        Batal
    </x-ui.link>

</div>

</form>
</x-ui.page>
@endsection
