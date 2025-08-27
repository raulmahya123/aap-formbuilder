{{-- resources/views/admin/forms/builder.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6"
     x-data="formBuilder({
        initial: @js($schema['fields'] ?? []),
     })">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Builder — {{ $form->title }}</h1>
    <a class="text-sm underline" href="{{ route('admin.forms.edit', $form) }}">← Kembali ke Edit</a>
  </div>

  <div class="grid md:grid-cols-3 gap-6">
    {{-- PALETTE (kiri) --}}
    <div class="bg-white rounded-xl border p-4">
      <h2 class="font-medium mb-3">Palette</h2>
      <div class="grid grid-cols-2 gap-2 text-sm">
        <template x-for="t in palette" :key="t.type">
          <button type="button" @click="addField(t.type)"
                  class="px-3 py-2 rounded border hover:bg-slate-50 text-left">
            <div class="font-semibold" x-text="t.label"></div>
            <div class="text-xs text-slate-500" x-text="t.desc"></div>
          </button>
        </template>
      </div>
    </div>

    {{-- CANVAS (tengah) --}}
    <div class="bg-white rounded-xl border p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-medium">Form Fields</h2>
        <div class="text-xs text-slate-500">Drag untuk urutan</div>
      </div>

      <div id="canvas" class="space-y-3">
        <template x-if="fields.length===0">
          <div class="text-slate-500 text-sm">Belum ada field. Tambahkan dari palette.</div>
        </template>

        <template x-for="(f,idx) in fields" :key="f._key">
          <div class="p-3 border rounded-lg">
            <div class="flex items-center justify-between">
              <div class="font-medium">
                <span class="cursor-move">☰</span>
                <span x-text="f.label || f.name"></span>
                <span class="text-xs text-slate-500">(<span x-text="f.type"></span>)</span>
                <template x-if="f.required">
                  <span class="ml-1 text-rose-600 text-xs font-semibold">*required</span>
                </template>
              </div>
              <div class="flex items-center gap-2">
                <button class="text-xs underline" @click="editIndex=idx">Edit</button>
                <button class="text-xs text-red-600 underline" @click="removeField(idx)">Hapus</button>
              </div>
            </div>
            {{-- ringkasan properti --}}
            <div class="mt-2 text-xs text-slate-600" x-show="f.options?.length && ['select','radio','checkbox'].includes(f.type)">
              Opsi: <span x-text="f.options.map(o => Array.isArray(o)? o[1] : o).join(', ')"></span>
            </div>
          </div>
        </template>
      </div>
    </div>

    {{-- PROPERTIES (kanan) --}}
    <div class="bg-white rounded-xl border p-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-medium">Properties</h2>
        <button class="text-xs underline" @click="editIndex=null" x-show="editIndex!==null">Selesai</button>
      </div>

      <template x-if="editIndex===null">
        <div class="text-slate-500 text-sm">Pilih field untuk mengedit.</div>
      </template>

      <template x-if="editIndex!==null">
        <div class="space-y-3" x-init="$nextTick(() => focusNameInput())">
          <div>
            <label class="text-sm block">Label</label>
            <input x-model="fields[editIndex].label" id="prop-label" class="border rounded w-full px-2 py-1">
          </div>
          <div>
            <label class="text-sm block">Name (snake_case, unik)</label>
            <input x-model="fields[editIndex].name" id="prop-name" class="border rounded w-full px-2 py-1">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm block">Type</label>
              <select x-model="fields[editIndex].type" class="border rounded w-full px-2 py-1">
                <template x-for="t in palette" :key="t.type">
                  <option :value="t.type" x-text="t.type"></option>
                </template>
              </select>
            </div>
            <div class="flex items-center gap-2 mt-6">
              <input type="checkbox" x-model="fields[editIndex].required">
              <span class="text-sm">Required</span>
            </div>
          </div>

          {{-- RULES --}}
          <div>
            <label class="text-sm block">Rules (opsional, format Laravel: min:3|max:80|regex:...)</label>
            <input x-model="fields[editIndex].rules" placeholder="mis. string|min:3|max:80"
                   class="border rounded w-full px-2 py-1">
          </div>

          {{-- MIMES & MAX khusus file --}}
          <template x-if="fields[editIndex].type==='file'">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-sm block">Mimes</label>
                <input x-model="fields[editIndex].mimes" placeholder="pdf,jpg,png" class="border rounded w-full px-2 py-1">
              </div>
              <div>
                <label class="text-sm block">Max (KB)</label>
                <input type="number" x-model.number="fields[editIndex].max" placeholder="2048" class="border rounded w-full px-2 py-1">
              </div>
            </div>
          </template>

          {{-- OPTIONS untuk select/radio/checkbox --}}
          <template x-if="['select','radio','checkbox'].includes(fields[editIndex].type)">
            <div>
              <div class="flex items-center justify-between">
                <label class="text-sm">Options</label>
                <button class="text-xs underline" @click="addOption()">Tambah Opsi</button>
              </div>
              <div class="mt-2 space-y-2">
                <template x-for="(opt,i) in (fields[editIndex].options ??= [])" :key="i">
                  <div class="flex items-center gap-2">
                    <input class="border rounded px-2 py-1 w-28" x-model="fields[editIndex].options[i][0]" placeholder="value">
                    <input class="border rounded px-2 py-1 flex-1" x-model="fields[editIndex].options[i][1]" placeholder="label">
                    <button class="text-xs text-red-600 underline" @click="fields[editIndex].options.splice(i,1)">hapus</button>
                  </div>
                </template>
              </div>
            </div>
          </template>

          <div class="pt-2">
            <button class="px-3 py-2 bg-slate-800 text-white rounded" @click="editIndex=null">Simpan Properti</button>
          </div>
        </div>
      </template>
    </div>
  </div>

  {{-- PREVIEW JSON + SAVE --}}
  <div class="mt-6 bg-white rounded-xl border p-4">
    <div class="flex items-center justify-between mb-3">
      <h2 class="font-medium">Schema JSON</h2>
      <div class="flex items-center gap-2">
        <button class="px-3 py-2 rounded border" @click="pretty()">Format JSON</button>
        <button class="px-3 py-2 bg-emerald-600 text-white rounded" @click="$refs.form.submit()">Save Schema</button>
      </div>
    </div>
    <form method="post" action="{{ route('admin.forms.builder.save', $form) }}" x-ref="form">
      @csrf @method('PUT')
      <textarea name="schema" x-model="json" rows="10" class="w-full font-mono text-sm border rounded p-2"></textarea>
    </form>
  </div>
