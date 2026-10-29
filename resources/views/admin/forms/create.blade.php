{{-- resources/views/admin/forms/create.blade.php --}}
@extends('layouts.app')
@section('content')
@php
  // ==== Defaults & dataset untuk JS ====
  $selectedDocType    = old('doc_type', request('doc_type', 'SOP'));
  $selectedType       = old('type', 'builder');
  $selectedCompanyId  = old('company_id', request('company_id'));
  $selectedSiteId     = old('site_id', request('site_id'));
  $selectedCompanyId  = $selectedCompanyId ?: collect($sites ?? [])->firstWhere('id', $selectedSiteId)?->company_id;
  /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
  // Buat key company_id jadi STRING agar aman di JS saat akses SITES_BY_COMPANY[String(id)]
  $sitesByCompany = collect($sites ?? [])->groupBy(function($s){
    return (string) $s->company_id;
  })->map(function($rows){
    return $rows->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'company_id'=>$s->company_id])->values();
  })->toArray();
@endphp
<div class="w-full min-w-0 bg-white rounded-lg">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Buat Form</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan form dan dokumen.</x-ui.page-description></div>
  <x-ui.page-actions>
<x-ui.button form="form-create" variant="primary" >Simpan</x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>
  {{-- Flash + error --}}
  @if ($errors->any())
    <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700">
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
      <label class="block">Perusahaan <span class="text-rose-600">*</span></label>
      <select name="company_id" id="company_id" class="w-full ui-input" required>
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
  {{-- ===== Site assignment (otomatis filter by company) ===== --}}
    <div class="mb-3">
      <label class="block">Site <span class="text-rose-600">*</span></label>
      <select name="site_id" id="site_id" required class="w-full ui-input" {{ $selectedCompanyId ? '' : 'disabled' }}>
        <option value="" disabled>— Pilih Site —</option>
        {{-- Render awal (kalau ada default company) --}}
        @if($selectedCompanyId && !empty($sitesByCompany[(string)$selectedCompanyId]))
          @foreach($sitesByCompany[(string)$selectedCompanyId] as $s)
            <option value="{{ $s['id'] }}" @selected((string)$selectedSiteId === (string)$s['id'])>{{ $s['name'] }}</option>
          @endforeach
        @endif
      </select>
      @error('site_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Form hanya dapat diakses user yang memiliki assignment ke site ini.</p>
    </div>
    {{-- ===== Department ===== --}}
    <div class="mb-3">
      <label class="block">Department <span class="text-rose-600">*</span></label>
      <select name="department_id" class="w-full ui-input" required>
        @foreach($departments as $d)
          <option value="{{ $d->id }}" @selected(old('department_id', request('department_id')) == $d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
      @error('department_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
    </div>
    {{-- ===== Judul ===== --}}
    <div class="mb-3">
      <label class="block">Judul <span class="text-rose-600">*</span></label>
      <input type="text" name="title" class="w-full ui-input" value="{{ old('title') }}" required maxlength="190">
      @error('title')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
    </div>
    {{-- === Jenis Dokumen (SOP/IK/FORM) === --}}
    <div class="mb-3">
      <label class="block">Jenis Dokumen <span class="text-rose-600">*</span></label>
      <select name="doc_type" id="doc_type" class="w-full ui-input" required>
        <option value="SOP"  @selected($selectedDocType === 'SOP')>SOP</option>
        <option value="IK"   @selected($selectedDocType === 'IK')>IK</option>
        <option value="FORM" @selected($selectedDocType === 'FORM')>FORM</option>
      </select>
      @error('doc_type')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Kategori dokumen untuk form ini.</p>
    </div>
    {{-- ===== Tipe ===== --}}
    <div class="mb-3">
      <label class="block">Tipe <span class="text-rose-600">*</span></label>
      <select name="type" id="type" class="w-full ui-input" required>
        <option value="builder" @selected($selectedType === 'builder')>Builder</option>
        <option value="pdf"     @selected($selectedType === 'pdf')>File (PDF/Word/Excel)</option>
      </select>
      @error('type')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
    </div>
    {{-- ===== Builder Box ===== --}}
    <div id="builderBox" class="mb-3">
      <label class="block">Schema (JSON)</label>
      <textarea name="schema" rows="10" class="w-full ui-input">{{ old('schema', json_encode([
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
      <label class="block">Unggah File (PDF/Word/Excel)</label>
      <input
        id="pdfInput"
        type="file"
        name="pdf"
        accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        class="w-full"
      >
      @error('pdf')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      <p class="text-sm text-slate-500 mt-1">Format: PDF, DOC/DOCX, XLS/XLSX — maks 4&nbsp;MB. File akan dikompresi otomatis saat disimpan.</p>
    </div>
    {{-- Aktif --}}
    <label class="inline-flex items-center">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
      <span>Aktif</span>
    </label>
    <div class="mt-4">
    </div>
  <script type="application/json" data-form-sites>@json($sitesByCompany)</script>
  </form>
</div>
{{-- Dataset untuk JS --}}
@endsection
