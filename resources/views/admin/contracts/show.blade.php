@extends('layouts.app')
@section('title', $contract->title)
@section('content')
<x-ui.page class="w-full min-w-0 ">
  {{-- Header --}}
  <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
      <x-ui.page-title>{{ $contract->title }}</x-ui.page-title>
      <x-ui.page-description>Kontrak • UUID: <span class="font-mono">{{ $contract->uuid }}</span></x-ui.page-description>
    </div></div>
  <x-ui.page-actions><div class="flex flex-wrap items-center gap-2">
      <x-ui.link variant="primary" href="{{ route('admin.contracts.download', $contract) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 3v10.586l3.293-3.293 1.414 1.414L12 17.414l-4.707-4.707 1.414-1.414L12 13.586V3h0z"/>
          <path d="M5 19h14v2H5z"/>
        </svg>
        Download PDF
      </x-ui.link>
      <x-ui.back-link href="{{ route('admin.contracts.index') }}">Kembali</x-ui.back-link>
    </div></x-ui.page-actions>
</x-ui.page-header>
  {{-- Alerts --}}
  @if(session('err'))
    <div class="p-3 rounded-lg bg-rose-50 text-rose-800 border border-rose-200">{{ session('err') }}</div>
  @endif
  {{-- Grid: Info + ACL --}}
  <div class="grid lg:grid-cols-3 gap-5">
    {{-- Left: Info Card --}}
    <div class="lg:col-span-1">
      <div class="rounded-lg border shadow-none overflow-hidden">
        <div class="px-4 py-3 bg-[#713F12] text-white">
          <div class="font-semibold">Informasi Kontrak</div>
        </div>
        <div class="p-4 space-y-4">
          <div class="flex items-start gap-3">
            <div class="shrink-0 w-10 h-10 rounded-full bg-[#713F12] text-white flex items-center justify-center">
              {{ strtoupper(Str::of($contract->owner->name)->explode(' ')->map(fn($s)=>Str::substr($s,0,1))->take(2)->implode('')) }}
            </div>
            <div class="min-w-0">
              <div class="text-sm text-coal-500">Owner</div>
              <div class="font-medium truncate">{{ $contract->owner->name }}</div>
              <div class="text-sm text-coal-500 truncate">{{ $contract->owner->email }}</div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="space-y-1">
              <div class="text-coal-500">Ukuran</div>
              <div class="font-medium">{{ number_format($contract->size_bytes/1024,1) }} KB</div>
            </div>
            <div class="space-y-1">
              <div class="text-coal-500">MIME</div>
              <div class="font-medium">{{ $contract->mime }}</div>
            </div>
            <div class="space-y-1 col-span-2">
              <div class="text-coal-500">Lokasi File</div>
              <div class="font-mono text-xs break-all text-coal-700">{{ $contract->file_path }}</div>
            </div>
            <div class="space-y-1 col-span-2">
              <div class="text-coal-500">Dibuat</div>
              <div class="font-medium">{{ $contract->created_at->format('d M Y H:i') }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    {{-- Right: ACL Card --}}
    <div class="lg:col-span-2">
      <div class="rounded-lg border shadow-none overflow-hidden">
        <div class="px-4 py-3 bg-[#713F12] text-white flex items-center justify-between">
          <div class="font-semibold">Akses Viewer</div>
          <span class="text-xs bg-white/20 px-2 py-1 rounded-md">{{ $contract->viewers->count() }} user</span>
        </div>
        <div class="p-4">
          {{-- Form: tambah viewer --}}
          <div class="mb-4">
            <form method="post" action="{{ route('admin.contracts.share', $contract) }}"
                  class="flex flex-col sm:flex-row gap-2">
              @csrf
              @isset($users)
                <select name="emails[]" multiple size="6"
                        class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:border-[#713F12]">
                  @foreach($users as $u)
                    <option value="{{ $u->email }}">{{ $u->name }} — {{ $u->email }}</option>
                  @endforeach
                </select>
              @else
                <input type="text" name="emails"
                       placeholder="email1@contoh.com, email2@contoh.com"
                       class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:border-[#713F12]" required>
              @endisset
              <x-ui.button variant="primary" class="shrink-0 px-4 py-2 rounded-lg hover:opacity-90">
                Tambah Akses
              </x-ui.button>
            </form>
            <p class="text-xs text-coal-500 mt-1">
              @isset($users)
                Pilih beberapa user (Ctrl/Cmd untuk multi-select).
              @else
                Pisahkan dengan koma untuk lebih dari satu email.
              @endisset
            </p>
          </div>
          {{-- Daftar viewers --}}
          <div class="rounded-lg border">
            @forelse($contract->viewers as $u)
              <div class="flex items-center justify-between gap-3 px-4 py-3 border-b last:border-b-0">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-9 h-9 rounded-full bg-maroon-700 text-ivory-50 flex items-center justify-center text-xs font-semibold"
                       style="background:#713F12">
                    {{ strtoupper(Str::of($u->name)->explode(' ')->map(fn($s)=>Str::substr($s,0,1))->take(2)->implode('')) }}
                  </div>
                  <div class="min-w-0">
                    <div class="font-medium truncate">{{ $u->name }}</div>
                    <div class="text-sm text-coal-500 truncate">{{ $u->email }}</div>
                  </div>
                </div>
                <form method="post" action="{{ route('admin.contracts.revoke', $contract) }}"
                      data-confirm="Cabut akses {{ $u->name }}?">
                  @csrf @method('delete')
                  <input type="hidden" name="user_id" value="{{ $u->id }}">
                  <x-ui.button variant="danger" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border">
                    Cabut
                  </x-ui.button>
                </form>
              </div>
            @empty
              <div class="p-6 text-center text-coal-500">
                Belum ada viewer. Tambahkan user pada form di atas.
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
  {{-- Preview PDF --}}
  <div class="rounded-lg border shadow-none overflow-hidden">
    <div class="px-4 py-3 bg-[#713F12] text-white">
      <div class="font-semibold">Preview Dokumen</div>
    </div>
    <div class="p-4">
      <iframe
        src="{{ route('admin.contracts.preview', $contract) }}"
        style="width:100%;height:800px;border:1px solid #e5e7eb;border-radius:12px"
        loading="lazy">
      </iframe>
      <p class="text-sm text-slate-500 mt-2">
        Jika preview tidak muncul, klik
        <x-ui.link variant="secondary" icon="file-down" href="{{ route('admin.contracts.download', $contract) }}">Unduh PDF</x-ui.link>.
      </p>
    </div>
  </div>
</x-ui.page>
@endsection