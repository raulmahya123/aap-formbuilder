{{-- resources/views/admin/sites/index.blade.php --}}
@extends('layouts.app')
@section('title','Sites')

@section('content')
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-[color:var(--brand-maroon,#7b1d2e)]">Sites</h1>
  <a href="{{ route('admin.sites.create') }}"
     class="px-4 py-2 rounded-xl bg-[color:var(--brand-maroon,#7b1d2e)] hover:brightness-105 text-white shadow">
     + Tambah
  </a>
</div>

{{-- Filter & Search --}}
<form class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-2"
      method="GET" action="{{ route('admin.sites.index') }}">
  <input
    class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[color:var(--brand-maroon,#7b1d2e)] focus:border-[color:var(--brand-maroon,#7b1d2e)]"
    type="text" name="q" value="{{ $q }}" placeholder="Cari name/code/deskripsi...">

  {{-- Filter perusahaan (opsional: kirimkan $companies dari controller) --}}
  <select name="company_id"
          class="border rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-[color:var(--brand-maroon,#7b1d2e)] focus:border-[color:var(--brand-maroon,#7b1d2e)]">
    <option value="">— Semua Perusahaan —</option>
    @foreach(($companies ?? []) as $c)
      <option value="{{ $c->id }}" @selected(request('company_id') == $c->id)>
        {{ $c->code }} — {{ $c->name }}
      </option>
    @endforeach
  </select>

  <div class="flex gap-2">
    <button class="px-4 py-2 rounded-lg bg-[color:var(--brand-maroon,#7b1d2e)] hover:brightness-105 text-white w-full md:w-auto">
      Terapkan
    </button>
    <a href="{{ route('admin.sites.index') }}"
       class="px-4 py-2 rounded-lg border w-full md:w-auto hover:bg-gray-50">
      Reset
    </a>
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

<div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
  <table class="w-full text-left text-sm">
    <thead class="bg-[color:var(--brand-maroon,#7b1d2e)] text-white">
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

          <td class="p-3">
            <div class="flex flex-wrap gap-2">
              {{-- Tombol Edit --}}
              <a href="{{ route('admin.sites.edit',$s) }}"
                 class="px-3 py-1 rounded-lg bg-[color:var(--brand-maroon,#7b1d2e)] text-white hover:brightness-105 shadow-sm">
                 Edit
              </a>

              {{-- Tombol Hapus --}}
              <form action="{{ route('admin.sites.destroy',$s) }}" method="POST"
                    onsubmit="return confirm('Hapus site {{ $s->code }} ?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 shadow-sm">
                  Hapus
                </button>
              </form>

              {{-- (Opsional) Switch Active Site jika route tersedia --}}
              @if(Route::has('admin.sites.switch'))
                <form action="{{ route('admin.sites.switch') }}" method="POST">
                  @csrf
                  <input type="hidden" name="site_id" value="{{ $s->id }}">
                  <button class="px-3 py-1 rounded-lg border hover:bg-gray-50">
                    Jadikan Aktif
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="p-6 text-center text-gray-500">Belum ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($sites,'links'))
  <div class="mt-4">{{ $sites->appends(request()->except('page'))->links() }}</div>
@endif
@endsection
