{{-- resources/views/admin/companies/index.blade.php --}}
@extends('layouts.app')

@section('title','Companies')

@section('content')
<div class="max-w-7xl mx-auto p-4 md:p-6">
  <div class="flex items-center justify-between gap-3 mb-4">
    <h1 class="text-xl font-semibold text-[color:var(--brand-maroon,#7b1d2e)]">Companies</h1>
    <div class="flex items-center gap-2">
      <form method="GET" action="{{ route('admin.companies.index') }}" class="hidden md:flex items-center gap-2">
        <input
          type="text"
          name="q"
          value="{{ request('q') }}"
          placeholder="Cari nama/kode…"
          class="rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[color:var(--brand-maroon,#7b1d2e)]"
        >
        <button
          class="px-3 py-2 rounded-xl border border-[color:var(--brand-maroon,#7b1d2e)]
                     text-[color:var(--brand-maroon,#7b1d2e)] hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10">
          Cari
        </button>
      </form>
      <a href="{{ route('admin.companies.create') }}"
         class="px-4 py-2 rounded-xl text-white
                bg-[color:var(--brand-maroon,#7b1d2e)] hover:brightness-105">
        + Tambah
      </a>
    </div>
  </div>

  {{-- Mobile search --}}
  <form method="GET" action="{{ route('admin.companies.index') }}" class="md:hidden mb-4">
    <div class="flex items-center gap-2">
      <input
        type="text"
        name="q"
        value="{{ request('q') }}"
        placeholder="Cari nama/kode…"
        class="flex-1 rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[color:var(--brand-maroon,#7b1d2e)]"
      >
      <button
        class="px-3 py-2 rounded-xl border border-[color:var(--brand-maroon,#7b1d2e)]
                   text-[color:var(--brand-maroon,#7b1d2e)] hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10">
        Cari
      </button>
    </div>
  </form>

  <div class="rounded-2xl border bg-white overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-coal-700 bg-[color:var(--brand-maroon,#7b1d2e)]/5">
          <tr>
            <th class="text-left px-4 py-3 w-14">Logo</th>
            <th class="text-left px-4 py-3">Kode</th>
            <th class="text-left px-4 py-3">Nama</th>
            <th class="text-left px-4 py-3">Industri</th>
            <th class="text-left px-4 py-3">Kota</th>
            <th class="text-left px-4 py-3">Negara</th>
            <th class="text-left px-4 py-3">Status</th>
            <th class="text-right px-4 py-3 w-48">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($companies as $company)
            <tr>
              <td class="px-4 py-3">
                @if(!empty($company?->logo_url))
                  <img src="{{ $company->logo_url }}?h=80" alt="logo" class="h-10 w-10 rounded object-cover">
                @else
                  <div class="h-10 w-10 rounded bg-[color:var(--brand-maroon,#7b1d2e)]/10 grid place-items-center text-[color:var(--brand-maroon,#7b1d2e)]">—</div>
                @endif
              </td>
              <td class="px-4 py-3 font-medium">{{ $company->code }}</td>
              <td class="px-4 py-3">
                <div class="font-medium text-coal-900">{{ $company->name }}</div>
                @if($company->legal_name)
                  <div class="text-xs text-coal-500">{{ $company->legal_name }}</div>
                @endif
              </td>
              <td class="px-4 py-3">{{ $company->industry ?? '—' }}</td>
              <td class="px-4 py-3">{{ $company->city ?? '—' }}</td>
              <td class="px-4 py-3">{{ $company->country ?? 'ID' }}</td>
              <td class="px-4 py-3">
                @php
                  $badge = match($company->status){
                    'inactive' => 'bg-amber-100 text-amber-800 border-amber-300',
                    'archived' => 'bg-coal-100 text-coal-700 border-coal-300',
                    default    => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                  };
                @endphp
                <span class="px-2 py-1 text-xs rounded border {{ $badge }}">{{ ucfirst($company->status ?? 'active') }}</span>
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2">
                  <a href="{{ route('admin.companies.show', $company) }}"
                     class="px-3 py-1.5 rounded-lg border border-[color:var(--brand-maroon,#7b1d2e)]
                            text-[color:var(--brand-maroon,#7b1d2e)] hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10">
                    Detail
                  </a>
                  <a href="{{ route('admin.companies.edit', $company) }}"
                     class="px-3 py-1.5 rounded-lg border border-[color:var(--brand-maroon,#7b1d2e)]
                            text-[color:var(--brand-maroon,#7b1d2e)] hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10">
                    Edit
                  </a>
                  <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Hapus perusahaan ini?');">
                    @csrf @method('DELETE')
                    <button
                      class="px-3 py-1.5 rounded-lg border
                             border-[color:var(--brand-maroon,#7b1d2e)]/60 text-[color:var(--brand-maroon,#7b1d2e)]
                             hover:bg-[color:var(--brand-maroon,#7b1d2e)]/10">
                      Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="px-4 py-6 text-center text-coal-500">Belum ada data perusahaan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($companies, 'links'))
      <div class="px-4 py-3 border-t">
        {{ $companies->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
