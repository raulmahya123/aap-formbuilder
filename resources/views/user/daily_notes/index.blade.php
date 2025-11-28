@extends('layouts.app')
@section('title','Catatan Harian')

@section('content')
@php
  /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
  // Map sites per company (key string supaya konsisten di JS)
  $sitesByCompany = collect($sites ?? [])->groupBy(fn($s) => (string)$s->company_id)
    ->map(fn($rows) => $rows->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'company_id'=>$s->company_id])->values())
    ->toArray();

  // Nilai terpilih untuk filter (dari controller atau query-string)
  $selectedCompany = isset($companyId) ? (string)$companyId : (string) request('company_id', '');
  $selectedSite    = isset($siteId)    ? (string)$siteId    : (string) request('site_id', '');
@endphp

<style>
  :root{
    /* Maroon lebih gelap (fallback jika --brand-maroon tidak diset secara global) */
    --maroon: var(--brand-maroon, #5a0d1a);
  }
  .btn {
    border-radius: 12px;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    background: #fff;
    transition: filter .2s ease, background .2s ease, border-color .2s ease;
  }
  .btn:hover { background: #f8fafc; }
  .btn-primary{
    background: var(--maroon);
    color: #fff;
    border: none;
  }
  .btn-primary:hover{ filter: brightness(1.06); }
  .chip{
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 10px;
    background: #fff;
    font-size: 12px;
  }
  .card{
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 6px 24px rgba(0,0,0,.05);
  }
  .input{
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 8px 12px;
    background: #fff;
    transition: box-shadow .2s ease, border-color .2s ease;
  }
  .input:focus{
    outline: none;
    /* tingkatkan kontras fokus */
    box-shadow: 0 0 0 3px color-mix(in oklab, var(--maroon) 30%, transparent);
    border-color: var(--maroon);
  }
</style>

<div class="max-w-6xl mx-auto space-y-6">
  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-end gap-3 justify-between">
    <div>
      <h1 class="text-2xl font-extrabold tracking-tight" style="color:var(--maroon);">
        📝 Catatan Harian
      </h1>
      <p class="text-sm text-slate-600">
        Rekap judul, deskripsi, tanggal & waktu (WIB), penulis, perusahaan & site.
      </p>
    </div>

    <div class="flex items-center gap-2">
      <form method="get" class="flex flex-wrap items-center gap-2">
        {{-- Tanggal --}}
        <input type="date" name="date"
               value="{{ $targetDate ?? now('Asia/Jakarta')->format('Y-m-d') }}"
               class="input">

        {{-- Pencarian --}}
        <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Cari judul / isi…"
               class="input" />

        {{-- Perusahaan --}}
        <select name="company_id" id="company_id" class="input">
          <option value="">Semua Perusahaan</option>
          @foreach(($companies ?? []) as $c)
            <option value="{{ $c->id }}" @selected((string)$selectedCompany === (string)$c->id)>
              {{ $c->code ?? 'CMP' }} — {{ $c->name }}
            </option>
          @endforeach
        </select>

        {{-- Site (auto-filter by company) --}}
        <select name="site_id" id="site_id" class="input" {{ $selectedCompany ? '' : 'disabled' }}>
          <option value="">Semua Site</option>
          @if($selectedCompany !== '' && !empty($sitesByCompany[(string)$selectedCompany]))
            @foreach($sitesByCompany[(string)$selectedCompany] as $s)
              <option value="{{ $s['id'] }}" @selected((string)$selectedSite === (string)$s['id'])>{{ $s['name'] }}</option>
            @endforeach
          @endif
        </select>

        {{-- perPage --}}
        <select name="perPage" class="input">
          <option value="all" {{ ($perPage ?? '') === 'all' ? 'selected' : '' }}>Semua</option>
          @foreach([10,25,50,100] as $opt)
            <option value="{{ $opt }}" {{ ($perPage ?? 10) == $opt ? 'selected' : '' }}>{{ $opt }}/hal</option>
          @endforeach
        </select>

        <button class="btn">Filter</button>

        @if(request()->hasAny(['date','q','perPage','company_id','site_id']))
          <a href="{{ route('user.daily_notes.index') }}" class="text-sm underline">Reset</a>
        @endif
      </form>

      @if(Route::has('user.daily_notes.create'))
        <a href="{{ route('user.daily_notes.create') }}"
           class="btn"
           style="border-color:var(--maroon); color:var(--maroon);
                  background: color-mix(in oklab, var(--maroon) 10%, white);">
          ➕ Catatan Baru
        </a>
      @endif
    </div>
  </div>

  {{-- (Opsional) Alpine.js CDN jika belum ada --}}
  <script>
    if (!window.alpineLoaded) {
      const s = document.createElement('script');
      s.defer = true;
      s.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
      s.onload = () => window.alpineLoaded = true;
      document.head.appendChild(s);
    }
  </script>

  {{-- Clamp util (2 baris) --}}
  <style>
    .clamp-2 {
      line-height: 1.4;
      max-height: calc(1.4em * 2);
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      word-break: break-word;
    }
  </style>

  <div x-data="{
        descSize: Number(localStorage.getItem('descSize') ?? 14),
        save(){ localStorage.setItem('descSize', this.descSize) }
      }"
      class="space-y-3">

    {{-- Kontrol ukuran teks deskripsi --}}
    <div class="flex items-center gap-2">
      <span class="text-sm text-gray-600">
        Ukuran teks Deskripsi: <span class="font-medium" x-text="descSize + 'px'"></span>
      </span>

      <button @click="descSize = Math.max(10, descSize - 1); save()"
        class="btn" aria-label="Perkecil">−</button>

      <input type="range" min="10" max="24" step="1" x-model.number="descSize" @input="save()"
        class="w-40" style="accent-color: var(--maroon);" aria-label="Geser ukuran deskripsi" />

      <button @click="descSize = Math.min(24, descSize + 1); save()"
        class="btn" aria-label="Perbesar">+</button>

      <button @click="descSize = 14; save()" class="btn">Reset</button>
    </div>

    {{-- Tabel --}}
    <div class="overflow-hidden card">
      <table class="min-w-full divide-y divide-gray-200">
        <thead style="background: color-mix(in oklab, var(--maroon) 14%, white);">
          <tr class="text-left text-sm">
            <th class="px-4 py-3 font-semibold">Judul</th>
            <th class="px-4 py-3 font-semibold" :style="`font-size:${descSize + 2}px`">Deskripsi</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal (WIB)</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu (WIB)</th>
            <th class="px-4 py-3 font-semibold">Oleh</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Perusahaan</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Site</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          @forelse($notes as $n)
            @php
              $wib = $n->note_time?->timezone('Asia/Jakarta');
            @endphp
            <tr class="text-sm align-top">
              <td class="px-4 py-3 font-medium">{{ $n->title }}</td>

              {{-- Deskripsi: textarea readonly supaya bisa discroll jika panjang --}}
              <td class="px-4 py-3 align-top" :style="`font-size:${descSize}px`">
                <textarea
                  readonly
                  rows="2"
                  class="w-full resize-y overflow-auto min-h-[2.6em] max-h-[32rem]
                         border border-gray-200 rounded-md p-2 bg-transparent"
                  :style="`font-size:${descSize}px; line-height:1.4;`"
                >{{ $n->content }}</textarea>
              </td>

              <td class="px-4 py-3 whitespace-nowrap">
                {{ $wib ? $wib->format('d/m/Y') : '—' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ $wib ? $wib->format('H:i') : '—' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ $n->user->name ?? '—' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                @if(!empty($n->company))
                  {{ $n->company->code ?? '' }} — {{ $n->company->name ?? '' }}
                @else
                  —
                @endif
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ $n->site->name ?? '—' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-slate-500">
                Belum ada catatan untuk kriteria ini.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if(method_exists($notes, 'hasPages') && $notes->hasPages())
    <div>
      {{ $notes->appends(request()->except('page'))->links() }}
    </div>
  @endif
</div>

{{-- Dataset untuk JS filter site (AMAN: JSON dipassing via data-* lalu diparse) --}}
<div id="dn-dataset"
     data-sites='@json($sitesByCompany)'
     data-company='@json($selectedCompany)'
     data-site='@json($selectedSite)'></div>

<script>
(function(){
  const meta = document.getElementById('dn-dataset');
  const mapRaw = JSON.parse(meta.dataset.sites || '{}');
  const currentCid = String(meta.dataset.company || '');
  const currentSid = String(meta.dataset.site || '');

  // normalisasi key jadi string
  const SITES_BY_COMPANY = {};
  Object.keys(mapRaw).forEach(k => SITES_BY_COMPANY[String(k)] = mapRaw[k]);

  const companySel = document.getElementById('company_id');
  const siteSel    = document.getElementById('site_id');

  function repopulateSites(companyId, keepSiteId){
    const rows = SITES_BY_COMPANY[String(companyId)] || [];

    // clear
    while (siteSel.options.length) siteSel.remove(0);

    // default
    siteSel.appendChild(new Option('Semua Site',''));

    // fill
    rows.forEach(r => {
      const opt = new Option(r.name, r.id);
      if (keepSiteId && String(keepSiteId) === String(r.id)) opt.selected = true;
      siteSel.appendChild(opt);
    });

    siteSel.disabled = false;
  }

  // Init: populate jika company terpilih (dari query string)
  if (currentCid !== '') repopulateSites(currentCid, currentSid);

  // Change: reset site ketika company berubah
  if (companySel) {
    companySel.addEventListener('change', function(){
      repopulateSites(this.value, null);
    });
  }
})();
</script>
@endsection
