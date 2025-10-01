@extends('layouts.app')
@section('title','Indicators')

@section('content')
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-maroon-800">Indicators</h1>
  <a href="{{ route('admin.indicators.create') }}"
     class="px-4 py-2 rounded-lg bg-maroon-600 hover:bg-maroon-700 text-white shadow">
     + New Indicator
  </a>
</div>

@foreach($groups as $g)
  <div class="mb-8">
    <h2 class="font-semibold text-lg mb-3 text-gray-800">{{ $g->name }}</h2>
    <div class="overflow-hidden bg-white rounded-xl border border-gray-200 shadow-sm">
      <table class="min-w-full divide-y divide-gray-200">
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
                  <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Yes</span>
                @else
                  <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">No</span>
                @endif
              </td>

              {{-- Threshold --}}
              <td class="px-3 py-2">
                <span class="font-mono text-gray-700">
                  {{ $i->threshold !== null && $i->threshold !== '' ? $i->threshold : 0 }}
                </span>
              </td>

              <td class="px-3 py-2 text-right space-x-2">
                <a href="{{ route('admin.indicators.edit',$i) }}"
                   class="inline-block px-3 py-1 rounded-lg bg-maroon-500 text-white hover:bg-maroon-600 shadow-sm">
                   Edit
                </a>
                <form action="{{ route('admin.indicators.destroy',$i) }}" method="post" class="inline"
                      onsubmit="return confirm('Delete this indicator?')">
                  @csrf @method('delete')
                  <button class="inline-block px-3 py-1 rounded-lg bg-maroon-700 text-white hover:bg-maroon-800 shadow-sm">
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-6 py-6 text-center text-gray-500">No indicators yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endforeach
@endsection
