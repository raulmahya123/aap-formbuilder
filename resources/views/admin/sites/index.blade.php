{{-- resources/views/admin/sites/index.blade.php --}}
@extends('layouts.app')
@section('title','Sites')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Sites</x-ui.page-title>
<x-ui.page-description>Kelola identitas dan status site operasional.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link icon="plus" variant="primary" href="{{ route('admin.sites.create') }}"
     class="px-4 py-2 rounded-lg hover:brightness-105 shadow-none">Tambah
  </x-ui.link></x-ui.page-actions>
</x-ui.page-header>

{{-- Filter & Search --}}
<form class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-2"
      method="GET" action="{{ route('admin.sites.index') }}">
  <input
    class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[color:var(--brand-maroon,#713F12)] focus:border-[color:var(--brand-maroon,#713F12)]"
    type="text" name="q" value="{{ $q }}" placeholder="Cari name/code/deskripsi...">

  {{-- Filter perusahaan (opsional: kirimkan $companies dari controller) --}}
  <select name="company_id"
          class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[color:var(--brand-maroon,#713F12)] focus:border-[color:var(--brand-maroon,#713F12)]">
    <option value="">— Semua Perusahaan —</option>
    @foreach(($companies ?? []) as $c)
      <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>
        {{ $c->code }} — {{ $c->name }}
      </option>
    @endforeach
  </select>

  <div class="flex gap-2">
    <x-ui.button variant="primary" class="px-4 py-2 rounded-lg hover:brightness-105 w-full md:w-auto">
      Terapkan
    </x-ui.button>
    <x-ui.link variant="secondary" href="{{ route('admin.sites.index') }}"
       class="px-4 py-2 rounded-lg border w-full md:w-auto">
      Reset
    </x-ui.link>
  </div>
</form>

@if(session('success'))
  <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-800 border border-green-200">
    {{ session('success') }}
  </div>
@endif
@if(session('ok'))
  <div class="mb-4 p-3 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200">
    {{ session('ok') }}
  </div>
@endif
@if(session('error'))
  <div class="mb-4 p-3 rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
    {{ session('error') }}
  </div>
@endif

<x-ui.panel :padded="false" class="overflow-x-auto rounded-lg border border-gray-200 shadow-none bg-white">
  <x-ui.table class="w-full text-left text-sm">
    <thead class="bg-[color:var(--brand-maroon,#713F12)] text-white">
      <tr>
        <th class="p-3 border-b">#</th>
        <th class="p-3 border-b">Code</th>
        <th class="p-3 border-b">Name</th>
        <th class="p-3 border-b">Perusahaan</th>
        <th class="p-3 border-b">Description</th>
        <th class="p-3 border-b w-48">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse($sites as $i => $s)
        <tr class="hover:bg-gray-50 transition">
          <td class="p-3">{{ $sites->firstItem() + $i }}</td>
          <td class="p-3 font-mono text-gray-700">{{ $s->code }}</td>
          <td class="p-3 font-medium">{{ $s->name }}</td>

          {{-- Kolom Perusahaan --}}
          <td class="p-3">
            @if($s->company)
              <div class="font-medium">{{ $s->company->name }}</div>
              <div class="text-xs text-gray-500">{{ $s->company->code }}</div>
            @else
              <span class="text-gray-400">—</span>
            @endif
          </td>

          <td class="p-3 text-gray-600">
            {{ \Illuminate\Support\Str::limit((string)$s->description, 80) }}
          </td>

          <td class="ui-action-cell p-3"><x-ui.table-actions>
            <div class="flex flex-nowrap gap-2">
              {{-- Tombol Edit --}}
              <x-ui.icon-button ability="update" :subject="$s" action="edit" label="Edit" href="{{ route('admin.sites.edit',$s) }}" />

              {{-- Tombol Hapus --}}
              <form action="{{ route('admin.sites.destroy',$s) }}" method="POST"
                    data-confirm="Hapus site {{ $s->code }} ?">
                @csrf @method('DELETE')
                <x-ui.icon-button ability="delete" :subject="$s" action="delete" label="Hapus" type="submit" />
              </form>

              {{-- (Opsional) Switch Active Site jika route tersedia --}}
              @if(Route::has('admin.sites.switch'))
                <form action="{{ route('admin.sites.switch') }}" method="POST">
                  @csrf
                  <input type="hidden" name="site_id" value="{{ $s->id }}">
                  <x-ui.icon-button action="activate" label="Aktifkan" type="submit" />
                </form>
              @endif
            </div>
          </x-ui.table-actions></td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="p-6 text-center text-gray-500">Belum ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </x-ui.table>
</x-ui.panel>

@if(method_exists($sites,'links'))
  <div class="mt-4">{{ $sites->appends(request()->except('page'))->links() }}</div>
@endif
@endsection
