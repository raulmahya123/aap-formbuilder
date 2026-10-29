@extends('layouts.app')

@section('content')
<x-ui.page class="w-full min-w-0 ">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Buat postingan baru</x-ui.page-title>
    <x-ui.page-description>Postingan hanya akan terlihat oleh pengguna yang memiliki akses ke site yang dipilih.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

  <form method="POST" action="{{ route('admin.qa.store') }}" enctype="multipart/form-data" >
    @csrf
    <div>
      <label class="block">Site</label>
      <select name="site_id" required onchange="if (this.value) window.location='{{ route('admin.qa.create') }}?site_id='+this.value" class="w-full ui-input">
        <option value="">Pilih site</option>
        @foreach($sites as $site)
          <option value="{{ $site->id }}" @selected((int) $selectedSiteId === (int) $site->id)>{{ $site->code }} — {{ $site->name }}</option>
        @endforeach
      </select>
      @error('site_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="block">Judul diskusi</label>
      <input type="text" name="subject" value="{{ old('subject') }}" required class="w-full ui-input">
    </div>

    <div>
      <label class="block">Privasi diskusi</label>
      <select name="scope" class="w-full ui-input">
        <option value="public">Publik (semua user bisa lihat)</option>
        <option value="private">Grup privat (anggota yang dipilih)</option>
      </select>

      <div class="mt-3 rounded-lg border border-coal-200 bg-ivory-50 p-3">
        <label class="block">Anggota grup privat <span class="font-normal text-coal-500">(opsional)</span></label>
        <p class="mt-1 text-xs text-coal-500">Pilihan ini digunakan jika tipe diskusi diubah menjadi grup privat.</p>
        @if($members->isEmpty())
          <p class="mt-3 text-sm text-amber-700">Pilih site terlebih dahulu untuk melihat anggota.</p>
        @else
          <div class="mt-3 grid max-h-52 gap-2 overflow-y-auto sm:grid-cols-2">
            @foreach($members as $member)
              @if($member->id !== auth()->id())
                <label class="flex items-center">
                  <input type="checkbox" name="recipient_ids[]" value="{{ $member->id }}" @checked(in_array($member->id, old('recipient_ids', []))) class="w-full ui-input">
                  <span class="min-w-0"><span class="block truncate font-medium text-coal-800">{{ $member->name }}</span><span class="block truncate text-xs text-coal-500">{{ $member->email }}</span></span>
                </label>
              @endif
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <div>
      <label class="block">Isi postingan</label>
      <textarea name="body" rows="6" required class="w-full ui-input" placeholder="Tulis pertanyaan, informasi, atau topik diskusi...">{{ old('body') }}</textarea>
    </div>

    <div>
      <label class="block">Lampiran laporan (opsional)</label>
      <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt" class="block w-full">
      <p class="mt-1 text-xs text-coal-500">Maksimal 5 file, masing-masing 10 MB.</p>
    </div>

    <x-ui.button variant="primary" type="submit"
            class="inline-flex items-center">
      Publikasikan
    </x-ui.button>
  </form>
</x-ui.page>
@endsection
