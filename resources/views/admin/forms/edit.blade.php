@extends('layouts.app')

@section('title','Edit Form: '.$form->title)

@section('content')
<div class="max-w-4xl mx-auto p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Edit Form</h1>

    <div class="flex items-center gap-2">
      @if($form->type === 'builder' && Route::has('admin.forms.builder'))
        <a href="{{ route('admin.forms.builder', $form) }}"
           class="px-3 py-1.5 rounded-lg border bg-white hover:bg-slate-50 text-sm">
          Open Builder
        </a>
      @endif
      <a href="{{ route('admin.forms.index') }}"
         class="px-3 py-1.5 rounded-lg border text-sm">Kembali</a>
    </div>
  </div>

  {{-- Flash --}}
  @if (session('ok'))
    <div class="mb-4 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 px-4 py-3">
      {{ session('ok') }}
    </div>
  @endif

  {{-- Errors --}}
  @if ($errors->any())
    <div class="mb-4 rounded bg-rose-50 text-rose-700 border border-rose-200 px-4 py-3">
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
        x-data="formEdit()">
    @csrf
    @method('PUT')

    <div class="rounded-2xl border bg-white p-5 space-y-5">

      {{-- Department --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Departemen</label>
        <select name="department_id" class="mt-1 w-full rounded-lg border px-3 py-2" required>
          @foreach($departments as $d)
            <option value="{{ $d->id }}" @selected(old('department_id',$form->department_id)==$d->id)>
              {{ $d->name }}
            </option>
          @endforeach
        </select>
      </div>

      {{-- Title --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Judul</label>
        <input type="text" name="title" value="{{ old('title',$form->title) }}"
               class="mt-1 w-full rounded-lg border px-3 py-2" required maxlength="190">
      </div>

      {{-- Type --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Tipe</label>
        <select name="type" x-model="type" class="mt-1 w-full rounded-lg border px-3 py-2" required>
          <option value="builder">Builder</option>
          {{-- nilai tetap "pdf" untuk kompatibilitas controller --}}
          <option value="pdf">File (PDF/Word/Excel)</option>
        </select>
      </div>

      {{-- Builder schema --}}
      <div x-show="type==='builder'">
        <label class="block text-sm font-medium text-slate-700">Schema (JSON)</label>
        <textarea name="schema" rows="10"
          class="mt-1 w-full rounded-lg border px-3 py-2 font-mono text-sm"
          placeholder='{"fields":[...]}'>{!!
            json_encode(
              old('schema',$form->schema ?? ['fields'=>[]]),
              JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE
            )
          !!}</textarea>
        <p class="text-xs text-slate-500 mt-1">
          Isi dengan objek JSON berisi key <code>fields</code> (array).
        </p>
      </div>

      {{-- File (PDF/Word/Excel) --}}
      <div x-show="type==='pdf'">
        <label class="block text-sm font-medium text-slate-700">Unggah File (opsional untuk ganti)</label>
        <input type="file" name="pdf"
               accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
               class="mt-1 block w-full text-sm file:mr-4 file:py-2 file:px-4
                      file:rounded-lg file:border-0 file:bg-emerald-50 file:text-emerald-700
                      hover:file:bg-emerald-100">
        <p class="text-xs text-slate-500 mt-1">
          Format: PDF, DOC/DOCX, XLS/XLSX — maks 30 MB. File akan dikompresi otomatis saat disimpan.
        </p>

        @if($form->pdf_path)
          <p class="text-xs text-slate-600 mt-1">
            File sekarang:
            <a class="underline" target="_blank"
               href="{{ Storage::disk('public')->url($form->pdf_path) }}">
              {{ basename($form->pdf_path) }}
            </a>
            <span class="text-slate-400">({{ $form->pdf_path }})</span>
          </p>
        @endif
      </div>

      {{-- Active --}}
      <div class="flex items-center gap-2">
        <input type="checkbox" id="active" name="is_active" value="1"
               @checked(old('is_active', $form->is_active))>
        <label for="active" class="text-sm">Aktif</label>
      </div>

    </div>

    <div class="mt-6 flex items-center gap-3">
      <button class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">
        Simpan Perubahan
      </button>
    </div>
  </form>

  {{-- Delete --}}
  <form method="POST"
        action="{{ route('admin.forms.destroy', $form) }}"
        class="mt-4"
        onsubmit="return confirm('Hapus form ini? Tindakan tidak bisa dibatalkan.')">
    @csrf @method('DELETE')
    <button class="px-4 py-2 rounded-xl border text-rose-700 border-rose-200 hover:bg-rose-50">
      Hapus
    </button>
  </form>
</div>

{{-- Inisialisasi Alpine --}}
<div id="form-data" data-type='@json(old("type", $form->type))'></div>
<script>
  function formEdit(){
    const el = document.getElementById('form-data');
    return {
      type: JSON.parse(el.dataset.type) // "builder" / "pdf"
    }
  }
</script>
@endsection
