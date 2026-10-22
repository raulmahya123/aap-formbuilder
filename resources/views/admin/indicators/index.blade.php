@extends('layouts.app')
@section('title','Indicators')
@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Indicators</x-ui.page-title>
<x-ui.page-description>Kelola indikator dan aturan perhitungannya.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link data-modal-form icon="plus" variant="primary" href="{{ route('admin.indicators.create') }}"
     class="px-4 py-2 rounded-lg shadow-none">Tambah Indikator
  </x-ui.link></x-ui.page-actions>
</x-ui.page-header>
<x-ui.table-toolbar><x-ui.table-search name="q" placeholder="Cari indikator…" aria-label="Cari indikator" /><x-ui.table-filter name="indicator_group_id" :options="$groups->pluck('name','id')" label="Grup indikator" placeholder="Semua grup" /><x-ui.link variant="secondary" :href="url()->current()">Reset</x-ui.link></x-ui.table-toolbar>
    <x-ui.panel :padded="false" class="overflow-hidden bg-white rounded-lg border border-gray-200 shadow-none">
      <x-ui.table :paginator="$indicators" class="min-w-full divide-y divide-gray-200">
        <thead class="bg-maroon-700 text-white">
          <tr>
            <th class="px-3 py-3 text-left text-sm font-semibold">Urutan</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Nama</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Kode</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Tipe</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Agg</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Unit</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Turunan</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Threshold</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Bobot</th>
            <th class="px-3 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($indicators as $i)
            <tr class="hover:bg-gray-50 transition">
              <td class="px-3 py-2 text-gray-700 text-right">{{ $i->order_index }}</td>
              <td class="px-3 py-2 font-medium"><x-ui.truncated-text :value="$i->name" /><div class="text-xs text-zinc-600">{{ $i->group?->name }}</div></td>
              <td class="px-3 py-2 font-mono text-gray-600">{{ $i->code }}</td>
              <td class="px-3 py-2">{{ $i->data_type }}</td>
              <td class="px-3 py-2">{{ strtoupper($i->agg) }}</td>
              <td class="px-3 py-2">{{ $i->unit }}</td>
              <td class="px-3 py-2">
                @if($i->is_derived)
                  <x-ui.badge variant="success" class="px-2 py-1 text-xs font-semibold rounded-full">Ya</x-ui.badge>
                @else
                  <x-ui.badge variant="neutral" class="px-2 py-1 text-xs font-semibold rounded-full">Tidak</x-ui.badge>
                @endif
              </td>
              {{-- Threshold --}}
              <td class="px-3 py-2">
                <span class="font-mono text-gray-700">
                  {{ $i->threshold !== null && $i->threshold !== '' ? $i->threshold : 0 }}
                </span>
              </td>
              <td class="px-3 py-2">
                <span class="font-mono text-gray-700">
                  {{ $i->weight !== null ? rtrim(rtrim(number_format((float) $i->weight, 2, '.', ''), '0'), '.') . '%' : '-' }}
                </span>
              </td>
              <td class="ui-action-cell px-3 py-2 text-right space-x-2"><x-ui.table-actions>
                <x-ui.icon-button data-modal-form ability="update" :subject="$i" action="edit" label="Edit" href="{{ route('admin.indicators.edit',$i) }}" />
                <form action="{{ route('admin.indicators.destroy',$i) }}" method="post" class="inline"
                      data-confirm="Delete this indicator?">
                  @csrf @method('delete')
                  <x-ui.icon-button ability="delete" :subject="$i" action="delete" label="Hapus" type="submit" />
                </form>
              </x-ui.table-actions></td>
            </tr>
          @empty
<x-ui.table-empty-state colspan="10"></x-ui.table-empty-state>
@endforelse
        </tbody>
      </x-ui.table>
    </x-ui.panel>

@endsection