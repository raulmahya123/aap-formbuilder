{{-- resources/views/admin/forms/builder.blade.php --}}
@extends('layouts.app')
@section('content')
<x-ui.page class="w-full min-w-0 "
     x-data="formBuilder({
        initial: @js($schema['fields'] ?? []),
        saveUrl: @js(route('admin.forms.builder.save', $form)),
        csrf: @js(csrf_token()),
     })">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Builder — {{ $form->title }}</x-ui.page-title>
<x-ui.page-description>Kelola form, struktur isian, dan tipe dokumen.</x-ui.page-description></div>
  <x-ui.page-actions><a class="text-sm underline" href="{{ route('admin.forms.edit', $form) }}">← Kembali ke Edit</a></x-ui.page-actions>
</x-ui.page-header>
  <div class="grid md:grid-cols-3 gap-5">
    {{-- PALETTE (kiri) --}}
    <x-ui.panel class="bg-white rounded-lg border">
      <x-ui.section-title class="font-medium mb-3">Palette</x-ui.section-title>
      <div class="grid grid-cols-2 gap-2 text-sm">
        <template x-for="t in palette" :key="t.type">
          <x-ui.button variant="secondary" type="button" @click.prevent="addField(t.type)"
                  class="px-3 py-2 rounded-lg border text-left">
            <div class="font-semibold" x-text="t.label"></div>
            <div class="text-xs text-slate-500" x-text="t.desc"></div>
          </x-ui.button>
        </template>
      </div>
    </x-ui.panel>
    {{-- CANVAS (tengah) --}}
    <x-ui.panel class="bg-white rounded-lg border">
      <div class="flex items-center justify-between mb-3">
        <x-ui.section-title class="font-medium">Form Fields</x-ui.section-title>
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
                <x-ui.icon-button action="edit" label="Edit field" type="button" @click.prevent="editIndex=idx" />
                <x-ui.icon-button action="delete" label="Hapus field" type="button" @click.prevent="removeField(idx)" />
              </div>
            </div>
            {{-- ringkasan properti --}}
            <div class="mt-2 text-xs text-slate-600" x-show="f.options?.length && ['select','radio','checkbox'].includes(f.type)">
              Opsi: <span x-text="f.options.map(o => Array.isArray(o)? o[1] : o).join(', ')"></span>
            </div>
          </div>
        </template>
      </div>
    </x-ui.panel>
    {{-- PROPERTIES (kanan) --}}
    <x-ui.panel class="bg-white rounded-lg border">
      <div class="flex items-center justify-between mb-3">
        <x-ui.section-title class="font-medium">Properties</x-ui.section-title>
        <x-ui.button variant="secondary" type="button" class="text-xs underline" @click.prevent="editIndex=null" x-show="editIndex!==null">Selesai</x-ui.button>
      </div>
      <template x-if="editIndex===null">
        <div class="text-slate-500 text-sm">Pilih field untuk mengedit.</div>
      </template>
      <template x-if="editIndex!==null">
        <div class="space-y-3" x-init="$nextTick(() => focusNameInput())">
          <div>
            <label class="text-sm block">Label</label>
            <input x-model="fields[editIndex].label" @input="queueSave()" id="prop-label" class="border rounded-lg w-full px-2 py-1">
          </div>
          <div>
            <label class="text-sm block">Name (snake_case, unik)</label>
            <input x-model="fields[editIndex].name" @input="queueSave()" id="prop-name" class="border rounded-lg w-full px-2 py-1">
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-sm block">Type</label>
              <select x-model="fields[editIndex].type" @change="saveNow()" class="border rounded-lg w-full px-2 py-1">
                <template x-for="t in palette" :key="t.type">
                  <option :value="t.type" x-text="t.type"></option>
                </template>
              </select>
            </div>
            <div class="flex items-center gap-2 mt-6">
              <input type="checkbox" x-model="fields[editIndex].required" @change="saveNow()">
              <span class="text-sm">Required</span>
            </div>
          </div>
          {{-- RULES --}}
          <div>
            <label class="text-sm block">Rules (opsional, format Laravel: min:3|max:80|regex:...)</label>
            <input x-model="fields[editIndex].rules" @input="queueSave()" placeholder="mis. string|min:3|max:80"
                   class="border rounded-lg w-full px-2 py-1">
          </div>
          {{-- MIMES & MAX khusus file --}}
          <template x-if="fields[editIndex].type==='file'">
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-sm block">Mimes</label>
                <input x-model="fields[editIndex].mimes" @input="queueSave()" placeholder="pdf,jpg,png" class="border rounded-lg w-full px-2 py-1">
              </div>
              <div>
                <label class="text-sm block">Max (KB)</label>
                <input type="number" x-model.number="fields[editIndex].max" @input="queueSave()" placeholder="2048" class="border rounded-lg w-full px-2 py-1">
              </div>
            </div>
          </template>
          {{-- OPTIONS untuk select/radio/checkbox --}}
          <template x-if="['select','radio','checkbox'].includes(fields[editIndex].type)">
            <div>
              <div class="flex items-center justify-between">
                <label class="text-sm">Options</label>
                <x-ui.button variant="secondary" type="button" class="text-xs underline" @click.prevent="addOption()">Tambah Opsi</x-ui.button>
              </div>
              <div class="mt-2 space-y-2">
                <template x-for="(opt,i) in (fields[editIndex].options ??= [])" :key="i">
                  <div class="flex items-center gap-2">
                    <input class="border rounded-lg px-2 py-1 w-28" x-model="fields[editIndex].options[i][0]" @input="queueSave()" placeholder="value">
                    <input class="border rounded-lg px-2 py-1 flex-1" x-model="fields[editIndex].options[i][1]" @input="queueSave()" placeholder="label">
                    <x-ui.button variant="danger" type="button" class="text-xs underline" @click.prevent="fields[editIndex].options.splice(i,1); saveNow()">hapus</x-ui.button>
                  </div>
                </template>
              </div>
            </div>
          </template>
          <div class="pt-2">
            <x-ui.button variant="secondary" type="button" class="px-3 py-2 rounded-lg" @click.prevent="editIndex=null">Selesai</x-ui.button>
          </div>
        </div>
      </template>
    </x-ui.panel>
  </div>
  {{-- PREVIEW JSON + SAVE --}}
  <x-ui.panel class="mt-6 bg-white rounded-lg border">
    <div class="flex items-center justify-between mb-3">
      <x-ui.section-title class="font-medium">Schema JSON</x-ui.section-title>
      <div class="flex items-center gap-2">
        <x-ui.button variant="secondary" type="button" class="px-3 py-2 rounded-lg border" @click.prevent="pretty()">Format JSON</x-ui.button>
        <x-ui.button variant="primary" type="button" class="px-3 py-2 rounded-lg" @click.prevent="saveNow()">Save Schema</x-ui.button>
      </div>
    </div>
    <form method="post" action="{{ route('admin.forms.builder.save', $form) }}" x-ref="form">
      @csrf @method('PUT')
      <textarea name="schema" x-model="json" rows="10" class="w-full font-mono text-sm border rounded-lg p-2"></textarea>
    </form>
    <div class="text-xs mt-2" x-show="savedFlash" x-transition>✓ Tersimpan</div>
  </x-ui.panel>
