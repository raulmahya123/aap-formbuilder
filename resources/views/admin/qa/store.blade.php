{{-- resources/views/admin/qa/create.blade.php --}}
@extends('layouts.app')

@section('content')
<x-ui.page class="max-w-2xl mx-auto">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Buat Thread Q&A</x-ui.page-title>
<x-ui.page-description>Kelola percakapan dan tindak lanjut laporan.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

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
      <x-ui.link variant="secondary" href="{{ url()->previous() }}" class="px-3 py-2 rounded-lg border">Batal</x-ui.link>
      <x-ui.button variant="primary" class="px-4 py-2 rounded-lg">Simpan</x-ui.button>
    </div>
  </form>
</x-ui.page>
@endsection
