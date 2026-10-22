{{-- resources/views/admin/companies/index.blade.php --}}
@extends('layouts.app')
@section('title','Companies')
@section('content')
<x-ui.page class="max-w-7xl mx-auto">
  <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Companies</x-ui.page-title>
<x-ui.page-description>Kelola data dan profil perusahaan.</x-ui.page-description></div>
  <x-ui.page-actions><div class="flex items-center gap-2">
      <x-ui.link data-modal-form icon="plus" variant="primary" href="{{ route('admin.companies.create') }}"
         class="px-4 py-2 rounded-lg hover:brightness-105">Tambah
      </x-ui.link>
    </div></x-ui.page-actions>
</x-ui.page-header>
<x-ui.table-toolbar><x-ui.table-search name="q" placeholder="Cari nama atau kode company…" aria-label="Cari company" /><x-ui.table-filter name="status" :options="['active'=>'Aktif','inactive'=>'Nonaktif','archived'=>'Arsip']" label="Status" placeholder="Semua status" /><x-ui.link variant="secondary" :href="route('admin.companies.index')">Reset</x-ui.link></x-ui.table-toolbar>
  <x-ui.panel :padded="false" class="rounded-lg border bg-white overflow-hidden">
    <div class="overflow-x-auto">
      <x-ui.table :paginator="$companies" class="min-w-full text-sm">
        <thead class="text-coal-700 bg-[color:var(--brand-maroon,#713F12)]/5">
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
                  <img src="{{ $company->logo_url }}?h=80" alt="logo" class="h-10 w-10 rounded-lg object-cover">
                @else
                  <div class="h-10 w-10 rounded-lg bg-[color:var(--brand-maroon,#713F12)]/10 grid place-items-center text-[color:var(--brand-maroon,#713F12)]">—</div>
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
                    'inactive' => 'ui-status ui-status--warning',
                    'archived' => 'bg-coal-100 text-coal-700 border-coal-300',
                    default    => 'ui-status ui-status--success',
                  };
                @endphp
                <span class="px-2 py-1 text-xs rounded border {{ $badge }}">{{ ucfirst($company->status ?? 'active') }}</span>
              </td>
              <td class="ui-action-cell px-4 py-3"><x-ui.table-actions>
                <div class="flex justify-end gap-2">
                  <x-ui.icon-button action="view" label="Lihat detail" href="{{ route('admin.companies.show', $company) }}" />
                  <x-ui.icon-button data-modal-form action="edit" label="Edit" href="{{ route('admin.companies.edit', $company) }}" />
                  <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" data-confirm="Hapus perusahaan ini?">
                    @csrf @method('DELETE')
                    <x-ui.icon-button action="delete" label="Hapus" type="submit" />
                  </form>
                </div>
              </x-ui.table-actions></td>
            </tr>
          @empty
<x-ui.table-empty-state colspan="8"></x-ui.table-empty-state>
@endforelse
        </tbody>
      </x-ui.table>
    </div>

  </x-ui.panel>
</x-ui.page>
@endsection