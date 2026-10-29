@extends('layouts.app')
@section('title','Input Catatan Harian')
@section('content')
@php
  // Map sites per company -> untuk filter dropdown site
  /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
  $sitesByCompany = collect($sites ?? [])->groupBy(fn($s) => (string)$s->company_id)
    ->map(fn($rows) => $rows->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'company_id'=>$s->company_id])->values())
    ->toArray();
@endphp
<div class="w-full min-w-0 space-y-5">
  {{-- Hero header --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
        <x-ui.page-title>Input Catatan Harian</x-ui.page-title>
        <x-ui.page-description>
          Catat hal penting hari ini. <span class="font-medium">note_time</span> disimpan otomatis (WIB).
        </x-ui.page-description>
      </div></div>
  <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('user.daily_notes.index') }}">Kembali</x-ui.link>
<x-ui.button form="page-primary-form" variant="primary" type="submit">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M17 8a1 1 0 0 1-1 1H7.414l3.293 3.293a1 1 0 1 1-1.414 1.414L4.586 9.707a1 1 0 0 1 0-1.414L9.293 3.586a1 1 0 1 1 1.414 1.414L7.414 8H16a1 1 0 0 1 1 1Z"/>
        </svg>
        Simpan
      </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>
<div class="ui-card">
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
      <div class="ui-form-summary-item">
        <div class="font-semibold">Tanggal (WIB)</div>
        <div>{{ now('Asia/Jakarta')->format('d M Y') }}</div>
      </div>
      <div class="ui-form-summary-item">
        <div class="font-semibold">Waktu (WIB)</div>
        <div>{{ now('Asia/Jakarta')->format('H:i') }}</div>
      </div>
      <div class="ui-form-summary-item">
        <div class="font-semibold">Pengguna</div>
        <div>{{ auth()->user()->name ?? '—' }}</div>
      </div>
    </div>
  </div>
  {{-- Alerts --}}
  @if ($errors->any())
    <div class="ui-form-alert" role="alert">
      <div class="ui-form-alert__title">Periksa kembali:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif
  {{-- Form --}}
  <form id="page-primary-form" action="{{ route('user.daily_notes.store') }}" method="post" class="ui-card" x-data="noteForm()">
  @csrf
    {{-- Section: Konteks --}}
    <div class="space-y-3">
      <div class="ui-form-section-title">Konteks</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Perusahaan --}}
        <div>
          <label class="block">Perusahaan (opsional)</label>
          <select name="company_id" id="company_id" class="w-full ui-input">
            <option value="">— Pilih Perusahaan —</option>
            @foreach(($companies ?? []) as $c)
              <option value="{{ $c->id }}" @selected(old('company_id')==$c->id)>
                {{ $c->code ?? 'CMP' }} — {{ $c->name }}
              </option>
            @endforeach
          </select>
          @error('company_id')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
        {{-- Site (terfilter by perusahaan) --}}
        <div>
          <label class="block">Site (opsional)</label>
          <select name="site_id" id="site_id" class="w-full ui-input" {{ old('company_id') ? '' : 'disabled' }}>
            <option value="">— Pilih Site —</option>
            @php
              $cidOld = (string) old('company_id','');
              $sidOld = (string) old('site_id','');
            @endphp
            @if($cidOld !== '' && !empty($sitesByCompany[$cidOld]))
              @foreach($sitesByCompany[$cidOld] as $s)
                <option value="{{ $s['id'] }}" @selected($sidOld === (string)$s['id'])>{{ $s['name'] }}</option>
              @endforeach
            @endif
          </select>
          <div class="ui-form-helper">Daftar site mengikuti perusahaan yang dipilih.</div>
          @error('site_id')<p class="ui-form-error">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>
    <hr class="border-t border-slate-200">
    {{-- Section: Konten --}}
    <div class="space-y-4">
      <div class="ui-form-section-title">Konten</div>
      {{-- Judul --}}
      <div>
        <div class="flex items-center justify-between">
          <label class="block">Judul <span style="color:#dc2626">*</span></label>
          <span class="ui-form-helper"><span x-text="title.length"></span>/255</span>
        </div>
        <input
          type="text"
          name="title"
          x-model="title"
          value="{{ old('title') }}"
          maxlength="255"
          placeholder="Ringkas & jelas, mis. 'Sync Produksi — Kendala Fuel Metering'"
          class="w-full ui-input @error('title') is-invalid @enderror">
        @error('title')<p class="ui-form-error">{{ $message }}</p>@enderror
      </div>
      {{-- Isi Catatan --}}
      <div>
        <div class="flex items-center justify-between">
          <label class="block">Isi Catatan <span style="color:#dc2626">*</span></label>
          <span class="ui-form-helper"><span x-text="content.length"></span> karakter</span>
        </div>
        <textarea
          name="content"
          rows="8"
          x-model="content"
          x-ref="contentArea"
          @input="autoGrow()"
          placeholder="Tulis kronologi, keputusan, follow-up, atau highlight penting…"
          class="w-full ui-input @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
        @error('content')<p class="ui-form-error">{{ $message }}</p>@enderror
        {{-- Quick tags --}}
        <div class="mt-2 flex flex-wrap gap-2">
          @foreach(['[Meeting]','[Follow-up]','[Issue]','[Decision]','[Reminder]','[Risk]','[Blocking]'] as $chip)
            <x-ui.button variant="secondary" type="button" @click="append('{{ $chip }} ')"
              >{{ $chip }}</x-ui.button>
          @endforeach
        </div>
      </div>
    </div>
    <hr class="border-t border-slate-200">
    {{-- Section: Waktu --}}
    <div class="space-y-3">
      <div class="ui-form-section-title">Waktu</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block">Tanggal & Waktu Saat Ini (WIB)</label>
          <input type="text" value="{{ now('Asia/Jakarta')->format('d-m-Y H:i') }}" class="w-full ui-input" readonly>
        </div>
        <div>
          <label class="block">Akan Disimpan Sebagai</label>
          <input type="text" value="note_time = now('Asia/Jakarta')" class="w-full ui-input" readonly>
        </div>
      </div>
      <div class="ui-form-helper">
        * Catatan tersimpan atas nama <strong>{{ auth()->user()->name ?? 'Anda' }}</strong>.
      </div>
    </div>
    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3 pt-2">
      <a href="{{ route('user.daily_notes.index') }}" class="btn-outline">Batal</a>
    </div>
  </form>
