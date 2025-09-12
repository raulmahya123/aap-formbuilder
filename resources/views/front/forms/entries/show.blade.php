
@extends('layouts.app')
@section('title','Detail Entri')
@section('content')
<h1 class="text-2xl font-bold mb-4">Detail Entri</h1>

<div class="p-4 border rounded bg-white">
  <div class="mb-2"><span class="font-semibold">Form:</span> {{ $entry->form->title ?? 'Form' }}</div>
  <div class="mb-2"><span class="font-semibold">ID Entri:</span> #{{ $entry->id }}</div>
  <div class="mb-2"><span class="font-semibold">Tanggal:</span> {{ $entry->created_at?->format('Y-m-d H:i') }}</div>

  <div class="mt-4">
    <h2 class="font-semibold mb-1">Jawaban</h2>
    <pre class="text-sm bg-gray-50 p-3 rounded overflow-auto">{{ json_encode($entry->answers, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
  </div>

  {{-- Jika ada lampiran, tampilkan daftar unduh --}}
  {{-- <div class="mt-4">
    <h2 class="font-semibold mb-1">Lampiran</h2>
    @foreach($entry->files as $f)
      <div>
        <a class="text-blue-600 hover:underline" href="{{ route('front.entry.download.attachment', $f->id) }}">
          {{ $f->original_name ?? basename($f->path) }}
        </a>
      </div>
    @endforeach
  </div> --}}
</div>
@endsection
