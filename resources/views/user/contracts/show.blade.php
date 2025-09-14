@extends('layouts.app')
@section('title', $contract->title)

@section('breadcrumbs')
  <nav class="text-sm text-coal-600 dark:text-coal-300">
    <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('user.contracts.index') }}" class="hover:underline">Kontrak Saya</a>
    <span class="mx-2">/</span>
    <span>{{ $contract->title }}</span>
  </nav>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-[#1D1C1A]">{{ $contract->title }}</h1>
      <p class="text-sm text-coal-500">
        Owner: {{ optional($contract->owner)->name }} — {{ optional($contract->owner)->email }}
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('user.contracts.download', $contract) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">
        ⬇ Download PDF
      </a>
      <a href="{{ route('user.contracts.index') }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-coal-300 hover:bg-ivory-100">
        ← Kembali
      </a>
    </div>
  </div>

  {{-- Alerts --}}
  @if(session('ok'))
    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('ok') }}</div>
  @endif
  @if(session('err'))
    <div class="p-3 rounded-xl bg-rose-50 text-rose-800 border border-rose-200">{{ session('err') }}</div>
  @endif

  {{-- Info Card --}}
  <div class="rounded-2xl border shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-[#7A2C2F] text-white">
      <div class="font-semibold">Informasi Kontrak</div>
    </div>
    <div class="p-4 grid sm:grid-cols-2 gap-4 text-sm">
      <div class="space-y-1">
        <div class="text-coal-500">Judul</div>
        <div class="font-medium">{{ $contract->title }}</div>
      </div>
      <div class="space-y-1">
        <div class="text-coal-500">Owner</div>
        <div class="font-medium">{{ optional($contract->owner)->name }}</div>
        <div class="text-coal-500">{{ optional($contract->owner)->email }}</div>
      </div>
      <div class="space-y-1">
        <div class="text-coal-500">Ukuran</div>
        <div class="font-medium">
          @php
            $kb = $contract->size_bytes ? number_format($contract->size_bytes/1024,1) : null;
          @endphp
          {{ $kb ? ($kb.' KB') : '-' }}
        </div>
      </div>
      <div class="space-y-1">
        <div class="text-coal-500">MIME</div>
        <div class="font-medium">{{ $contract->mime ?? 'application/pdf' }}</div>
      </div>
      <div class="space-y-1 sm:col-span-2">
        <div class="text-coal-500">Dibuat</div>
        <div class="font-medium">{{ optional($contract->created_at)->format('d M Y H:i') }}</div>
      </div>
    </div>
  </div>

  {{-- Preview inline --}}
  <div class="rounded-2xl border bg-ivory-50 p-4">
    <div class="text-sm text-coal-600 mb-2">
      Preview dokumen. Jika tidak tampil, gunakan tombol Unduh.
    </div>

    <div class="w-full aspect-[1/1.3] bg-white border rounded-xl overflow-hidden">
      {{-- Gunakan route preview untuk menampilkan PDF inline --}}
      <iframe
        src="{{ route('user.contracts.preview', $contract) }}"
        title="Preview {{ $contract->title }}"
        class="w-full h-full"
        style="border:0;"
      ></iframe>
    </div>

    <noscript>
      <div class="mt-3 text-sm">
        Browser kamu tidak mendukung preview. <a class="underline" href="{{ route('user.contracts.download', $contract) }}">Unduh PDF</a>.
      </div>
    </noscript>
  </div>

</div>
@endsection
