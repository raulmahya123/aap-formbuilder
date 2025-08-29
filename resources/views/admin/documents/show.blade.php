@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold text-[#1D1C1A]">{{ $document->doc_no }} — {{ $document->title }}</h1>
    @can('update',$document)
      <a href="{{ route('admin.documents.edit',$document) }}" class="px-3 py-2 rounded-xl border text-[#1D1C1A]">Edit</a>
    @endcan
  </div>

  {{-- Header --}}
  <div class="border rounded-xl p-4 bg-white">
    <div class="flex items-center justify-between">
      @if(data_get($document->header_config,'logo.position')==='left' && data_get($document->header_config,'logo.url'))
        <img src="{{ data_get($document->header_config,'logo.url') }}" class="h-10">
      @endif
      <div class="flex-1 text-center font-semibold">{{ $document->title }}</div>
      @if(data_get($document->header_config,'logo.position')==='right' && data_get($document->header_config,'logo.url'))
        <img src="{{ data_get($document->header_config,'logo.url') }}" class="h-10">
      @endif
    </div>
    <div class="mt-2 text-xs text-gray-600">
      Doc.No: {{ $document->doc_no }} • Rev: {{ $document->revision_no }} • Eff.Date: {{ optional($document->effective_date)->format('d M Y') }}
    </div>
  </div>

  {{-- Sections --}}
  <div class="bg-white border rounded-xl p-4 space-y-6">
    @foreach(($document->sections ?? []) as $s)
      <div>
        <h2 class="font-semibold text-[#1D1C1A]">{{ $s['label'] ?? strtoupper($s['key']) }}</h2>
        <div class="prose max-w-none">{!! $s['html'] ?? '' !!}</div>
      </div>
    @endforeach
  </div>

  {{-- Signatures (mode grid sederhana) --}}
  @php($sig = $document->signature_config ?? [])
  <div class="bg-white border rounded-xl p-4">
    <h2 class="font-semibold text-[#1D1C1A] mb-3">Pengesahan</h2>
    @php($cols = $sig['columns'] ?? 4)
    <div class="grid gap-4" style="grid-template-columns: repeat({{ $cols }}, minmax(0,1fr));">
      @foreach(($sig['rows'] ?? []) as $r)
        <div class="text-center border rounded-lg p-3"
             style="grid-column: {{ $r['colStart'] ?? 1 }} / span {{ $r['colSpan'] ?? 1 }};">
          <div class="h-20 flex items-center justify-center">
            @if(!empty($r['image_path'])) <img src="{{ $r['image_path'] }}" class="max-h-20">@endif
          </div>
          <div class="mt-2 font-medium">{{ $r['name'] ?? '-' }}</div>
          <div class="text-xs text-gray-600">{{ $r['position_title'] ?? $r['role'] }}</div>
        </div>
      @endforeach
    </div>
  </div>
</div>
@endsection
