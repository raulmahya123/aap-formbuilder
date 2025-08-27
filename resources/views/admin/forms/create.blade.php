@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl">
  <h1 class="text-xl font-semibold mb-4">Buat Form</h1>
  <form action="{{ route('admin.forms.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label class="block">Department</label>
      <select name="department_id" class="border rounded w-full">
        @foreach($departments as $d)
          <option value="{{ $d->id }}">{{ $d->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label class="block">Judul</label>
      <input type="text" name="title" class="border rounded w-full" required>
    </div>
    <div class="mb-3">
      <label class="block">Tipe</label>
      <select name="type" id="type" class="border rounded w-full">
        <option value="builder">Builder</option>
        <option value="pdf">PDF</option>
      </select>
    </div>

    <div id="builderBox" class="mb-3">
      <label class="block">Schema (JSON)</label>
      <textarea name="schema" rows="8" class="border rounded w-full">
{
  "fields": [
    {"label":"Nama","name":"nama","type":"text","required":true},
    {"label":"Email","name":"email","type":"email"},
    {"label":"Tanggal","name":"tanggal","type":"date"},
    {"label":"Keterangan","name":"keterangan","type":"textarea"}
  ]
}
      </textarea>
      <p class="text-sm text-slate-500 mt-1">Tip: isian fleksibel, sesuaikan sesuai kebutuhan.</p>
    </div>

    <div id="pdfBox" class="mb-3 hidden">
      <label class="block">Unggah PDF</label>
      <input type="file" name="pdf" accept="application/pdf">
    </div>

    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" checked>
      <span>Aktif</span>
    </label>

    <div class="mt-4">
      <button class="px-4 py-2 bg-emerald-600 text-white rounded">Simpan</button>
    </div>
  </form>
</div>

<script>
document.getElementById('type').addEventListener('change', function(){
  const isPdf = this.value === 'pdf';
  document.getElementById('pdfBox').classList.toggle('hidden', !isPdf);
  document.getElementById('builderBox').classList.toggle('hidden', isPdf);
});
</script>
@endsection
