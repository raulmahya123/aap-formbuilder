@extends('layouts.app')
@section('title','Indicators')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Indicators</h1>
  <a href="{{ route('admin.indicators.create') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">New Indicator</a>
</div>

@foreach($groups as $g)
  <div class="mb-6">
    <h2 class="font-semibold text-lg mb-2">{{ $g->name }}</h2>
    <div class="bg-white rounded-xl border">
      <table class="min-w-full divide-y">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">Order</th>
            <th class="px-3 py-2 text-left">Name</th>
            <th class="px-3 py-2 text-left">Code</th>
            <th class="px-3 py-2 text-left">Type</th>
            <th class="px-3 py-2 text-left">Agg</th>
            <th class="px-3 py-2 text-left">Unit</th>
            <th class="px-3 py-2 text-left">Derived</th>
            <th class="px-3 py-2"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @foreach($g->indicators as $i)
          <tr>
            <td class="px-3 py-2">{{ $i->order_index }}</td>
            <td class="px-3 py-2">{{ $i->name }}</td>
            <td class="px-3 py-2 font-mono">{{ $i->code }}</td>
            <td class="px-3 py-2">{{ $i->data_type }}</td>
            <td class="px-3 py-2">{{ strtoupper($i->agg) }}</td>
            <td class="px-3 py-2">{{ $i->unit }}</td>
            <td class="px-3 py-2">{{ $i->is_derived ? 'Yes' : 'No' }}</td>
            <td class="px-3 py-2 text-right">
              <a class="text-indigo-600" href="{{ route('admin.indicators.edit',$i) }}">Edit</a>
              <form action="{{ route('admin.indicators.destroy',$i) }}" method="post" class="inline"
                    onsubmit="return confirm('Delete this indicator?')">
                @csrf @method('delete')
                <button class="ml-3 text-red-600">Delete</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endforeach
@endsection
