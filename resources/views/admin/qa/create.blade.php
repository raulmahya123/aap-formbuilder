@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl rounded-2xl border border-coal-200 bg-white p-6 shadow-sm">
  <div class="mb-5">
    <p class="text-sm font-medium text-maroon-700">Forum site</p>
    <h1 class="mt-1 text-2xl font-semibold text-coal-900">Buat postingan baru</h1>
    <p class="mt-1 text-sm text-coal-500">Postingan hanya akan terlihat oleh pengguna yang memiliki akses ke site yang dipilih.</p>
  </div>

  <form method="POST" action="{{ route('admin.qa.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <div>
      <label class="mb-1 block text-sm font-medium text-coal-700">Site</label>
      <select name="site_id" required onchange="if (this.value) window.location='{{ route('admin.qa.create') }}?site_id='+this.value" class="w-full rounded-xl border-coal-200 focus:border-maroon-500 focus:ring-maroon-500">
        <option value="">Pilih site</option>
        @foreach($sites as $site)
          <option value="{{ $site->id }}" @selected((int) old('site_id', request('site_id')) === (int) $site->id)>{{ $site->code }} — {{ $site->name }}</option>
        @endforeach
      </select>
      @error('site_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium text-coal-700">Judul diskusi</label>
      <input type="text" name="subject" value="{{ old('subject') }}" required class="w-full rounded-xl border-coal-200 focus:border-maroon-500 focus:ring-maroon-500">
    </div>

    <div x-data="{scope:'public'}">
      <label class="block text-sm font-medium text-coal-700">Privasi diskusi</label>
      <select name="scope" x-model="scope" class="w-full rounded-lg border-gray-300 focus:border-maroon-500 focus:ring-maroon-500">
        <option value="public">Publik (semua user bisa lihat)</option>
        <option value="private">Grup privat (anggota yang dipilih)</option>
      </select>

      <div x-show="scope==='private'" x-cloak class="mt-3 rounded-xl border border-coal-200 bg-ivory-50 p-3">
        <label class="block text-sm font-medium text-coal-700">Anggota grup</label>
        <p class="mt-1 text-xs text-coal-500">Pilih orang yang boleh membaca dan membalas chat ini.</p>
        @if($members->isEmpty())
          <p class="mt-3 text-sm text-amber-700">Pilih site terlebih dahulu untuk melihat anggota.</p>
        @else
          <div class="mt-3 grid max-h-52 gap-2 overflow-y-auto sm:grid-cols-2">
            @foreach($members as $member)
              @if($member->id !== auth()->id())
                <label class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm hover:bg-maroon-50">
                  <input type="checkbox" name="recipient_ids[]" value="{{ $member->id }}" @checked(in_array($member->id, old('recipient_ids', []))) class="rounded border-coal-300 text-maroon-700 focus:ring-maroon-500">
                  <span class="min-w-0"><span class="block truncate font-medium text-coal-800">{{ $member->name }}</span><span class="block truncate text-xs text-coal-500">{{ $member->email }}</span></span>
                </label>
              @endif
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-coal-700">Isi postingan</label>
      <textarea name="body" rows="6" required class="w-full rounded-xl border-coal-200 focus:border-maroon-500 focus:ring-maroon-500" placeholder="Tulis pertanyaan, informasi, atau topik diskusi...">{{ old('body') }}</textarea>
    </div>

    <div>
      <label class="mb-1 block text-sm font-medium text-coal-700">Lampiran laporan (opsional)</label>
      <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt" class="block w-full rounded-xl border border-coal-200 bg-white px-3 py-2 text-sm text-coal-600 file:mr-3 file:rounded-lg file:border-0 file:bg-ivory-100 file:px-3 file:py-1.5 file:text-sm file:font-medium">
      <p class="mt-1 text-xs text-coal-500">Maksimal 5 file, masing-masing 10 MB.</p>
    </div>

    <button type="submit"
            class="inline-flex items-center rounded-xl bg-maroon-700 px-4 py-2.5 font-semibold text-white hover:bg-maroon-800">
      Publikasikan
    </button>
  </form>
</div>
@endsection
