@extends('layouts.app')
@section('title','Input Catatan Harian')

@section('content')
@php
  // Map sites per company -> untuk filter dropdown site
  /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
  $sitesByCompany = collect($sites ?? [])->groupBy(fn($s) => (string)$s->company_id)
    ->map(fn($rows) => $rows->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'company_id'=>$s->company_id])->values())
    ->toArray();

  // Pakai maroon sesuai swatch (#610E1C) sebagai fallback
  $brandMaroon = 'var(--brand-maroon,#610e1c)';
@endphp

<style>
  /* Mini design tokens */
  :root{
    --maroon: {{ $brandMaroon }};
    --maroon-weak: color-mix(in oklab, var(--maroon) 14%, white);
    --maroon-soft: color-mix(in oklab, var(--maroon) 10%, white);
  }

  /* Glowing focus */
  .focus-maroon:focus{
    outline: none;
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--maroon) 30%, transparent);
    border-color: var(--maroon) !important;
  }

  /* Subtle card */
  .card {
    background: #fff;
    border: 1px solid #e7e7ea;
    border-radius: 16px;
    box-shadow: 0 6px 24px rgba(0,0,0,.05);
  }

  .chip {
    border: 1px solid #d8d8dd;
    border-radius: 10px;
    padding: 4px 10px;
    font-size: 12px;
    background: #fff;
    transition: background .2s ease, border-color .2s ease;
  }
  .chip:hover { background: #f7f7fb; }

  .btn-primary {
    background: var(--maroon);
    color: #fff;
    border-radius: 12px;
    padding: 10px 16px;
    transition: filter .15s ease, transform .02s ease, background .2s ease;
  }
  .btn-primary:hover { filter: brightness(1.08); }

  .btn-outline {
    border: 1px solid var(--maroon);
    color: var(--maroon);
    border-radius: 12px;
    padding: 8px 14px;
    background: color-mix(in oklab, var(--maroon) 10%, white);
  }

  .section-title{
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    color: #6b7280;
    text-transform: uppercase;
  }

  /* Input base */
  .input {
    width: 100%;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 12px;
    background: #fff;
    transition: box-shadow .2s ease, border-color .2s ease, background .2s ease;
  }

  .muted {
    color: #64748b;
    font-size: 12px;
  }
</style>

<div class="max-w-4xl mx-auto space-y-6" style="--brand-maroon:#610e1c;">
  {{-- Hero header --}}
  <div class="card p-5">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--maroon);">
          📝 Input Catatan Harian
        </h1>
        <p class="text-sm text-slate-600 mt-1">
          Catat hal penting hari ini. <span class="font-medium">note_time</span> disimpan otomatis (WIB).
        </p>
      </div>
      <a href="{{ route('user.daily_notes.index') }}" class="btn-outline">Kembali</a>
    </div>
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
      <div class="p-3 rounded-xl" style="background: var(--maroon-soft);">
        <div class="font-semibold">Tanggal (WIB)</div>
        <div class="text-slate-700">{{ now('Asia/Jakarta')->format('d M Y') }}</div>
      </div>
      <div class="p-3 rounded-xl" style="background: var(--maroon-soft);">
        <div class="font-semibold">Waktu (WIB)</div>
        <div class="text-slate-700">{{ now('Asia/Jakarta')->format('H:i') }}</div>
      </div>
      <div class="p-3 rounded-xl" style="background: var(--maroon-soft);">
        <div class="font-semibold">Pengguna</div>
        <div class="text-slate-700">{{ auth()->user()->name ?? '—' }}</div>
      </div>
    </div>
  </div>

  {{-- Alerts --}}
  @if(session('success'))
    <div class="card p-4 border-emerald-200" style="background: #ecfdf5;">
      <div class="text-emerald-800">{{ session('success') }}</div>
    </div>
  @endif
  @if ($errors->any())
    <div class="card p-4 border-rose-200" style="background: #fff1f2;">
      <div class="font-semibold mb-1" style="color:#be123c;">Periksa kembali:</div>
      <ul class="list-disc pl-5 space-y-1 text-sm" style="color:#9f1239;">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- Form --}}
  <form action="{{ route('user.daily_notes.store') }}" method="post" class="card p-6 space-y-6" x-data="noteForm()">
    @csrf

    {{-- Section: Konteks --}}
    <div class="space-y-3">
      <div class="section-title">Konteks</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Perusahaan --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Perusahaan (opsional)</label>
          <select name="company_id" id="company_id" class="input focus-maroon">
            <option value="">— Pilih Perusahaan —</option>
            @foreach(($companies ?? []) as $c)
              <option value="{{ $c->id }}" @selected(old('company_id')==$c->id)>
                {{ $c->code ?? 'CMP' }} — {{ $c->name }}
              </option>
            @endforeach
          </select>
          @error('company_id')<p class="text-sm mt-1" style="color:#b91c1c;">{{ $message }}</p>@enderror
        </div>

        {{-- Site (terfilter by perusahaan) --}}
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Site (opsional)</label>
          <select name="site_id" id="site_id" class="input focus-maroon" {{ old('company_id') ? '' : 'disabled' }}>
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
          <div class="muted mt-1">Daftar site mengikuti perusahaan yang dipilih.</div>
          @error('site_id')<p class="text-sm mt-1" style="color:#b91c1c;">{{ $message }}</p>@enderror
        </div>
      </div>
    </div>

    <hr class="border-t border-slate-200">

    {{-- Section: Konten --}}
    <div class="space-y-4">
      <div class="section-title">Konten</div>

      {{-- Judul --}}
      <div>
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium text-slate-700">Judul <span style="color:#dc2626">*</span></label>
          <span class="muted"><span x-text="title.length"></span>/255</span>
        </div>
        <input
          type="text"
          name="title"
          x-model="title"
          value="{{ old('title') }}"
          maxlength="255"
          placeholder="Ringkas & jelas, mis. 'Sync Produksi — Kendala Fuel Metering'"
          class="input focus-maroon @error('title') border-rose-500 @enderror">
        @error('title')<p class="text-sm mt-1" style="color:#b91c1c;">{{ $message }}</p>@enderror
      </div>

      {{-- Isi Catatan --}}
      <div>
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium text-slate-700">Isi Catatan <span style="color:#dc2626">*</span></label>
          <span class="muted"><span x-text="content.length"></span> karakter</span>
        </div>
        <textarea
          name="content"
          rows="8"
          x-model="content"
          x-ref="contentArea"
          @input="autoGrow()"
          placeholder="Tulis kronologi, keputusan, follow-up, atau highlight penting…"
          class="input focus-maroon font-[450] @error('content') border-rose-500 @enderror">{{ old('content') }}</textarea>
        @error('content')<p class="text-sm mt-1" style="color:#b91c1c;">{{ $message }}</p>@enderror

        {{-- Quick tags --}}
        <div class="mt-2 flex flex-wrap gap-2">
          @foreach(['[Meeting]','[Follow-up]','[Issue]','[Decision]','[Reminder]','[Risk]','[Blocking]'] as $chip)
            <button type="button" @click="append('{{ $chip }} ')"
              class="chip">{{ $chip }}</button>
          @endforeach
        </div>
      </div>
    </div>

    <hr class="border-t border-slate-200">

    {{-- Section: Waktu --}}
    <div class="space-y-3">
      <div class="section-title">Waktu</div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal & Waktu Saat Ini (WIB)</label>
          <input type="text" value="{{ now('Asia/Jakarta')->format('d-m-Y H:i') }}" class="input" readonly style="background:#f8fafc;">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">Akan Disimpan Sebagai</label>
          <input type="text" value="note_time = now('Asia/Jakarta')" class="input" readonly style="background:#f8fafc;">
        </div>
      </div>
      <div class="muted">
        * Catatan tersimpan atas nama <strong>{{ auth()->user()->name ?? 'Anda' }}</strong>.
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3 pt-2">
      <a href="{{ route('user.daily_notes.index') }}" class="btn-outline">Batal</a>
      <button type="submit" class="btn-primary inline-flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path d="M17 8a1 1 0 0 1-1 1H7.414l3.293 3.293a1 1 0 1 1-1.414 1.414L4.586 9.707a1 1 0 0 1 0-1.414L9.293 3.586a1 1 0 1 1 1.414 1.414L7.414 8H16a1 1 0 0 1 1 1Z"/>
        </svg>
        Simpan
      </button>
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
