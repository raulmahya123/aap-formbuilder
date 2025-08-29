@extends('layouts.app')

@section('title','Edit Document')

@section('content')
<div x-data="docBuilder()" x-init="init()" class="max-w-5xl mx-auto p-6 space-y-6">
  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">Edit Dokumen</h1>
    <a href="{{ route('admin.documents.index') }}"
       class="px-4 py-2 rounded-xl border text-[#1D1C1A]">← Kembali</a>
  </div>

  {{-- FORM --}}
  <form method="POST"
        action="{{ route('admin.documents.update',$document) }}"
        enctype="multipart/form-data"
        class="bg-white border rounded-xl p-6 space-y-6">
    @csrf
    @method('PUT')

    <div class="grid md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-medium">Judul</label>
        <input type="text" name="title" value="{{ old('title',$document->title) }}"
               class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-medium">Nomor Dokumen</label>
        <input type="text" name="doc_no" value="{{ old('doc_no',$document->doc_no) }}"
               class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-medium">Revisi</label>
        <input type="number" name="revision_no" value="{{ old('revision_no',$document->revision_no) }}"
               class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-medium">Tanggal Efektif</label>
        <input type="date" name="effective_date" value="{{ old('effective_date',optional($document->effective_date)->format('Y-m-d')) }}"
               class="mt-1 w-full border rounded-lg px-3 py-2">
      </div>
    </div>

    {{-- QR & Barcode --}}
    <div class="bg-white border rounded-xl p-4 space-y-3">
      <h2 class="font-semibold text-[#1D1C1A]">QR & Barcode</h2>
      <div>
        <label class="text-sm font-medium">QR Text</label>
        <input type="text" name="qr_text" value="{{ old('qr_text',$document->qr_text) }}"
               class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="contoh: https://intra/verify/{{ '{doc_no}' }}">
      </div>
      <div>
        <label class="text-sm font-medium">Barcode Text</label>
        <input type="text" name="barcode_text" value="{{ old('barcode_text',$document->barcode_text) }}"
               class="mt-1 w-full border rounded-lg px-3 py-2" placeholder="PLT-SOP-003-REV1">
      </div>
      @if($document->qr_image_path || $document->barcode_image_path)
      <div class="flex gap-6 mt-3">
        @if($document->qr_image_path)
          <div><img src="{{ $document->qr_image_path }}" class="h-28"><div class="text-xs mt-1">QR Sekarang</div></div>
        @endif
        @if($document->barcode_image_path)
          <div><img src="{{ $document->barcode_image_path }}" class="h-20"><div class="text-xs mt-1">Barcode Sekarang</div></div>
        @endif
      </div>
      @endif
    </div>

    {{-- SIGNATURES --}}
    <div class="border rounded-xl p-4 space-y-4">
      <h2 class="font-semibold text-[#1D1C1A]">Pengesahan (TTD)</h2>

      @foreach($document->signatures as $i => $sig)
        <div class="p-3 border rounded-lg space-y-3">
          <div class="grid md:grid-cols-3 gap-3">
            <div>
              <label class="text-sm">Role</label>
              <input type="text" name="signatures[{{ $i }}][role]"
                     value="{{ old("signatures.$i.role",$sig->role) }}"
                     class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
            <div>
              <label class="text-sm">Nama</label>
              <input type="text" name="signatures[{{ $i }}][name]"
                     value="{{ old("signatures.$i.name",$sig->name) }}"
                     class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
            <div>
              <label class="text-sm">Jabatan</label>
              <input type="text" name="signatures[{{ $i }}][position_title]"
                     value="{{ old("signatures.$i.position_title",$sig->position_title) }}"
                     class="mt-1 w-full border rounded-lg px-3 py-2">
            </div>
          </div>

          {{-- URL TTD --}}
          <div>
            <label class="text-sm">TTD (URL)</label>
            <input type="text" name="signatures[{{ $i }}][image_path]"
                   value="{{ old("signatures.$i.image_path",$sig->image_path) }}"
                   class="mt-1 w-full border rounded-lg px-3 py-2">
          </div>

          {{-- Signature Pad --}}
          <div>
            <label class="text-sm">Tanda Tangan Langsung</label>
            <div class="flex items-start gap-3">
              <canvas id="pad_{{ $i }}" width="300" height="100" class="bg-white border"></canvas>
              <div class="flex flex-col gap-2">
                <input type="hidden" name="esign_data[{{ $i }}]" id="esign_input_{{ $i }}">
                <button type="button" class="px-3 py-1 rounded bg-[#7A2C2F] text-white"
                        onclick="savePad({{ $i }})">Simpan</button>
                <button type="button" class="px-3 py-1 rounded border"
                        onclick="clearPad({{ $i }})">Bersihkan</button>
              </div>
            </div>
            @if($sig->signed_image_path)
              <div class="mt-2"><img src="{{ $sig->signed_image_path }}" class="h-16"><div class="text-xs">TTD tersimpan</div></div>
            @endif
          </div>
        </div>
      @endforeach
    </div>

    <div class="pt-2 flex items-center justify-end gap-3">
      <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-xl border text-[#1D1C1A]">Batal</a>
      <button class="px-4 py-2 rounded-xl bg-[#7A2C2F] text-white hover:opacity-90">Update</button>
    </div>
  </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
<script>
let pads = [];
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("canvas[id^='pad_']").forEach((c, i) => {
    pads[i] = new SignaturePad(c, { minWidth:1, maxWidth:2 });
  });
});
function savePad(i){
  if (!pads[i] || pads[i].isEmpty()){ alert("TTD masih kosong"); return; }
  const dataUrl = pads[i].toDataURL("image/png");
  document.getElementById("esign_input_"+i).value = dataUrl;
}
function clearPad(i){
  pads[i]?.clear();
  document.getElementById("esign_input_"+i).value = "";
}
</script>
@endpush
@endsection
