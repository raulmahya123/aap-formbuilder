@extends('layouts.app')

@section('title','Buat Document Template')

@section('content')
<div x-data="docTemplateBuilder()" x-init="init()" class="max-w-4xl mx-auto p-6 space-y-6">
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Buat Template Baru</h1>
    <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
  </div>

  {{-- FORM --}}
  <form method="POST"
        action="{{ route('admin.document_templates.store') }}"
        enctype="multipart/form-data"
        class="bg-white border rounded-xl p-6 space-y-6">
    @csrf

    {{-- Hidden JSONs (dirakit otomatis dari pilihan UI) --}}
    <input type="hidden" name="header_config" :value="JSON.stringify(header)">
    <input type="hidden" name="footer_config" :value="JSON.stringify(footer)">
    <input type="hidden" name="signature_config" :value="JSON.stringify(signature)">
    <input type="hidden" name="layout_config" :value="JSON.stringify(layout)">

    <div class="grid md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <label class="text-sm font-medium">Nama Template</label>
        <input type="text" name="name" required
               class="mt-1 w-full border rounded-lg px-3 py-2"
               placeholder="Contoh: SOP Default">
      </div>

      {{-- ========== HEADER ========== --}}
      <div class="border rounded-xl p-4">
        <div class="font-semibold text-[#1D1C1A] mb-3">Header</div>

        <label class="text-sm">Logo (upload)</label>
        <input type="file" name="logo_file" accept="image/*"
               @change="onLogoSelected($event)"
               class="mt-1 w-full border rounded-lg px-3 py-2">
        <div class="text-xs text-gray-500 mt-1">PNG/JPG/SVG, maks 2MB.</div>

        <div class="grid grid-cols-2 gap-3 mt-3">
          <div>
            <label class="text-sm">Posisi Logo</label>
            <select x-model="header.logo.position" class="mt-1 w-full border rounded-lg px-3 py-2">
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </div>
          <div>
            <label class="text-sm">Align Judul</label>
            <select x-model="header.title.align" class="mt-1 w-full border rounded-lg px-3 py-2">
              <option value="left">Left</option>
              <option value="center">Center</option>
              <option value="right">Right</option>
            </select>
          </div>
        </div>

        {{-- Preview Header --}}
        <div class="mt-4 border rounded-lg overflow-hidden">
          <div class="bg-[#1D1C1A] text-white px-3 py-2 text-sm">Preview Header</div>
          <div class="p-3">
            <div class="flex items-center justify-between">
              <div x-show="header.logo.position==='left' && header.logo.url" class="w-24 h-10 flex items-center justify-center">
                <img :src="header.logo.url" class="max-h-10">
              </div>
              <div class="flex-1 px-3 font-semibold text-[#1D1C1A]"
                   :class="{
                     'text-left'  : header.title.align==='left',
                     'text-center': header.title.align==='center',
                     'text-right' : header.title.align==='right'
                   }">Judul Dokumen
              </div>
              <div x-show="header.logo.position==='right' && header.logo.url" class="w-24 h-10 flex items-center justify-center">
                <img :src="header.logo.url" class="max-h-10">
              </div>
            </div>
            <div class="mt-2 text-xs text-gray-600">
              Metadata (Doc.No / Rev / Eff.Date / Page) akan dirender saat template dipakai.
            </div>
          </div>
        </div>
      </div>

      {{-- ========== FOOTER ========== --}}
      <div class="border rounded-xl p-4">
        <div class="font-semibold text-[#1D1C1A] mb-3">Footer</div>

        <label class="text-sm">Footer Text</label>
        <input x-model="footer.text" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="© Perusahaan 2025">
        <div class="mt-3">
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" x-model="footer.show_page_number" class="rounded">
            Tampilkan nomor halaman
          </label>
        </div>
      </div>

      {{-- ========== SIGNATURE (TTD) ========== --}}
      <div class="md:col-span-2 border rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div class="font-semibold text-[#1D1C1A]">Pengesahan (TTD)</div>
          <div class="flex gap-3">
            <button type="button" @click="addSigner()" class="text-[#7A2C2F]">+ Tambah</button>
            <button type="button" @click="resetSigners()" class="text-xs text-gray-600">Reset</button>
          </div>
        </div>

        <div class="grid md:grid-cols-4 gap-3 mt-3">
          <div>
            <label class="text-sm">Mode</label>
            <select x-model="signature.mode" class="mt-1 w-full border rounded-lg px-3 py-2">
              <option value="grid">Grid (mudah)</option>
              <option value="absolute">Absolute (presisi)</option>
            </select>
          </div>
          <div x-show="signature.mode==='grid'">
            <label class="text-sm">Kolom Grid</label>
            <input type="number" min="1" x-model.number="signature.columns" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="4">
          </div>
        </div>

        <template x-for="(sg,i) in signature.rows" :key="i">
          <div class="mt-3 p-3 border rounded-lg">
            <div class="grid md:grid-cols-4 gap-3">
              <div>
                <label class="text-sm">Role</label>
                <input x-model="sg.role" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Disiapkan / Diperiksa / ...">
              </div>
              <div>
                <label class="text-sm">Nama</label>
                <input x-model="sg.name" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Nama">
              </div>
              <div>
                <label class="text-sm">Jabatan</label>
                <input x-model="sg.position_title" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="Jabatan">
              </div>
              <div>
                <label class="text-sm">TTD (URL)</label>
                <input x-model="sg.image_path" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="/uploads/ttd.png">
                <div class="text-[11px] text-gray-500 mt-1">Untuk template, biasanya dikosongkan. Isi saat bikin Dokumen.</div>
              </div>
            </div>

            {{-- Opsi Grid --}}
            <div class="grid md:grid-cols-4 gap-3 mt-3" x-show="signature.mode==='grid'">
              <div>
                <label class="text-sm">colStart</label>
                <input type="number" min="1" x-model.number="sg.colStart" class="mt-1 w-full border rounded-lg px-3 py-2">
              </div>
              <div>
                <label class="text-sm">colSpan</label>
                <input type="number" min="1" x-model.number="sg.colSpan" class="mt-1 w-full border rounded-lg px-3 py-2">
              </div>
            </div>

            {{-- Opsi Absolute --}}
            <div class="grid md:grid-cols-4 gap-3 mt-3" x-show="signature.mode==='absolute'">
              <div>
                <label class="text-sm">top (px)</label>
                <input type="number" min="0" x-model.number="sg.top" class="mt-1 w-full border rounded-lg px-3 py-2">
              </div>
              <div>
                <label class="text-sm">left (px)</label>
                <input type="number" min="0" x-model.number="sg.left" class="mt-1 w-full border rounded-lg px-3 py-2">
              </div>
              <div>
                <label class="text-sm">width (px)</label>
                <input type="number" min="0" x-model.number="sg.width" class="mt-1 w-full border rounded-lg px-3 py-2">
              </div>
              <div>
                <label class="text-sm">height (px)</label>
                <input type="number" min="0" x-model.number="sg.height" class="mt-1 w-full border rounded-lg px-3 py-2">
              </div>
            </div>

            <div class="mt-3">
              <button type="button" @click="signature.rows.splice(i,1)" class="text-rose-600 text-sm">hapus baris</button>
            </div>
          </div>
        </template>
      </div>

      {{-- ========== LAYOUT ========== --}}
      <div class="md:col-span-2 border rounded-xl p-4">
        <div class="font-semibold text-[#1D1C1A] mb-3">Layout Dokumen</div>
        <div class="grid md:grid-cols-5 gap-3">
          <div>
            <label class="text-sm">Margin Top (mm)</label>
            <input type="number" min="0" x-model.number="layout.margins.top" class="mt-1 w-full border rounded-lg px-3 py-2">
          </div>
          <div>
            <label class="text-sm">Right</label>
            <input type="number" min="0" x-model.number="layout.margins.right" class="mt-1 w-full border rounded-lg px-3 py-2">
          </div>
          <div>
            <label class="text-sm">Bottom</label>
            <input type="number" min="0" x-model.number="layout.margins.bottom" class="mt-1 w-full border rounded-lg px-3 py-2">
          </div>
          <div>
            <label class="text-sm">Left</label>
            <input type="number" min="0" x-model.number="layout.margins.left" class="mt-1 w-full border rounded-lg px-3 py-2">
          </div>
          <div>
            <label class="text-sm">Font Size (pt)</label>
            <input type="number" min="8" x-model.number="layout.font.size" class="mt-1 w-full border rounded-lg px-3 py-2">
          </div>
        </div>
      </div>
    </div>

    <div class="pt-2 flex items-center justify-end gap-3">
      <a href="{{ route('admin.document_templates.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">Batal</a>
      <button class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Simpan</button>
    </div>
  </form>
</div>

@push('scripts')
<script>
function docTemplateBuilder(){
  return {
    header:   { logo: { url:'', position:'left' }, title: { align:'center' } },
    footer:   { text:'', show_page_number:true },
    signature:{ mode:'grid', columns:4, rows:[
      { role:'Disiapkan', name:'', position_title:'', image_path:'', colStart:1, colSpan:1, top:0, left:0, width:120, height:40 },
      { role:'Diperiksa', name:'', position_title:'', image_path:'', colStart:2, colSpan:1, top:0, left:0, width:120, height:40 },
      { role:'Disetujui', name:'', position_title:'', image_path:'', colStart:3, colSpan:1, top:0, left:0, width:120, height:40 },
      { role:'Ditetapkan', name:'', position_title:'', image_path:'', colStart:4, colSpan:1, top:0, left:0, width:120, height:40 },
    ]},
    layout:   { margins:{ top:30, right:25, bottom:25, left:25 }, font:{ size:11 } },

    init(){},
    onLogoSelected(e){
      const f = e.target.files?.[0];
      if(!f) return;
      // Preview di client; path final akan diisi oleh server setelah upload
      this.header.logo.url = URL.createObjectURL(f);
    },
    addSigner(){
      this.signature.rows.push({ role:'Signer', name:'', position_title:'', image_path:'', colStart:1, colSpan:1, top:0, left:0, width:120, height:40 });
    },
    resetSigners(){
      this.signature.rows = [
        { role:'Disiapkan', name:'', position_title:'', image_path:'', colStart:1, colSpan:1, top:0, left:0, width:120, height:40 },
        { role:'Diperiksa', name:'', position_title:'', image_path:'', colStart:2, colSpan:1, top:0, left:0, width:120, height:40 },
        { role:'Disetujui', name:'', position_title:'', image_path:'', colStart:3, colSpan:1, top:0, left:0, width:120, height:40 },
        { role:'Ditetapkan', name:'', position_title:'', image_path:'', colStart:4, colSpan:1, top:0, left:0, width:120, height:40 },
      ];
    }
  }
}
</script>
@endpush
@endsection
