
@extends('layouts.app')
@section('title','Detail Entri')
@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Detail Entri</x-ui.page-title>
<x-ui.page-description>Tinjau detail entri form dan tindakan yang tersedia.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

<x-ui.panel class="border rounded-lg bg-white">
  <div class="mb-2"><span class="font-semibold">Form:</span> {{ $entry->form->title ?? 'Form' }}</div>
  <div class="mb-2"><span class="font-semibold">ID Entri:</span> #{{ $entry->id }}</div>
  <div class="mb-2"><span class="font-semibold">Tanggal:</span> {{ $entry->created_at?->format('Y-m-d H:i') }}</div>

  <div class="mt-4">
    <x-ui.section-title class="font-semibold mb-1">Jawaban</x-ui.section-title>
    <pre class="text-sm bg-gray-50 p-3 rounded-lg overflow-auto">{{ json_encode($entry->answers, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
  </div>

  {{-- Jika ada lampiran, tampilkan daftar unduh --}}
  {{-- <div class="mt-4">
    <x-ui.section-title class="font-semibold mb-1">Lampiran</x-ui.section-title>
    @foreach($entry->files as $f)
      <div>
        <a class="text-blue-600 hover:underline" href="{{ route('front.entry.download.attachment', $f->id) }}">
          {{ $f->original_name ?? basename($f->path) }}
        </a>
      </div>
    @endforeach
  </div> --}}
</x-ui.panel>
@endsection
