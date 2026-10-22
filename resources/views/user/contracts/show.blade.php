@extends('layouts.app')
@section('title', $contract->title)
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('user.contracts.index') }}" class="hover:underline">Kontrak Saya</a>
    <span class="mx-2">/</span>
    <span>{{ $contract->title }}</span>
@endsection
@section('content')
<x-ui.page class="w-full min-w-0 ">
  {{-- Header --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>{{ $contract->title }}</x-ui.page-title>
      <x-ui.page-description>
        Owner: {{ optional($contract->owner)->name }} — {{ optional($contract->owner)->email }}
      </x-ui.page-description>
    </div></div>
  <x-ui.page-actions><div class="flex flex-wrap items-center gap-2">
      <x-ui.link variant="primary" href="{{ route('user.contracts.download', $contract) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg">
        ⬇ Download PDF
      </x-ui.link>
      <x-ui.back-link href="{{ route('user.contracts.index') }}">Kembali</x-ui.back-link>
    </div></x-ui.page-actions>
</x-ui.page-header>
  {{-- Alerts --}}
  @if(session('err'))
    <div class="p-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200">{{ session('err') }}</div>
  @endif
  {{-- Info Card --}}
  <div class="rounded-lg border shadow-none overflow-hidden">
    <div class="px-4 py-3 bg-[#713F12] text-white">
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
  <div class="rounded-lg border bg-ivory-50 p-4">
    <div class="text-sm text-coal-600 mb-2">
      Preview dokumen. Jika tidak tampil, gunakan tombol Unduh.
    </div>
    <x-ui.panel class="w-full aspect-[1/1.3] bg-white border rounded-lg overflow-hidden">
      {{-- Gunakan route preview untuk menampilkan PDF inline --}}
      <iframe
        src="{{ route('user.contracts.preview', $contract) }}"
        title="Preview {{ $contract->title }}"
        class="w-full h-full"
        style="border:0;"
      ></iframe>
    </x-ui.panel>
    <noscript>
      <div class="mt-3 text-sm">
        Browser kamu tidak mendukung preview. <a class="underline" href="{{ route('user.contracts.download', $contract) }}">Unduh PDF</a>.
      </div>
    </noscript>
  </div>
</x-ui.page>
@endsection