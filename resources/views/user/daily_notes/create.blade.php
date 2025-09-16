@extends('layouts.app')
@section('title','Input Catatan Harian')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-maroon-700">Input Catatan Harian</h1>

<form action="{{ route('user.daily_notes.store') }}" method="post"
      class="space-y-4 max-w-xl bg-white p-6 rounded-xl shadow">
  @csrf

  {{-- Judul tidak perlu diisi user, otomatis Daily Hari Ini --}}
  <div>
    <label class="block font-semibold">Judul</label>
    <input type="text" value="Daily Hari Ini" class="w-full border rounded p-2 bg-gray-100 text-gray-600" readonly>
  </div>

  <div>
    <label class="block font-semibold">Isi Catatan</label>
    <textarea name="content" rows="5" class="w-full border rounded p-2" required></textarea>
  </div>

  <div>
    <label class="block font-semibold">Waktu</label>
    <input type="text" value="{{ now()->format('d-m-Y H:i') }}"
           class="w-full border rounded p-2 bg-gray-100 text-gray-600" readonly>
  </div>

  <button type="submit"
          class="px-4 py-2 rounded bg-maroon-600 text-white hover:bg-maroon-700">
    Simpan
  </button>
</form>
@endsection