</div>
{{-- Dataset untuk JS filter site --}}
<div id="dn-dataset" data-sites='@json($sitesByCompany)'></div>
{{-- Alpine (CDN ringan jika belum ada) --}}
<script>
  if (!window.alpineLoaded) {
    const s = document.createElement('script');
    s.defer = true;
    s.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
    s.onload = () => {
      window.alpineLoaded = true;
      // Auto-grow pertama kali (kalau old('content') panjang)
      const ta = document.querySelector('[x-ref="contentArea"]');
      if (ta) { ta.style.height = 'auto'; ta.style.height = (ta.scrollHeight + 6) + 'px'; }
    };
    document.head.appendChild(s);
  }
  function noteForm(){
    return {
      title: @json(old('title','')),
      content: @json(old('content','')),
      append(s){ this.content = (this.content ? this.content + ' ' : '') + s; },
      autoGrow(){
        const ta = this.$refs.contentArea;
        if(!ta) return;
        ta.style.height = 'auto';
        ta.style.height = (ta.scrollHeight + 6) + 'px';
      }
    }
  }
  (function(){
    const meta   = document.getElementById('dn-dataset');
    const mapRaw = JSON.parse(meta.dataset.sites || '{}');
    const SITES_BY_COMPANY = {};
    Object.keys(mapRaw).forEach(k => SITES_BY_COMPANY[String(k)] = mapRaw[k]);
    const companySel = document.getElementById('company_id');
    const siteSel    = document.getElementById('site_id');
    function repopulateSites(companyId, keepSiteId){
      const rows = SITES_BY_COMPANY[String(companyId)] || [];
      // clear
      while (siteSel.options.length) siteSel.remove(0);
      // default
      siteSel.appendChild(new Option('— Pilih Site —',''));
      // fill
      rows.forEach(r => {
        const opt = new Option(r.name, r.id);
        if (keepSiteId && String(keepSiteId) === String(r.id)) opt.selected = true;
        siteSel.appendChild(opt);
      });
      siteSel.disabled = false;
    }
    if (companySel) {
      companySel.addEventListener('change', function(){
        repopulateSites(this.value, null);
      });
    }
  })();
</script>
@endsection
