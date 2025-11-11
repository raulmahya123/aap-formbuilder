{{-- resources/views/admin/companies/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Perusahaan')

@section('content')
<div class="max-w-6xl mx-auto p-4 md:p-6">

  {{-- Header --}}
  <div class="mb-6 flex items-center justify-between gap-3">
    <div>
      <a href="{{ route('admin.companies.index') }}"
         class="text-sm underline underline-offset-4 text-[color:var(--brand-maroon,#7b1d2e)]">← Kembali</a>

      @php
        // Pastikan logo aman tampil walau logo_path kadang punya prefix salah
        $logoShow = $company->logo_url ?? null;
        if(!$logoShow && !empty($company->logo_path)){
            $p = ltrim(preg_replace('#^(public/|storage/)#','',$company->logo_path),'/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($p)) {
                $logoShow = asset('storage/'.$p);
            }
        }
      @endphp

      <div class="mt-2 flex items-start gap-3">
        @if($logoShow)
          <img src="{{ $logoShow }}" class="h-12 w-12 rounded object-cover ring-1 ring-black/5" alt="logo">
        @else
          <div class="h-12 w-12 rounded bg-[color:var(--brand-maroon,#7b1d2e)]/10 grid place-items-center text-[color:var(--brand-maroon,#7b1d2e)]">—</div>
        @endif

        <div>
          <h1 class="text-2xl font-semibold text-coal-900">{{ $company->name }}</h1>
          <div class="text-sm text-coal-500">
            Kode: <span class="font-medium text-coal-700">{{ $company->code }}</span>
            &middot; Status:
            @php
              $badge = match($company->status){
                'inactive' => 'bg-amber-100 text-amber-800 border-amber-300',
                'archived' => 'bg-coal-100 text-coal-700 border-coal-300',
                default    => 'bg-emerald-100 text-emerald-800 border-emerald-300',
              };
            @endphp
            <span class="px-1.5 py-0.5 text-xs rounded border align-middle {{ $badge }}">
              {{ ucfirst($company->status ?? 'active') }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.companies.edit', $company) }}"
         class="rounded-xl px-4 py-2 border border-[color:var(--brand-maroon,#7b1d2e)]
                text-[color:var(--brand-maroon,#7b1d2e)] hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10">
        Edit
      </a>
      <form action="{{ route('admin.companies.destroy', $company) }}" method="POST"
            onsubmit="return confirm('Hapus perusahaan ini?');">
        @csrf @method('DELETE')
        <button
          class="rounded-xl px-4 py-2 border border-[color:var(--brand-maroon,#7b1d2e)]/60
                 text-[color:var(--brand-maroon,#7b1d2e)] hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10">
          Hapus
        </button>
      </form>
    </div>
  </div>

  {{-- Grid detail --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Kolom kiri --}}
    <div class="md:col-span-2 space-y-4">
      {{-- Profil --}}
      <div class="rounded-2xl border bg-white p-4 md:p-5">
        <h2 class="font-semibold mb-3 text-[color:var(--brand-maroon,#7b1d2e)]">Profil</h2>
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
      </div>

      {{-- Kontak & Situs --}}
      <div class="rounded-2xl border bg-white p-4 md:p-5">
        <h2 class="font-semibold mb-3 text-[color:var(--brand-maroon,#7b1d2e)]">Kontak & Situs</h2>
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
                 class="text-[color:var(--brand-maroon,#7b1d2e)] underline underline-offset-4">
                {{ $company->website }}
              </a>
            @else
              —
            @endif
          </dd>
        </dl>
      </div>

      {{-- Alamat --}}
      <div class="rounded-2xl border bg-white p-4 md:p-5">
        <h2 class="font-semibold mb-3 text-[color:var(--brand-maroon,#7b1d2e)]">Alamat</h2>
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
      </div>
    </div>

    {{-- Kolom kanan (meta) --}}
    <div class="space-y-4">
      <div class="rounded-2xl border bg-white p-4 md:p-5">
        <h2 class="font-semibold mb-3 text-[color:var(--brand-maroon,#7b1d2e)]">Pengaturan</h2>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-coal-500">Zona Waktu</dt>
          <dd class="col-span-2">{{ $company->timezone ?: 'Asia/Jakarta' }}</dd>

          <dt class="text-coal-500">Mata Uang</dt>
          <dd class="col-span-2">{{ $company->currency ?: 'IDR' }}</dd>
        </dl>
      </div>

      <div class="rounded-2xl border bg-white p-4 md:p-5">
        <h2 class="font-semibold mb-3 text-[color:var(--brand-maroon,#7b1d2e)]">Metadata</h2>
        <dl class="grid grid-cols-3 gap-2 text-sm">
          <dt class="text-coal-500">Dibuat</dt>
          <dd class="col-span-2">{{ optional($company->created_at)->format('d M Y H:i') ?? '—' }}</dd>

          <dt class="text-coal-500">Diubah</dt>
          <dd class="col-span-2">{{ optional($company->updated_at)->format('d M Y H:i') ?? '—' }}</dd>
        </dl>
      </div>
    </div>
  </div>
</div>
@endsection
