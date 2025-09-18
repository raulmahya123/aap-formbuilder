@extends('layouts.app')
@section('title','Input Catatan Harian')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-maroon-700">Input Catatan Harian</h1>

<form action="{{ route('user.daily_notes.store') }}" method="post"
      class="space-y-4 max-w-xl bg-white p-6 rounded-xl shadow">
  @csrf

  {{-- Judul wajib diisi user --}}
  <div>
    <label class="block font-semibold">Judul</label>
    <input type="text" name="title" value="{{ old('title') }}"
           class="w-full border rounded p-2 @error('title') border-red-500 @enderror" required>
    @error('title')
      <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <label class="block font-semibold">Isi Catatan</label>
    <textarea name="content" rows="5"
              class="w-full border rounded p-2 @error('content') border-red-500 @enderror" required>{{ old('content') }}</textarea>
    @error('content')
      <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
  </div>

  <div>
    <label class="block font-semibold">Waktu</label>
    <input type="text" value="{{ now('Asia/Jakarta')->format('d-m-Y H:i') }}"
           class="w-full border rounded p-2 bg-gray-100 text-gray-600" readonly>
  </div>

  <button type="submit"
          class="px-4 py-2 rounded bg-maroon-600 text-white hover:bg-maroon-700">
    Simpan
  </button>
</form>
@endsection
