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
  @endphp

  <form action="{{ route('admin.forms.store') }}" method="post" enctype="multipart/form-data">
    @csrf

    <div class="mb-3">
      <label class="block font-medium mb-1">Department</label>
      <select name="department_id" class="border rounded w-full p-2" required>
        @foreach($departments as $d)
          <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3">
      <label class="block font-medium mb-1">Judul</label>
      <input type="text" name="title" class="border rounded w-full p-2" value="{{ old('title') }}" required maxlength="190">
    </div>

    {{-- === Jenis Dokumen (SOP/IK/FORM) === --}}
    <div class="mb-3">
      <label class="block font-medium mb-1">Jenis Dokumen</label>
      <select name="doc_type" id="doc_type" class="border rounded w-full p-2" required>
        {{-- Tidak pakai placeholder kosong agar tidak pernah kirim "" --}}
        <option value="SOP"  @selected($selectedDocType === 'SOP')>SOP</option>
        <option value="IK"   @selected($selectedDocType === 'IK')>IK</option>
        <option value="FORM" @selected($selectedDocType === 'FORM')>FORM</option>
      </select>
      <p class="text-sm text-slate-500 mt-1">Kategori dokumen untuk form ini.</p>
    </div>

    <div class="mb-3">
      <label class="block font-medium mb-1">Tipe</label>
      <select name="type" id="type" class="border rounded w-full p-2" required>
        <option value="builder" @selected($selectedType === 'builder')>Builder</option>
        <option value="pdf"     @selected($selectedType === 'pdf')>File (PDF/Word/Excel)</option>
      </select>
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
      <p class="text-sm text-slate-500 mt-1">Format: PDF, DOC/DOCX, XLS/XLSX — maks 30&nbsp;MB. File akan dikompresi otomatis saat disimpan.</p>
    </div>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
      <span>Aktif</span>
    </label>

    <div class="mt-4">
      <button class="px-4 py-2 bg-emerald-600 text-white rounded">Simpan</button>
    </div>
  </form>
</div>

<script>
(function () {
  const typeSel    = document.getElementById('type');
  const pdfBox     = document.getElementById('pdfBox');
  const builderBox = document.getElementById('builderBox');
  const pdfInput   = document.getElementById('pdfInput');

  function toggleBoxes() {
    const isFile = typeSel.value === 'pdf';
    pdfBox.classList.toggle('hidden', !isFile);
    builderBox.classList.toggle('hidden', isFile);

    // sinkron dengan validasi required_if:type,pdf
    if (pdfInput) {
      if (isFile) {
        pdfInput.setAttribute('required', 'required');
      } else {
        pdfInput.removeAttribute('required');
        pdfInput.value = ''; // bersihkan jika berpindah dari pdf ke builder
      }
    }
  }

  typeSel.addEventListener('change', toggleBoxes);
  // Init on load (respect old value)
  toggleBoxes();
})();
</script>
@endsection
