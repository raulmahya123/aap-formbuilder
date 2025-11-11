@extends('layouts.app')

@section('title', 'Edit Perusahaan')

@section('content')
<div class="max-w-5xl mx-auto p-4 md:p-6">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">Edit Perusahaan</h1>
    <a href="{{ route('admin.companies.index') }}" class="px-3 py-2 rounded-lg border hover:bg-ivory-100">← Kembali</a>
  </div>

  <div class="rounded-2xl border bg-white p-4 md:p-6">
    <form action="{{ route('admin.companies.update', $company) }}" method="POST" enctype="multipart/form-data" class="grid gap-6">
      @csrf
      @method('PUT')
      @include('admin.companies._form', ['company' => $company])
      <div class="flex items-center gap-3">
        <button class="px-4 py-2 rounded-xl bg-[color:var(--brand-blue,#1a73e8)] text-white hover:brightness-105">
          Update
        </button>
        <a href="{{ route('admin.companies.index') }}" class="px-4 py-2 rounded-xl border hover:bg-ivory-100">Batal</a>
      </div>
    </form>
  </div>
</div>
@endsection
