@extends('layouts.admin')
@section('title','Dokumen Tidak Ditemukan')

@section('content')
  <div class="max-w-2xl mx-auto my-10 p-6 border rounded-2xl bg-white">
    <h1 class="text-xl font-semibold mb-2">Belum ada document</h1>
    <p class="text-slate-600 mb-4">
      Dokumen yang kamu minta tidak ditemukan. Pilih dokumen lain atau buat dokumen baru.
    </p>
    <a href="{{ route('admin.documents.index') }}" class="px-4 py-2 rounded-xl border hover:bg-gray-50">← Kembali ke daftar dokumen</a>
  </div>
@endsection
