@extends('layouts.app')
@section('title','Indicators')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Indicators</x-ui.page-title>
<x-ui.page-description>Kelola indikator dan aturan perhitungannya.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link icon="plus" variant="primary" href="{{ route('admin.indicators.create') }}"
     class="px-4 py-2 rounded-lg shadow-none">New Indicator
  </x-ui.link></x-ui.page-actions>
</x-ui.page-header>

@foreach($groups as $g)
  <div class="mb-8">
    <x-ui.section-title class="font-semibold text-lg mb-3 text-gray-800">{{ $g->name }}</x-ui.section-title>
    <x-ui.panel :padded="false" class="overflow-hidden bg-white rounded-lg border border-gray-200 shadow-none">
      <x-ui.table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-maroon-700 text-white">
          <tr>
            <th class="px-3 py-3 text-left text-sm font-semibold">Order</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Name</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Code</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Type</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Agg</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Unit</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Derived</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Threshold</th>
            <th class="px-3 py-3 text-left text-sm font-semibold">Bobot</th>
            <th class="px-3 py-3"></th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse($g->indicators as $i)
            <tr class="hover:bg-gray-50 transition">
              <td class="px-3 py-2 text-gray-700">{{ $i->order_index }}</td>
              <td class="px-3 py-2 font-medium">{{ $i->name }}</td>
              <td class="px-3 py-2 font-mono text-gray-600">{{ $i->code }}</td>
              <td class="px-3 py-2">{{ $i->data_type }}</td>
              <td class="px-3 py-2">{{ strtoupper($i->agg) }}</td>
              <td class="px-3 py-2">{{ $i->unit }}</td>

              <td class="px-3 py-2">
                @if($i->is_derived)
                  <x-ui.badge variant="success" class="px-2 py-1 text-xs font-semibold rounded-full">Yes</x-ui.badge>
                @else
                  <x-ui.badge variant="danger" class="px-2 py-1 text-xs font-semibold rounded-full">No</x-ui.badge>
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
                <x-ui.icon-button ability="update" :subject="$i" action="edit" label="Edit" href="{{ route('admin.indicators.edit',$i) }}" />
                <form action="{{ route('admin.indicators.destroy',$i) }}" method="post" class="inline"
                      data-confirm="Delete this indicator?">
                  @csrf @method('delete')
                  <x-ui.icon-button ability="delete" :subject="$i" action="delete" label="Hapus" type="submit" />
                </form>
              </x-ui.table-actions></td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="px-6 py-6 text-center text-gray-500">No indicators yet.</td>
            </tr>
          @endforelse
        </tbody>
      </x-ui.table>
    </x-ui.panel>
  </div>
@endforeach
@endsection