</div>

{{-- SortableJS CDN untuk drag & drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function formBuilder({ initial }) {
  return {
    palette: [
      { type:'text',     label:'Text',     desc:'Input teks 1 baris' },
      { type:'email',    label:'Email',    desc:'Validasi email' },
      { type:'number',   label:'Number',   desc:'Angka saja' },
      { type:'date',     label:'Date',     desc:'Tanggal' },
      { type:'textarea', label:'Textarea', desc:'Teks multi-baris' },
      { type:'select',   label:'Select',   desc:'Dropdown pilihan' },
      { type:'radio',    label:'Radio',    desc:'Pilih satu' },
      { type:'checkbox', label:'Checkbox', desc:'Bisa pilih banyak' },
      { type:'file',     label:'File',     desc:'Upload berkas' },
    ],

    fields: (initial || []).map((f, i) => ({ _key: cryptoRandom(), required:false, ...f })),
    editIndex: null,

    get json() { return JSON.stringify({ fields: this.fields.map(stripRuntime) }, null, 2); },
    set json(v) {
      try {
        const obj = JSON.parse(v);
        const arr = (obj?.fields ?? []).map((f) => ({ _key: cryptoRandom(), required:false, ...f }));
        this.fields = arr;
      } catch(e) { /* ignore */ }
    },

    addField(type){
      const base = { type, label: type.toUpperCase(), name: uniqueName(this.fields, type), required:false };
      if (['select','radio','checkbox'].includes(type)) base.options = [['opt1','Opsi 1'],['opt2','Opsi 2']];
      if (type==='file') { base.mimes='pdf'; base.max=2048; }
      this.fields.push({ _key: cryptoRandom(), ...base });
      this.editIndex = this.fields.length - 1;
      queueMicrotask(() => focusNameInput());
    },
    removeField(i){ if (this.editIndex===i) this.editIndex=null; this.fields.splice(i,1); },

    addOption(){
      if (this.editIndex===null) return;
      const f = this.fields[this.editIndex];
      if (!Array.isArray(f.options)) f.options = [];
      f.options.push(['value','Label']);
    },
    pretty(){ this.json = this.json; },

    init(){
      // active drag
      new Sortable(document.getElementById('canvas'), {
        handle: '.cursor-move',
        animation: 150,
        draggable: '.p-3.border.rounded-lg',
        onEnd: (evt) => {
          const oldI = evt.oldIndex, newI = evt.newIndex;
          if (oldI === newI) return;
          const moved = this.fields.splice(oldI,1)[0];
          this.fields.splice(newI,0,moved);
          if (this.editIndex === oldI) this.editIndex = newI;
          else if (this.editIndex !== null) {
            // adjust edit index if necessary
            if (oldI < this.editIndex && newI >= this.editIndex) this.editIndex--;
            if (oldI > this.editIndex && newI <= this.editIndex) this.editIndex++;
          }
        }
      });
    }
  }
}

function stripRuntime(f){
  const { _key, ...rest } = f;
  return rest;
}
function cryptoRandom(){
  if (window.crypto?.getRandomValues) {
    const arr = new Uint32Array(2); crypto.getRandomValues(arr);
    return 'k'+arr[0].toString(36)+arr[1].toString(36);
  }
  return 'k'+Math.random().toString(36).slice(2);
}
function uniqueName(fields, type){
  const base = type.replace(/[^a-z0-9]+/gi,'_').toLowerCase();
  let i = 1, candidate = base;
  const names = new Set(fields.map(f=>f.name));
  while (names.has(candidate)) { i++; candidate = base+'_'+i; }
  return candidate;
}
function focusNameInput(){
  const el = document.getElementById('prop-name');
  el && el.focus();
}
</script>
@endsection
