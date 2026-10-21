{{-- resources/views/admin/reports/edit_total.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Total Indikator')
@section('content')
@php
  /** @var \App\Models\Indicator|null $indicator */
  /** @var \App\Models\Site|null $site */
  $indName  = $indicator->name ?? ('Indikator #' . ($indicator->id ?? request('indicator_id')));
  $siteName = $site?->code
      ? $site->code . ' — ' . ($site->name ?? '')
      : 'Semua Site';
  // context periode & filter – pastikan nggak hilang
  $scope  = $scope  ?? request('scope', 'month');
  $date   = $date   ?? request('date');
  $week   = $week   ?? request('week');
  $month  = $month  ?? request('month');
  $year   = $year   ?? request('year');
  $siteId = $siteId ?? request('site_id');
  // nilai existing total (kalau ada), fallback 0
  $existingTotal = $existingTotal ?? 0;
@endphp
<div class="max-w-2xl mx-auto space-y-5">
  {{-- HEADER --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Edit Total Indikator</x-ui.page-title>
    <x-ui.page-description>
      Override nilai total agregat untuk periode
      <span class="font-semibold text-slate-800">{{ $periodLabel ?? '-' }}</span>.
    </x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>
  {{-- INFO KONTEKS --}}
  <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
    <div class="font-semibold text-slate-900">{{ $indName }}</div>
    <dl class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-1 text-xs text-slate-600">
      <div class="flex justify-between">
        <dt class="opacity-70">Group</dt>
        <dd class="font-mono">{{ $groupCode ?? '-' }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="opacity-70">Site</dt>
        <dd class="font-medium text-right">{{ $siteName }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="opacity-70">Scope</dt>
        <dd class="normal-case font-medium text-right">{{ $scope }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="opacity-70">Periode</dt>
        <dd class="font-medium text-right">{{ $periodLabel ?? '-' }}</dd>
      </div>
    </dl>
  </div>
  {{-- FLASH STATUS (kalau ada) --}}
  {{-- ERROR VALIDASI --}}
  @if ($errors->any())
    <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800">
      <div class="font-semibold mb-1">Periksa kembali isian anda:</div>
      <ul class="list-disc list-inside space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  {{-- FORM --}}
  <form action="{{ route('admin.report-totals.update') }}" method="post"
        class="space-y-4 rounded-lg border border-slate-200 bg-white px-4 py-4 shadow-none">
    @csrf
    {{-- kalau route update kamu pakainya PUT/PATCH, aktifkan baris ini --}}
    {{-- @method('PUT') --}}
    {{-- HIDDEN CONTEXT (ini kunci supaya override-nya nyangkut di kombinasi yang tepat) --}}
    <input type="hidden" name="indicator_id" value="{{ $indicator->id ?? request('indicator_id') }}">
    <input type="hidden" name="group_code"   value="{{ $groupCode }}">
    <input type="hidden" name="scope"        value="{{ $scope }}">
    <input type="hidden" name="date"         value="{{ $date }}">
    <input type="hidden" name="week"         value="{{ $week }}">
    <input type="hidden" name="month"        value="{{ $month }}">
    <input type="hidden" name="year"         value="{{ $year }}">
    <input type="hidden" name="site_id"      value="{{ $siteId }}">
    <div>
      <label class="block text-xs font-semibold text-slate-700 mb-1">
        Total Baru
      </label>
      <input
        type="number"
        step="0.01"
        name="total"
        value="{{ old('total', $existingTotal) }}"
        class="w-full rounded-lg border-slate-300 text-sm shadow-none focus:border-maroon-500 focus:ring-maroon-500"
        required
      >
      <p class="mt-1 text-[11px] text-slate-500">
        Masukkan nilai total yang ingin digunakan untuk kombinasi
        indikator + periode + site ini. Nilai ini akan meng-override hasil perhitungan otomatis.
      </p>
      @error('total')
        <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
      @enderror
    </div>
    <div class="flex items-center justify-between gap-3 pt-2">
      {{-- Kembali ke rekap: paling aman pakai URL sebelumnya --}}
      <x-ui.link variant="secondary" href="{{ url()->previous() }}"
         class="inline-flex items-center px-3 py-2 rounded-lg border text-xs font-semibold">
        ← Kembali ke Rekap
      </x-ui.link>
      <x-ui.button variant="primary" type="submit"
              class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold shadow-none">
        Simpan Total
      </x-ui.button>
    </div>
  </form>
</div>
@endsection