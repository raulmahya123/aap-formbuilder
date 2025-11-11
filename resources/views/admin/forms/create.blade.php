{{-- resources/views/admin/forms/create.blade.php --}}
@extends('layouts.app')

@section('content')
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

  @php
    // default aman: ambil dari old(), kalau kosong coba dari ?doc_type=, lalu fallback ke SOP
    $selectedDocType = old('doc_type', request('doc_type', 'SOP'));
    $selectedType    = old('type', 'builder'); // default tampilan pertama: builder

    // Siapkan dataset sites by company untuk JS
    /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
    $sitesByCompany = collect($sites ?? [])->groupBy('company_id')->map(function($rows){
      return $rows->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'company_id'=>$s->company_id])->values();
    })->toArray();
  @endphp

  <form action="{{ route('admin.forms.store') }}" method="post" enctype="multipart/form-data" id="form-create">
    @csrf

    {{-- ===== Perusahaan ===== --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Perusahaan <span class="text-rose-600">*</span></label>
      <select name="company_id" id="company_id" class="border rounded w-full p-2" required>
        <option value="" disabled {{ old('company_id') ? '' : 'selected' }}>Pilih perusahaan…</option>
        @foreach(($companies ?? []) as $c)
          <option value="{{ $c->id }}" @selected((string)old('company_id') === (string)$c->id)>
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
      <select name="site_id" id="site_id" class="border rounded w-full p-2">
        <option value="">— Tanpa Site —</option>
        {{-- options akan diisi via JS berdasar company terpilih, namun kita render juga default dari old() agar aman --}}
        @if(old('company_id') && !empty($sitesByCompany[(int)old('company_id')]))
          @foreach($sitesByCompany[(int)old('company_id')] as $s)
            <option value="{{ $s['id'] }}" @selected((string)old('site_id') === (string)$s['id'])>{{ $s['name'] }}</option>
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

  // sitesByCompany di-inject dari PHP (array: company_id -> [{id,name,company_id},...])
  const sitesByCompany = @json($sitesByCompany);
  const oldSiteId = "{{ old('site_id') }}";

  function repopulateSites() {
    const compId = companySel.value ? String(companySel.value) : '';
    const rows   = sitesByCompany[compId] || sitesByCompany[parseInt(compId)] || [];

    // Clear options
    while (siteSel.options.length) siteSel.remove(0);

    // Append default
    siteSel.appendChild(new Option('— Tanpa Site —', ''));

    if (rows.length === 0) {
      siteSel.disabled = false; // tetap bisa set null
      return;
    }

    rows.forEach(r => {
      const opt = new Option(r.name, r.id);
      if (oldSiteId && String(oldSiteId) === String(r.id)) {
        opt.selected = true;
      }
      siteSel.appendChild(opt);
    });

    siteSel.disabled = false;
  }

  // Build map key agar bisa diakses dengan string key juga
  // (Blade sudah menghasilkan array, tetapi jaga-jaga)
  const normalized = {};
  Object.keys(sitesByCompany || {}).forEach(k => {
    normalized[String(k)] = sitesByCompany[k];
  });
  // assign balik
  // eslint-disable-next-line no-global-assign
  sitesByCompany = normalized;

  companySel.addEventListener('change', function(){
    // reset oldSiteId saat user mengganti company
    window.setTimeout(()=>{ repopulateSites(); }, 0);
  });

  // Init on load jika company sudah dipilih (old)
  if (companySel.value) repopulateSites();
})();
</script>
@endsection
