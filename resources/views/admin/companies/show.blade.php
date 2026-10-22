{{-- resources/views/admin/companies/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Perusahaan')
@section('content')
<x-ui.page class="w-full min-w-0 ">
  {{-- Header --}}
  @php
        // Pastikan logo aman tampil walau logo_path kadang punya prefix salah
        $logoShow = $company->logo_url ?? null;
        if(!$logoShow && !empty($company->logo_path)){
            $p = ltrim(preg_replace('#^(public/|storage/)#','',$company->logo_path),'/');
            if (\Illuminate\Support\Facades\Storage::disk('mandala_uploads')->exists($p)) {
                $logoShow = route('pubfile.stream', ['path' => $p]);
            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($p)) {
                $logoShow = asset('storage/'.$p);
            }
        }
      @endphp
@php
              $badge = match($company->status){
                'inactive' => 'ui-status ui-status--warning',
                'archived' => 'bg-coal-100 text-coal-700 border-coal-300',
                default    => 'ui-status ui-status--success',
              };
            @endphp
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>{{ $company->name }}</x-ui.page-title><x-ui.page-description>Kelola profil perusahaan dan informasi operasionalnya.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('admin.companies.index') }}">Kembali</x-ui.link><div class="flex items-center gap-2">
      <x-ui.link data-modal-form variant="primary" href="{{ route('admin.companies.edit', $company) }}"
         class="rounded-lg px-4 py-2 border">
        Edit
      </x-ui.link>
      <form action="{{ route('admin.companies.destroy', $company) }}" method="POST"
            data-confirm="Hapus perusahaan ini?">
        @csrf @method('DELETE')
        <x-ui.button variant="danger"
          class="rounded-lg px-4 py-2 border">
          Hapus
        </x-ui.button>
      </form>
    </div></x-ui.page-actions>
</x-ui.page-header>
<div class="flex items-center gap-3">
  @if($logoShow)<img src="{{ $logoShow }}" class="h-12 w-12 rounded-lg object-cover" alt="Logo {{ $company->name }}">@endif
  <span class="text-sm text-coal-500">Kode: {{ $company->code }}</span>
  <span class="{{ $badge }}">{{ ucfirst($company->status ?? 'active') }}</span>
</div>
  {{-- Grid detail --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    {{-- Kolom kiri --}}
    <div class="md:col-span-2 space-y-4">
      {{-- Profil --}}
      <x-ui.panel class="rounded-lg border bg-white">
        <x-ui.section-title class="font-semibold mb-3 text-[color:var(--brand-maroon,#713F12)]">Profil</x-ui.section-title>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-coal-500">Nama</dt>
          <dd class="col-span-2">{{ $company->name }}</dd>
          <dt class="text-coal-500">Badan Hukum</dt>
          <dd class="col-span-2">{{ $company->legal_name ?: '—' }}</dd>
          <dt class="text-coal-500">Slug</dt>
          <dd class="col-span-2">{{ $company->slug ?: '—' }}</dd>
          <dt class="text-coal-500">Industri</dt>
          <dd class="col-span-2">{{ $company->industry ?: '—' }}</dd>
          <dt class="text-coal-500">NPWP</dt>
          <dd class="col-span-2">{{ $company->npwp ?: '—' }}</dd>
          <dt class="text-coal-500">NIB</dt>
          <dd class="col-span-2">{{ $company->nib ?: '—' }}</dd>
        </dl>
      </x-ui.panel>
      {{-- Kontak & Situs --}}
      <x-ui.panel class="rounded-lg border bg-white">
        <x-ui.section-title class="font-semibold mb-3 text-[color:var(--brand-maroon,#713F12)]">Kontak & Situs</x-ui.section-title>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-coal-500">Email</dt>
          <dd class="col-span-2">{{ $company->email ?: '—' }}</dd>
          <dt class="text-coal-500">Telepon</dt>
          <dd class="col-span-2">{{ $company->phone ?: '—' }}</dd>
          <dt class="text-coal-500">Website</dt>
          <dd class="col-span-2">
            @if($company->website)
              <a href="{{ \Illuminate\Support\Str::startsWith($company->website, ['http://','https://']) ? $company->website : 'https://'.$company->website }}"
                 target="_blank"
                 class="text-[color:var(--brand-maroon,#713F12)] underline underline-offset-4">
                {{ $company->website }}
              </a>
            @else
              —
            @endif
          </dd>
        </dl>
      </x-ui.panel>
      {{-- Alamat --}}
      <x-ui.panel class="rounded-lg border bg-white">
        <x-ui.section-title class="font-semibold mb-3 text-[color:var(--brand-maroon,#713F12)]">Alamat</x-ui.section-title>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-coal-500">Alamat</dt>
          <dd class="col-span-2 whitespace-pre-line">{{ $company->hq_address ?: '—' }}</dd>
          <dt class="text-coal-500">Kota</dt>
          <dd class="col-span-2">{{ $company->city ?: '—' }}</dd>
          <dt class="text-coal-500">Provinsi</dt>
          <dd class="col-span-2">{{ $company->province ?: '—' }}</dd>
          <dt class="text-coal-500">Kode Pos</dt>
          <dd class="col-span-2">{{ $company->postal_code ?: '—' }}</dd>
          <dt class="text-coal-500">Negara</dt>
          <dd class="col-span-2">{{ $company->country ?: 'ID' }}</dd>
        </dl>
      </x-ui.panel>
    </div>
    {{-- Kolom kanan (meta) --}}
    <div class="space-y-4">
      <x-ui.panel class="rounded-lg border bg-white">
        <x-ui.section-title class="font-semibold mb-3 text-[color:var(--brand-maroon,#713F12)]">Pengaturan</x-ui.section-title>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-coal-500">Zona Waktu</dt>
          <dd class="col-span-2">{{ $company->timezone ?: 'Asia/Jakarta' }}</dd>
          <dt class="text-coal-500">Mata Uang</dt>
          <dd class="col-span-2">{{ $company->currency ?: 'IDR' }}</dd>
        </dl>
      </x-ui.panel>
      <x-ui.panel class="rounded-lg border bg-white">
        <x-ui.section-title class="font-semibold mb-3 text-[color:var(--brand-maroon,#713F12)]">Metadata</x-ui.section-title>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-coal-500">Dibuat</dt>
          <dd class="col-span-2">{{ optional($company->created_at)->format('d M Y H:i') ?? '—' }}</dd>
          <dt class="text-coal-500">Diubah</dt>
          <dd class="col-span-2">{{ optional($company->updated_at)->format('d M Y H:i') ?? '—' }}</dd>
        </dl>
      </x-ui.panel>
    </div>
  </div>
</x-ui.page>
@endsection