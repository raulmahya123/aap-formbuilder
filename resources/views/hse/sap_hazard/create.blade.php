@extends('layouts.app')
@section('title', 'Form Hazard Report — SHE-FM-007')
@section('content')
<x-ui.page>
  <x-ui.page-header>
    <div class="ui-page-header__copy"><x-ui.page-title>Form Hazard Report — SHE-FM-007</x-ui.page-title><x-ui.page-description>Catat lokasi dan deskripsi bahaya, lalu lanjutkan ke penilaian risiko.</x-ui.page-description></div>
    <x-ui.page-actions><x-ui.back-link href="{{ route('hse.dashboard.sap.hazard.index') }}">Kembali ke daftar</x-ui.back-link></x-ui.page-actions>
  </x-ui.page-header>
  @if($errors->any())
    <div role="alert" class="ui-card mb-5 border-red-600"><p class="font-medium">Periksa kembali isian formulir.</p><ul class="mt-2 list-disc pl-5 text-sm text-red-700">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif
  <section class="ui-card ui-standalone-form">
<form method="POST" action="{{ route('hse.dashboard.sap.hazard.store') }}" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="text-xs text-slate-600" for="site_id">Site</label>
<select id="site_id" name="site_id" class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="">- Pilih Site -</option>
                                @foreach(($options['sites'] ?? []) as $s)
                                    <option value="{{ $s->id }}" @selected((string)old('site_id') === (string)($s->id))>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="department_id">Departemen</label>
<select id="department_id" name="department_id" class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="">- Pilih Departemen -</option>
                                @foreach(($options['departments'] ?? []) as $d)
                                    <option value="{{ $d->id }}" @selected((string)old('department_id') === (string)($d->id))>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="report_date">Tanggal Dilaporkan</label>
<input id="report_date" type="date" name="report_date" value="{{ old('report_date', date('Y-m-d')) }}" class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="report_time">Waktu Dilaporkan</label>
<input id="report_time" type="time" name="report_time" value="{{ old('report_time', date('H:i')) }}" class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600" for="location">Lokasi Bahaya *</label>
<input id="location" type="text" name="location" required class="w-full mt-1 border rounded px-2 py-1.5" value="{{ old('location') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600" for="location_detail">Detail Lokasi</label>
<input id="location_detail" type="text" name="location_detail" class="w-full mt-1 border rounded px-2 py-1.5" value="{{ old('location_detail') }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600" for="activity_related">Aktivitas Terkait</label>
<input id="activity_related" type="text" name="activity_related" class="w-full mt-1 border rounded px-2 py-1.5" value="{{ old('activity_related') }}">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="hazard_type">Kategori Bahaya *</label>
<select id="hazard_type" name="hazard_type" required class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="Unsafe Condition" @selected((string)old('hazard_type') === (string)('Unsafe Condition'))>Unsafe Condition</option>
                                <option value="Unsafe Action" @selected((string)old('hazard_type') === (string)('Unsafe Action'))>Unsafe Action</option>
                                <option value="Environment" @selected((string)old('hazard_type') === (string)('Environment'))>Environment</option>
                                <option value="Equipment" @selected((string)old('hazard_type') === (string)('Equipment'))>Equipment</option>
                                <option value="Other" @selected((string)old('hazard_type') === (string)('Other'))>Other</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600" for="hazard_description">Deskripsi Bahaya *</label>
<textarea id="hazard_description" name="hazard_description" required class="w-full mt-1 border rounded px-2 py-1.5" rows="3">{{ old('hazard_description') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600" for="potential_impact">Potensi Dampak</label>
<textarea id="potential_impact" name="potential_impact" class="w-full mt-1 border rounded px-2 py-1.5" rows="2">{{ old('potential_impact') }}</textarea>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3">
                        <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.hazard.index') }}">Batal</x-ui.link>
<x-ui.button type="submit" variant="primary" icon="plus">Buat Laporan & Lanjut Penilaian Risiko</x-ui.button>
                    </div>
                </form>
  </section>
</x-ui.page>
@endsection
