{{-- resources/views/admin/forms/create.blade.php --}}
@extends('layouts.app')

@section('content')
@php
  // ==== Defaults & dataset untuk JS ====
  $selectedDocType    = old('doc_type', request('doc_type', 'SOP'));
  $selectedType       = old('type', 'builder');
  $selectedCompanyId  = old('company_id', request('company_id'));
  $selectedSiteId     = old('site_id');

  /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
  // Buat key company_id jadi STRING agar aman di JS saat akses SITES_BY_COMPANY[String(id)]
  $sitesByCompany = collect($sites ?? [])->groupBy(function($s){
    return (string) $s->company_id;
  })->map(function($rows){
    return $rows->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'company_id'=>$s->company_id])->values();
  })->toArray();
@endphp

<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl">
  <h1 class="text-xl font-semibold mb-4">Buat Form</h1>

  {{-- Flash + error --}}
  @if (session('ok'))
    <div class="mb-4 p-3 rounded bg-emerald-50 text-emerald-700">{{ session('ok') }}</div>
  @endif
  @if ($errors->any())
    <div class="mb-4 p-3 rounded bg-red-50 text-red-700">
      <div class="font-semibold mb-1">Periksa kembali:</div>
      <ul class="list-disc pl-5 space-y-1 text-sm">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('admin.forms.store') }}" method="post" enctype="multipart/form-data" id="form-create">
    @csrf

    {{-- ===== Perusahaan ===== --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Perusahaan <span class="text-rose-600">*</span></label>
      <select name="company_id" id="company_id" class="border rounded w-full p-2" required>
        <option value="" disabled {{ $selectedCompanyId ? '' : 'selected' }}>Pilih perusahaan…</option>
        @foreach(($companies ?? []) as $c)
          <option value="{{ $c->id }}" @selected((string)$selectedCompanyId === (string)$c->id)>
            {{ $c->code ?? '—' }} — {{ $c->name }}
          </option>
        @endforeach
      </select>
      @error('company_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Form ini akan dimiliki oleh perusahaan yang dipilih.</p>
    </div>

    {{-- ===== Site (opsional, otomatis filter by company) ===== --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Site (Opsional)</label>
      <select name="site_id" id="site_id" class="border rounded w-full p-2" {{ $selectedCompanyId ? '' : 'disabled' }}>
        <option value="">— Tanpa Site —</option>
        {{-- Render awal (kalau ada default company) --}}
        @if($selectedCompanyId && !empty($sitesByCompany[(string)$selectedCompanyId]))
          @foreach($sitesByCompany[(string)$selectedCompanyId] as $s)
            <option value="{{ $s['id'] }}" @selected((string)$selectedSiteId === (string)$s['id'])>{{ $s['name'] }}</option>
          @endforeach
        @endif
      </select>
      @error('site_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Opsional: hubungkan form ke site tertentu dalam perusahaan.</p>
    </div>

    {{-- ===== Department ===== --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Department <span class="text-rose-600">*</span></label>
      <select name="department_id" class="border rounded w-full p-2" required>
        @foreach($departments as $d)
          <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
      @error('department_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- ===== Judul ===== --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Judul <span class="text-rose-600">*</span></label>
      <input type="text" name="title" class="border rounded w-full p-2" value="{{ old('title') }}" required maxlength="190">
      @error('title')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- === Jenis Dokumen (SOP/IK/FORM) === --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Jenis Dokumen <span class="text-rose-600">*</span></label>
      <select name="doc_type" id="doc_type" class="border rounded w-full p-2" required>
        <option value="SOP"  @selected($selectedDocType === 'SOP')>SOP</option>
        <option value="IK"   @selected($selectedDocType === 'IK')>IK</option>
        <option value="FORM" @selected($selectedDocType === 'FORM')>FORM</option>
      </select>
      @error('doc_type')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Kategori dokumen untuk form ini.</p>
    </div>

    {{-- ===== Tipe ===== --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Tipe <span class="text-rose-600">*</span></label>
      <select name="type" id="type" class="border rounded w-full p-2" required>
        <option value="builder" @selected($selectedType === 'builder')>Builder</option>
        <option value="pdf"     @selected($selectedType === 'pdf')>File (PDF/Word/Excel)</option>
      </select>
      @error('type')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
    </div>

    {{-- ===== Builder Box ===== --}}
    <div id="builderBox" class="mb-3">
      <label class="block font-medium mb-1">Schema (JSON)</label>
      <textarea name="schema" rows="10" class="border rounded w-full p-2">{{ old('schema', json_encode([
        'fields' => [
          ['label'=>'Nama','name'=>'nama','type'=>'text','required'=>true],
          ['label'=>'Email','name'=>'email','type'=>'email'],
          ['label'=>'Tanggal','name'=>'tanggal','type'=>'date'],
          ['label'=>'Keterangan','name'=>'keterangan','type'=>'textarea']
        ]
      ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}</textarea>
      @error('schema')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Tip: isian fleksibel, sesuaikan sesuai kebutuhan.</p>
    </div>

    {{-- ===== File Box (PDF/Word/Excel) ===== --}}
    <div id="pdfBox" class="mb-3 hidden">
      <label class="block font-medium mb-1">Unggah File (PDF/Word/Excel)</label>
      <input
        id="pdfInput"
        type="file"
        name="pdf"
        accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        class="border rounded w-full p-2"
      >
      @error('pdf')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Format: PDF, DOC/DOCX, XLS/XLSX — maks 30&nbsp;MB. File akan dikompresi otomatis saat disimpan.</p>
    </div>

    {{-- Aktif --}}
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
      <span>Aktif</span>
    </label>

    <div class="mt-4">
      <button class="px-4 py-2 bg-emerald-600 text-white rounded">Simpan</button>
    </div>
  </form>
</div>

{{-- Dataset untuk JS --}}
<script>
(function () {
  // ====== Toggle Builder/PDF ======
  const typeSel    = document.getElementById('type');
  const pdfBox     = document.getElementById('pdfBox');
  const builderBox = document.getElementById('builderBox');
  const pdfInput   = document.getElementById('pdfInput');

  function toggleBoxes() {
    const isFile = typeSel.value === 'pdf';
    pdfBox.classList.toggle('hidden', !isFile);
    builderBox.classList.toggle('hidden', isFile);

    if (pdfInput) {
      if (isFile) {
        pdfInput.setAttribute('required', 'required');
      } else {
        pdfInput.removeAttribute('required');
        pdfInput.value = '';
      }
    }
  }
  typeSel.addEventListener('change', toggleBoxes);
  toggleBoxes();

  // ====== Filter Site by Company ======
  const companySel = document.getElementById('company_id');
  const siteSel    = document.getElementById('site_id');

  // { "<company_id:string>": [{id,name,company_id}, ...] }
  const SITES_BY_COMPANY = @json($sitesByCompany);
  const initialCompanyId = "{{ (string) $selectedCompanyId }}";
  const initialSiteId    = "{{ (string) $selectedSiteId }}";

  function getSitesForCompany(id) {
    if (!id) return [];
    const key = String(id);
    return SITES_BY_COMPANY[key] || [];
  }

  function repopulateSites(prefillSiteId = null) {
    const compId = companySel.value;
    const rows   = getSitesForCompany(compId);

    // Clear options
    while (siteSel.options.length) siteSel.remove(0);

    // Default option (null)
    siteSel.appendChild(new Option('— Tanpa Site —', ''));

    rows.forEach(r => {
      const opt = new Option(r.name, r.id);
      // pilih ulang berdasarkan prefill (old() / initial)
      if (prefillSiteId && String(prefillSiteId) === String(r.id)) {
        opt.selected = true;
      }
      siteSel.appendChild(opt);
    });

    siteSel.disabled = false;
  }

  companySel.addEventListener('change', () => {
    // Reset site ketika ganti company
    repopulateSites(null);
  });

  // Init on load
  if (companySel.value) {
    // Jika sudah ada default company (old()/query), prefill site juga bila ada
    repopulateSites(initialSiteId || null);
  } else {
    // Kalau belum pilih perusahaan, kunci dropdown site
    siteSel.disabled = true;
  }

  // (Opsional) debug:
  // console.log('SITES_BY_COMPANY', SITES_BY_COMPANY);
})();
</script>
@endsection
