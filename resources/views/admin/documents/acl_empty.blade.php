@extends('layouts.app')
@section('title','Dokumen Tidak Ditemukan')

@section('content')
  <x-ui.page class="max-w-2xl mx-auto">
    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Belum ada document</x-ui.page-title>
<x-ui.page-description>Kelola dokumen, revisi, dan hak akses.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>
    <p class="text-slate-600 mb-4">
      Dokumen yang kamu minta tidak ditemukan. Pilih dokumen lain atau buat dokumen baru.
    </p>
    <x-ui.link variant="secondary" href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-lg border">← Kembali ke daftar dokumen</x-ui.link>
  </x-ui.page>
@endsection
