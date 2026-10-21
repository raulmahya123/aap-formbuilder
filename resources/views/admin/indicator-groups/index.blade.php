@extends('layouts.app')
@section('title','Indicator Groups')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Indicator Groups</x-ui.page-title>
<x-ui.page-description>Atur pengelompokan indikator operasional.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.link icon="plus" variant="primary" href="{{ route('admin.groups.create') }}"
     class="px-4 py-2 rounded-lg shadow-none">New Group
  </x-ui.link></x-ui.page-actions>
</x-ui.page-header>

<x-ui.panel :padded="false" class="overflow-hidden rounded-lg border border-gray-200 shadow-none bg-white">
  <x-ui.table class="min-w-full divide-y divide-gray-200">
    {{-- Header pakai maroon gelap agar tegas --}}
    <thead class="bg-maroon-700 text-white">
      <tr>
        <th class="px-4 py-3 text-left text-sm font-semibold">Order</th>
        <th class="px-4 py-3 text-left text-sm font-semibold">Name</th>
        <th class="px-4 py-3 text-left text-sm font-semibold">Code</th>
        <th class="px-4 py-3 text-sm font-semibold">Active</th>
        <th class="px-4 py-3"></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse($groups as $g)
      <tr class="hover:bg-gray-50 transition">
        <td class="px-4 py-3 text-gray-700">{{ $g->order_index }}</td>
        <td class="px-4 py-3 font-medium">{{ $g->name }}</td>
        <td class="px-4 py-3 font-mono text-gray-600">{{ $g->code }}</td>
        <td class="px-4 py-3">
          @if($g->is_active)
            <x-ui.badge variant="success" class="px-2 py-1 text-xs font-semibold rounded-full">Yes</x-ui.badge>
          @else
            <x-ui.badge variant="danger" class="px-2 py-1 text-xs font-semibold rounded-full">No</x-ui.badge>
          @endif
        </td>
        <td class="ui-action-cell px-4 py-3 text-right space-x-2"><x-ui.table-actions>
          {{-- Tombol Edit pakai maroon terang --}}
          <x-ui.icon-button ability="update" :subject="$g" action="edit" label="Edit" href="{{ route('admin.groups.edit',$g) }}" />
          {{-- Tombol Delete pakai maroon gelap --}}
          <form action="{{ route('admin.groups.destroy',$g) }}" method="post" class="inline"
                data-confirm="Delete this group?">
            @csrf @method('delete')
            <x-ui.icon-button ability="delete" :subject="$g" action="delete" label="Hapus" type="submit" />
          </form>
        </x-ui.table-actions></td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="px-6 py-6 text-center text-gray-500">No groups yet.</td>
      </tr>
      @endforelse
    </tbody>
  </x-ui.table>
</x-ui.panel>
@endsection
