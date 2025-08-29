{{-- resources/views/admin/qa/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-4 sm:p-6">
  <h1 class="text-xl font-semibold mb-4">Buat Thread Q&A</h1>

  <form method="POST" action="{{ route('admin.qa.store') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium mb-1">Subject</label>
      <input type="text" name="subject" value="{{ old('subject') }}"
             class="w-full rounded-lg border p-2" required>
      @error('subject') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Assign ke (opsional)</label>
      <input type="number" name="assigned_to" value="{{ old('assigned_to') }}"
             class="w-full rounded-lg border p-2" placeholder="User ID">
      @error('assigned_to') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Pesan pertama (opsional)</label>
      <textarea name="message" rows="5" class="w-full rounded-lg border p-2"
                placeholder="Jelaskan persoalan atau konteks awal...">{{ old('message') }}</textarea>
      @error('message') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ url()->previous() }}" class="px-3 py-1.5 rounded-lg border">Batal</a>
      <button class="px-4 py-1.5 rounded-lg bg-emerald-600 text-white">Simpan</button>
    </div>
  </form>
</div>
@endsection
