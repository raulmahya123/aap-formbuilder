{{-- resources/views/admin/forms/edit.blade.php --}}
@extends('layouts.app')

@section('title','Edit Form: '.$form->title)

@section('content')
@php
  // ===== Defaults dari old()/model =====
  $selectedDocType   = old('doc_type', $form->doc_type);
  $currentCompanyId  = (string) old('company_id', $form->company_id);
  $currentSiteId     = (string) old('site_id', $form->site_id);

  /** @var \Illuminate\Support\Collection|\App\Models\Site[] $sites */
  // Paksa key groupBy ke STRING → JS akses SITES_BY_COMPANY[String(id)] konsisten
  $sitesByCompany = collect($sites ?? [])->groupBy(function($s){
    return (string) $s->company_id;
  })->map(function($rows){
    return $rows->map(fn($s)=>['id'=>$s->id,'name'=>$s->name,'company_id'=>$s->company_id])->values();
  })->toArray();
@endphp

<div class="max-w-4xl mx-auto">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Edit Form</x-ui.page-title>
<x-ui.page-description>Perbarui informasi form dan dokumen.</x-ui.page-description></div>
  <x-ui.page-actions><div class="flex items-center gap-2">
      @if($form->type === 'builder' && Route::has('admin.forms.builder'))
        <x-ui.link variant="secondary" href="{{ route('admin.forms.builder', $form) }}"
           class="px-3 py-2 rounded-lg border text-sm">
          Open Builder
        </x-ui.link>
      @endif
      <x-ui.link variant="secondary" href="{{ route('admin.forms.index') }}"
         class="px-3 py-2 rounded-lg border text-sm">Kembali</x-ui.link>
    </div></x-ui.page-actions>
