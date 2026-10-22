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
<div class="w-full min-w-0 space-y-5">
  {{-- Header --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>Catatan Harian</x-ui.page-title>
      <x-ui.page-description>
        Rekap judul, deskripsi, tanggal & waktu (WIB), penulis, perusahaan & site.
      </x-ui.page-description>
    </div></div>
  <x-ui.page-actions><div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
      @if(Route::has('user.daily_notes.create'))
        <x-ui.link variant="primary" icon="plus" href="{{ route('user.daily_notes.create') }}">Catatan Baru</x-ui.link>
      @endif
    </div></x-ui.page-actions>

<x-slot:filters>
<x-ui.table-toolbar class="ui-daily-note-filters">
  <div class="ui-daily-note-filter ui-daily-note-filter--search">
    <label for="daily-note-search">Pencarian</label>
    <x-ui.table-search id="daily-note-search" name="q" :value="$query ?? ''" placeholder="Cari judul atau deskripsi…" />
  </div>
  <div class="ui-daily-note-filter">
    <label for="daily-note-date">Tanggal</label>
    <x-ui.form-input type="date" id="daily-note-date" name="date" :value="$targetDate ?? ''" />
  </div>
  <div class="ui-daily-note-filter">
    <label for="company_id">Perusahaan</label>
    <x-ui.form-select name="company_id" id="company_id">
      <option value="">Semua perusahaan</option>
      @foreach(($companies ?? []) as $c)
        <option value="{{ $c->id }}" @selected((string)$selectedCompany === (string)$c->id)>{{ $c->code }} — {{ $c->name }}</option>
      @endforeach
    </x-ui.form-select>
  </div>
  <div class="ui-daily-note-filter">
    <label for="site_id">Site</label>
    <x-ui.form-select name="site_id" id="site_id">
      <option value="">Semua site</option>
      @foreach(($sites ?? []) as $s)
        @if(!$selectedCompany || (string)$s->company_id === (string)$selectedCompany)
          <option value="{{ $s->id }}" @selected((string)$selectedSite === (string)$s->id)>{{ $s->name }}</option>
        @endif
      @endforeach
    </x-ui.form-select>
  </div>
  <div class="ui-daily-note-filter">
    <label for="daily-note-limit">Baris</label>
    <x-ui.form-select name="perPage" id="daily-note-limit">
      @foreach([10,25,50,100] as $opt)<option value="{{ $opt }}" @selected((int)($perPage ?? 10) === $opt)>{{ $opt }} / halaman</option>@endforeach
      <option value="all" @selected(($perPage ?? '') === 'all')>Semua</option>
    </x-ui.form-select>
  </div>
  <x-slot:actions>
    <x-ui.button type="submit" variant="primary" icon="filter">Terapkan</x-ui.button>
    <x-ui.link variant="secondary" :href="route('user.daily_notes.index').'?site_id='">Reset</x-ui.link>
  </x-slot:actions>
</x-ui.table-toolbar>
</x-slot:filters>
</x-ui.page-header>

  <div class="space-y-3">
    {{-- Tabel --}}
    <div>
      <x-ui.table :paginator="$notes" class="min-w-[760px] divide-y divide-gray-200">
        <thead>
          <tr class="text-left text-sm">
            <th class="px-4 py-3 font-semibold">Judul</th>
            <th class="px-4 py-3 font-semibold">Deskripsi</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Tanggal (WIB)</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Waktu (WIB)</th>
            <th class="px-4 py-3 font-semibold">Oleh</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Perusahaan</th>
            <th class="px-4 py-3 font-semibold whitespace-nowrap">Site</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @forelse($notes as $n)
            @php
              $wib = $n->note_time?->timezone('Asia/Jakarta');
            @endphp
            <tr class="text-sm align-top">
              <td class="px-4 py-3 font-medium">{{ $n->title }}</td>
              <td class="px-4 py-3"><div class="ui-truncated-text" title="{{ $n->content }}">{{ $n->content }}</div></td>
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
              <td class="ui-action-cell"><x-ui.table-actions><x-ui.icon-button action="view" label="Lihat deskripsi lengkap" :data-dialog-open="'daily-note-'.$n->id" /></x-ui.table-actions></td>
            </tr>
            @push('modals')
              <x-ui.modal-detail :id="'daily-note-'.$n->id" :title="$n->title" description="Detail catatan harian" size="lg">
                <x-ui.detail-section title="Informasi catatan">
                  <x-ui.detail-row label="Penulis">{{ $n->user?->name ?? '—' }}</x-ui.detail-row>
                  <x-ui.detail-row label="Waktu (WIB)">{{ $wib?->format('d M Y H:i') ?? '—' }}</x-ui.detail-row>
                  <x-ui.detail-row label="Perusahaan">{{ $n->company?->name ?? '—' }}</x-ui.detail-row>
                  <x-ui.detail-row label="Site">{{ $n->site?->name ?? '—' }}</x-ui.detail-row>
                </x-ui.detail-section>
                <x-ui.detail-section title="Deskripsi"><div class="whitespace-pre-wrap break-words text-sm">{{ $n->content }}</div></x-ui.detail-section>
              </x-ui.modal-detail>
            @endpush
          @empty
<x-ui.table-empty-state colspan="8"></x-ui.table-empty-state>
@endforelse
        </tbody>
      </x-ui.table>
    </div>
  </div>

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
    const rows = companyId ? (SITES_BY_COMPANY[String(companyId)] || []) : Object.values(SITES_BY_COMPANY).flat();
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
  repopulateSites(currentCid, currentSid);
  // Change: reset site ketika company berubah
  if (companySel) {
    companySel.addEventListener('change', function(){
      repopulateSites(this.value, null);
    });
  }
})();
</script>
@endsection