</x-ui.page>
{{-- SortableJS --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
function formBuilder({ initial, saveUrl, csrf }) {
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
    fields: (initial || []).map((f) => ({ _key: cryptoRandom(), required:false, ...f })),
    editIndex: null,
    savedFlash: false,
    saveTimer: null,
    get json() { return JSON.stringify({ fields: this.fields.map(stripRuntime) }, null, 2); },
    set json(v) {
      try {
        const obj = JSON.parse(v);
        const arr = (obj?.fields ?? []).map((f) => ({ _key: cryptoRandom(), required:false, ...f }));
        this.fields = arr;
        this.queueSave();
      } catch(e) { /* ignore */ }
    },
    addField(type){
      const base = { type, label: type.toUpperCase(), name: uniqueName(this.fields, type), required:false };
      if (['select','radio','checkbox'].includes(type)) base.options = [['opt1','Opsi 1'],['opt2','Opsi 2']];
      if (type==='file') { base.mimes='pdf'; base.max=2048; }
      this.fields.push({ _key: cryptoRandom(), ...base });
      this.editIndex = this.fields.length - 1;
      queueMicrotask(() => focusNameInput());
      this.saveNow(); // real-time
    },
    async removeField(i){
      this.fields.splice(i,1);
      if (this.editIndex === i) this.editIndex = null;
      else if (this.editIndex !== null && this.editIndex > i) this.editIndex--;
      await this.saveNow(); // real-time
    },
    addOption(){
      if (this.editIndex===null) return;
      const f = this.fields[this.editIndex];
      if (!Array.isArray(f.options)) f.options = [];
      f.options.push(['value','Label']);
      this.saveNow(); // real-time
    },
    pretty(){ this.json = this.json; },
    queueSave(){
      clearTimeout(this.saveTimer);
      this.saveTimer = setTimeout(() => this.saveNow(), 600); // debounce utk ketikan biasa
    },
    async saveNow(){
      try {
        const res = await fetch(saveUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-HTTP-Method-Override': 'PUT',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ schema: this.json })
        });
        if (!res.ok || res.redirected) {
          const message = res.status === 401 || res.status === 419 ? 'Sesi telah berakhir. Masuk kembali sebelum menyimpan.'
            : res.status === 403 ? 'Anda tidak memiliki izin untuk menyimpan form ini.'
            : res.status === 422 ? 'Form belum dapat disimpan. Periksa kembali susunan dan isian form.'
            : 'Form gagal disimpan. Silakan coba lagi.';
          window.dispatchEvent(new CustomEvent('ui:toast', { detail: { type: 'error', message } }));
          return;
        }
        this.savedFlash = true;
        setTimeout(()=> this.savedFlash = false, 1200);
      } catch (e) {
        window.dispatchEvent(new CustomEvent('ui:toast', { detail: { type: 'error', message: 'Koneksi terputus. Perubahan belum tersimpan. Periksa koneksi, lalu coba simpan kembali.' } }));
      }
    },
    init(){
      new Sortable(document.getElementById('canvas'), {
        handle: '.cursor-move',
        animation: 150,
        draggable: '.p-3.border.rounded-lg',
        filter: 'button,a,input,[data-nodrag]',
        onEnd: (evt) => {
          const oldI = evt.oldIndex, newI = evt.newIndex;
          if (oldI === newI) return;
          const moved = this.fields.splice(oldI,1)[0];
          this.fields.splice(newI,0,moved);
          if (this.editIndex === oldI) this.editIndex = newI;
          else if (this.editIndex !== null) {
            if (oldI < this.editIndex && newI >= this.editIndex) this.editIndex--;
            if (oldI > this.editIndex && newI <= this.editIndex) this.editIndex++;
          }
          this.saveNow(); // real-time
        }
      });
    }
  }
}
function stripRuntime(f){ const { _key, ...rest } = f; return rest; }
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
function focusNameInput(){ const el = document.getElementById('prop-name'); el && el.focus(); }
</script>
@endsection