</x-ui.page-header>

  {{-- Flash --}}
  @if (session('ok'))
    <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 px-4 py-3">
      {{ session('ok') }}
    </div>
  @endif

  {{-- Errors --}}
  @if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 px-4 py-3">
      <div class="font-medium">Terjadi kesalahan:</div>
      <ul class="list-disc pl-5 mt-2 space-y-1">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Update --}}
  <form method="POST"
        action="{{ route('admin.forms.update', $form) }}"
        enctype="multipart/form-data"
        x-data='@json(["type" => old("type", $form->type)])'>
    @csrf
    @method('PUT')

    <x-ui.panel class="rounded-lg border bg-white space-y-5">

      {{-- Company --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Perusahaan <span class="text-rose-600">*</span></label>
        <select name="company_id" id="company_id" class="mt-1 w-full rounded-lg border px-3 py-2" required>
          @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected((string)old('company_id',$form->company_id)===(string)$c->id)>
              {{ $c->code }} — {{ $c->name }}
            </option>
          @endforeach
        </select>
        @error('company_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Site assignment (ter-filter by company) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Site <span class="text-rose-600">*</span></label>
        <select name="site_id" id="site_id"
                class="mt-1 w-full rounded-lg border px-3 py-2"
                {{ $currentCompanyId ? '' : 'disabled' }}>
          <option value="" disabled>— Pilih Site —</option>
          @if($currentCompanyId !== '' && !empty($sitesByCompany[(string)$currentCompanyId]))
            @foreach($sitesByCompany[(string)$currentCompanyId] as $s)
              <option value="{{ $s['id'] }}" @selected((string)$currentSiteId === (string)$s['id'])>{{ $s['name'] }}</option>
            @endforeach
          @endif
        </select>
        @error('site_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        <p class="text-xs text-slate-500 mt-1">Form hanya dapat diakses user yang memiliki assignment ke site ini.</p>
      </div>

      {{-- Department --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Departemen <span class="text-rose-600">*</span></label>
        <select name="department_id" class="mt-1 w-full rounded-lg border px-3 py-2" required>
          @foreach($departments as $d)
            <option value="{{ $d->id }}" @selected((string)old('department_id',$form->department_id)===(string)$d->id)>
              {{ $d->name }}
            </option>
          @endforeach
        </select>
        @error('department_id')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Title --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Judul <span class="text-rose-600">*</span></label>
        <input type="text" name="title" value="{{ old('title',$form->title) }}"
               class="mt-1 w-full rounded-lg border px-3 py-2" required maxlength="190">
        @error('title')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Jenis Dokumen (SOP/IK/FORM) --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Jenis Dokumen <span class="text-rose-600">*</span></label>
        <select name="doc_type" id="doc_type" class="mt-1 w-full rounded-lg border px-3 py-2" required>
          <option value="SOP"  @selected($selectedDocType==='SOP')>SOP</option>
          <option value="IK"   @selected($selectedDocType==='IK')>IK</option>
          <option value="FORM" @selected($selectedDocType==='FORM')>FORM</option>
        </select>
        @error('doc_type')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        <p class="text-xs text-slate-500 mt-1">Kategori dokumen. Tidak mengubah tipe implementasi di bawah.</p>
      </div>

      {{-- Type --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Tipe <span class="text-rose-600">*</span></label>
        <select name="type" x-model="type" class="mt-1 w-full rounded-lg border px-3 py-2" required>
          <option value="builder">Builder</option>
          <option value="pdf">File (PDF/Word/Excel)</option>
        </select>
        @error('type')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Builder schema --}}
      <div x-show="type==='builder'">
        <label class="block text-sm font-medium text-slate-700">Schema (JSON)</label>
        <textarea name="schema" rows="10"
          class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-sm"
          placeholder='{"fields":[...]}'>{!!
            json_encode(
              is_array(old('schema')) ? old('schema') : (old('schema') ?: ($form->schema ?? ['fields'=>[]])),
              JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE
            )
          !!}</textarea>
        @error('schema')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        <p class="text-xs text-slate-500 mt-1">
          Isi dengan objek JSON berisi key <code>fields</code> (array).
        </p>

        {{-- Import CSV --}}
        <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg">
          <p class="text-sm font-medium text-slate-700 mb-2">Import Soal dari CSV</p>
          <div class="flex items-center gap-3">
            <x-ui.link variant="secondary" href="{{ route('admin.forms.import-template') }}"
               class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium border rounded-lg">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              Download Template
            </x-ui.link>
            <form action="{{ route('admin.forms.import-schema', $form) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
              @csrf
              <input type="file" name="csv_file" accept=".csv,.txt" class="text-xs file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
              <x-ui.button variant="primary" type="submit" class="px-3 py-2 text-xs font-medium rounded-lg">
                Import
              </x-ui.button>
            </form>
          </div>
          <p class="text-xs text-slate-400 mt-2">Format: no, pertanyaan, tipe (text/radio/checkbox/select/textarea/number/date/email/file), wajib (ya/tidak), opsi_value, opsi_label, placeholder, rules</p>
        </div>
      </div>

      {{-- File (PDF/Word/Excel) --}}
      <div x-show="type==='pdf'">
        <label class="block text-sm font-medium text-slate-700">Unggah File (opsional untuk ganti)</label>
        <input type="file" name="pdf" id="pdfInput"
               accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
               class="mt-1 block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
        <p class="text-xs text-slate-500 mt-1">
          Format: PDF, DOC/DOCX, XLS/XLSX — maks 4 MB. File akan dikompresi otomatis saat disimpan.
        </p>

        @if($form->pdf_path)
          <p class="text-xs text-slate-600 mt-1">
            File sekarang:
            <a class="underline" target="_blank"
               href="{{ route('pubfile.stream', ['path' => $form->pdf_path]) }}">
              {{ basename($form->pdf_path) }}
            </a>
            <span class="text-slate-400">({{ $form->pdf_path }})</span>
          </p>
        @endif
        @error('pdf')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Active --}}
      <div class="flex items-center gap-2">
        <input type="checkbox" id="active" name="is_active" value="1"
               @checked(old('is_active', $form->is_active))>
        <label for="active" class="text-sm">Aktif</label>
      </div>

    </x-ui.panel>

    <div class="mt-6 flex items-center gap-3">
      <x-ui.button variant="primary" class="px-5 py-3 rounded-lg hover:brightness-105">
        Simpan Perubahan
      </x-ui.button>
    </div>
  </form>

  {{-- Delete --}}
  <form method="POST"
        action="{{ route('admin.forms.destroy', $form) }}"
        class="mt-4"
        data-confirm="Hapus form ini? Tindakan tidak bisa dibatalkan.">
    @csrf @method('DELETE')
    <x-ui.button variant="danger" class="px-4 py-2 rounded-lg border">
      Hapus
    </x-ui.button>
  </form>
</div>

{{-- Bootstrap data untuk JS --}}
<div id="form-data"
     data-sites='@json($sitesByCompany)'
     data-company="{{ $currentCompanyId }}"
     data-site="{{ $currentSiteId }}"></div>

<script>
  (function(){
    const meta       = document.getElementById('form-data');
    const mapRaw     = JSON.parse(meta.dataset.sites || '{}');

    // company & site JANGAN pakai @@json untuk scalar (ESCAPED agar Blade tidak memproses)
    const currentCid = String(meta.dataset.company || '');
    const currentSid = String(meta.dataset.site || '');

    // Normalisasi key map ke string
    const sitesByCompany = {};
    Object.keys(mapRaw).forEach(k => { sitesByCompany[String(k)] = mapRaw[k]; });

    const companySel = document.getElementById('company_id');
    const siteSel    = document.getElementById('site_id');

    function repopulateSites(selectedCompanyId, keepSiteId){
      const rows = sitesByCompany[String(selectedCompanyId)] || [];

      // Clear
      while (siteSel.options.length) siteSel.remove(0);

      // Default
      siteSel.appendChild(new Option('— Tanpa Site —',''));

      // Fill
      rows.forEach(r => {
        const opt = new Option(r.name, r.id);
        if (keepSiteId && String(keepSiteId) === String(r.id)) opt.selected = true;
        siteSel.appendChild(opt);
      });

      siteSel.disabled = false;
    }

    // Init pertama: populate jika sudah ada company (old()/model)
    if (currentCid !== '') {
      repopulateSites(currentCid, currentSid);
    } else {
      siteSel.disabled = true; // kunci sampai perusahaan dipilih
    }

    companySel.addEventListener('change', function(){
      repopulateSites(this.value, null); // reset pilihan site saat ganti company
    });
  })();
</script>
@endsection
