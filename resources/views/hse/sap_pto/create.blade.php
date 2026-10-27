@extends('layouts.app')
@section('title', 'Form PTO — SHE-FM-019')
@section('content')
<x-ui.page>
  <x-ui.page-header>
    <div class="ui-page-header__copy"><x-ui.page-title>Form PTO — SHE-FM-019</x-ui.page-title><x-ui.page-description>Lengkapi informasi pengamatan tugas, lalu lanjutkan ke checklist PTO.</x-ui.page-description></div>
    <x-ui.page-actions><x-ui.back-link href="{{ route('hse.dashboard.sap.pto.index') }}">Kembali ke daftar</x-ui.back-link></x-ui.page-actions>
  </x-ui.page-header>
  @if($errors->any())
    <div role="alert" class="ui-card mb-5 border-red-600"><p class="font-medium">Periksa kembali isian formulir.</p><ul class="mt-2 list-disc pl-5 text-sm text-red-700">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif
  <section class="ui-card ui-standalone-form">
<form method="POST" action="{{ route('hse.dashboard.sap.pto.store') }}" class="space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-600" for="site_id">Site / Lokasi Kerja</label>
<select id="site_id" name="site_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Site -</option>
                                @foreach(($options['sites'] ?? []) as $s)
                                    <option value="{{ $s->id }}" @selected((string)old('site_id') === (string)($s->id))>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="department_id">Departemen</label>
<select id="department_id" name="department_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Departemen -</option>
                                @foreach(($options['departments'] ?? []) as $d)
                                    <option value="{{ $d->id }}" @selected((string)old('department_id') === (string)($d->id))>{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600" for="work_activity">Pekerjaan yang Diamati *</label>
<textarea id="work_activity" name="work_activity" required class="w-full mt-1 border rounded px-2 py-1.5 text-sm" rows="2">{{ old('work_activity') }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-xs text-slate-600" for="related_procedure">Prosedur yang Berkaitan (IK/JSA/Prosedur)</label>
<textarea id="related_procedure" name="related_procedure" class="w-full mt-1 border rounded px-2 py-1.5 text-sm" rows="2">{{ old('related_procedure') }}</textarea>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="observation_date">Tanggal Pelaksanaan *</label>
<input id="observation_date" type="date" name="observation_date" required value="{{ old('observation_date', date('Y-m-d')) }}" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="observation_time">Waktu Pengamatan</label>
<input id="observation_time" type="time" name="observation_time" class="w-full mt-1 border rounded px-2 py-1.5 text-sm" value="{{ old('observation_time') }}">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="duration">Durasi Pengamatan</label>
<input id="duration" type="text" name="duration" placeholder="Contoh: 30 menit" class="w-full mt-1 border rounded px-2 py-1.5 text-sm" value="{{ old('duration') }}">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="work_location">Lokasi Kerja</label>
<input id="work_location" type="text" name="work_location" class="w-full mt-1 border rounded px-2 py-1.5 text-sm" value="{{ old('work_location') }}">
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="observed_employee_id">Karyawan yang Diamati</label>
<select id="observed_employee_id" name="observed_employee_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Karyawan -</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected((string)old('observed_employee_id') === (string)($u->id))>{{ $u->name }} {{ $u->nik ? '('.$u->nik.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600" for="supervisor_id">Atasan Langsung / Supervisor Review</label>
<select id="supervisor_id" name="supervisor_id" class="w-full mt-1 border rounded px-2 py-1.5 text-sm">
                                <option value="">- Pilih Supervisor -</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected((string)old('supervisor_id') === (string)($u->id))>{{ $u->name }} {{ $u->nik ? '('.$u->nik.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-3">
                        <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.pto.index') }}">Batal</x-ui.link>
<x-ui.button type="submit" variant="primary" icon="plus">Buat PTO & Lanjut Isi Checklist</x-ui.button>
                    </div>
                </form>
  </section>
</x-ui.page>
@endsection
