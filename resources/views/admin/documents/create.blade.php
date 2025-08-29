@extends('layouts.app')

@section('content')
<div x-data="docBuilder()" x-init="init()" class="max-w-7xl mx-auto p-6 space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Buat Dokumen</h1>
    <button form="docForm" class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white">Simpan</button>
  </div>

  <form id="docForm" method="POST" action="{{ route('admin.documents.store') }}">
    @csrf
    <input type="hidden" name="header_config" :value="JSON.stringify(header)">
    <input type="hidden" name="footer_config" :value="JSON.stringify(footer)">
    <input type="hidden" name="signature_config" :value="JSON.stringify(signatures)">
    <input type="hidden" name="sections" :value="JSON.stringify(sections)">

    <div class="grid md:grid-cols-3 gap-4">
      <div class="bg-white border rounded-xl p-4 md:col-span-2">
        <label class="text-sm font-medium">Judul</label>
        <input name="title" class="mt-1 w-full border rounded-lg px-3 py-2" required>

        <div class="grid grid-cols-2 gap-3 mt-4">
          <div>
            <label class="text-sm font-medium">Dept Code</label>
            <input name="dept_code" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="PLT/SHE/ENG">
          </div>
          <div>
            <label class="text-sm font-medium">Doc Type</label>
            <input name="doc_type" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="SOP/IK/ST...">
          </div>
          <div>
            <label class="text-sm font-medium">Effective Date</label>
            <input type="date" name="effective_date" class="mt-1 w-full border rounded-lg px-3 py-2">
          </div>
          <div>
            <label class="text-sm font-medium">Class</label>
            <select name="class" class="mt-1 w-full border rounded-lg px-3 py-2">
              <option value="">-</option><option>I</option><option>II</option><option>III</option><option>IV</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Controlled Status</label>
            <select name="controlled_status" class="mt-1 w-full border rounded-lg px-3 py-2">
              <option value="controlled">Controlled</option>
              <option value="uncontrolled">Uncontrolled</option>
              <option value="obsolete">Obsolete</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium">Department Owner</label>
            <select name="department_id" class="mt-1 w-full border rounded-lg px-3 py-2">
              <option value="">-</option>
              @foreach($departments as $dp)
                <option value="{{ $dp->id }}">{{ $dp->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Sections --}}
        <div class="mt-6">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-[#1D1C1A]">Sections</h2>
            <button type="button" @click="addSection()" class="text-[#7A2C2F]">+ Tambah</button>
          </div>
          <template x-for="(s,i) in sections" :key="i">
            <div class="mt-3 p-3 border rounded-lg">
              <div class="flex gap-3">
                <input x-model="s.label" class="border rounded px-2 py-1 w-48">
                <button type="button" @click="sections.splice(i,1)" class="text-red-600">hapus</button>
              </div>
              <textarea x-model="s.html" class="mt-2 w-full border rounded-lg px-3 py-2" rows="4" placeholder="HTML/Konten"></textarea>
            </div>
          </template>
        </div>
      </div>

      {{-- Panel Custom Layout --}}
      <div class="bg-white border rounded-xl p-4 space-y-4">
        <h2 class="font-semibold text-[#1D1C1A]">Header / Footer / TTD</h2>

        <div>
          <label class="text-sm font-medium">Logo URL</label>
          <input x-model="header.logo.url" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="/uploads/logo.png">
          <label class="text-sm mt-2 block">Posisi Logo</label>
          <select x-model="header.logo.position" class="w-full border rounded-lg px-3 py-2">
            <option>left</option><option>center</option><option>right</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium">Align Judul</label>
          <select x-model="header.title.align" class="mt-1 w-full border rounded-lg px-3 py-2">
            <option>left</option><option>center</option><option>right</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-medium">Footer Text</label>
          <input x-model="footer.text" class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="© Perusahaan ...">
        </div>

        <div class="border-t pt-3">
          <div class="flex items-center justify-between">
            <span class="font-medium">Tabel TTD</span>
            <button type="button" @click="addSigner()" class="text-[#7A2C2F]">+ Tambah</button>
          </div>
          <template x-for="(sg,i) in signatures.rows" :key="i">
            <div class="mt-2 p-2 border rounded">
              <div class="grid grid-cols-1 gap-2">
                <input x-model="sg.role" class="border rounded px-2 py-1" placeholder="Disiapkan/Diperiksa/...">
                <input x-model="sg.name" class="border rounded px-2 py-1" placeholder="Nama">
                <input x-model="sg.position_title" class="border rounded px-2 py-1" placeholder="Jabatan">
                <input x-model="sg.image_path" class="border rounded px-2 py-1" placeholder="/uploads/ttd.png">

                {{-- Mode Grid: colStart/colSpan --}}
                <div class="grid grid-cols-2 gap-2">
                  <input x-model.number="sg.colStart" class="border rounded px-2 py-1" placeholder="colStart (1)">
                  <input x-model.number="sg.colSpan" class="border rounded px-2 py-1" placeholder="colSpan (1)">
                </div>

                {{-- Mode Absolute (opsional) --}}
                <details class="text-xs">
                  <summary class="cursor-pointer">Absolute (opsional)</summary>
                  <div class="grid grid-cols-2 gap-2 mt-2">
                    <input x-model.number="sg.top" class="border rounded px-2 py-1" placeholder="top(px)">
                    <input x-model.number="sg.left" class="border rounded px-2 py-1" placeholder="left(px)">
                  </div>
                </details>

                <button type="button" @click="signatures.rows.splice(i,1)" class="text-red-600 text-left">hapus</button>
              </div>
            </div>
          </template>
        </div>

        {{-- Preview header --}}
        <div class="mt-4 border rounded-lg overflow-hidden">
          <div class="bg-[#1D1C1A] text-white px-3 py-2 text-sm">Preview Header</div>
          <div class="p-4">
            <div class="flex items-center justify-between">
              <div x-show="header.logo.position==='left' && header.logo.url" class="w-24 h-10 flex items-center justify-center">
                <img :src="header.logo.url" class="max-h-10">
              </div>
              <div :class="{'text-left':header.title.align==='left','text-center':header.title.align==='center','text-right':header.title.align==='right'}" class="flex-1 px-3 font-semibold text-[#1D1C1A]">Judul Dokumen</div>
              <div x-show="header.logo.position==='right' && header.logo.url" class="w-24 h-10 flex items-center justify-center">
                <img :src="header.logo.url" class="max-h-10">
              </div>
            </div>
            <div class="mt-2 text-xs text-gray-600">Doc.No / Rev.No / Eff.Date muncul saat render.</div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

@push('scripts')
<script>
function docBuilder(){
  return {
    header:{ logo:{url:'',position:'left'}, title:{align:'center'} },
    footer:{ text:'', show_page_number:true },
    signatures:{ rows:[], columns:4, mode:'grid' },
    sections: @json($defaultSections),
    init(){},
    addSigner(){ this.signatures.rows.push({role:'Disiapkan',name:'',position_title:'',image_path:'',colStart:1,colSpan:1}); },
    addSection(){ this.sections.push({key:'custom_'+Date.now(),label:'Section Baru',html:''}); }
  }
}
</script>
@endpush
@endsection
