@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white dark:bg-coal-900 rounded-xl shadow">
  <h1 class="text-xl font-semibold mb-4 text-maroon-700">Buat Thread Baru</h1>

  <form method="POST" action="{{ route('admin.qa.store') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium text-gray-700">Judul</label>
      <input type="text" name="subject" class="w-full rounded-lg border-gray-300 focus:border-maroon-500 focus:ring-maroon-500">
    </div>

    <div x-data="{scope:'public'}">
      <label class="block text-sm font-medium text-gray-700">Tipe Thread</label>
      <select name="scope" x-model="scope" class="w-full rounded-lg border-gray-300 focus:border-maroon-500 focus:ring-maroon-500">
        <option value="public">Publik (semua user bisa lihat)</option>
        <option value="private">Privat (hanya dengan Admin/Super Admin)</option>
      </select>

      <div x-show="scope==='private'" class="mt-3">
        <label class="block text-sm font-medium text-gray-700">Tujuan</label>
        <select name="recipient_id" class="w-full rounded-lg border-gray-300 focus:border-maroon-500 focus:ring-maroon-500">
          @foreach($admins as $a)
            <option value="{{ $a->id }}">{{ $a->name }} — {{ strtoupper($a->role) }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Pertanyaan</label>
      <textarea name="body" rows="5" class="w-full rounded-lg border-gray-300 focus:border-maroon-500 focus:ring-maroon-500"></textarea>
    </div>

    <button type="submit"
            class="px-4 py-2 rounded-lg bg-maroon-700 text-white hover:bg-maroon-600">
      Kirim
    </button>
  </form>
</div>
@endsection
