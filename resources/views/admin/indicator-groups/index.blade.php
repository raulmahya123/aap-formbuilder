@extends('layouts.app')
@section('title','Indicator Groups')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Indicator Groups</h1>
  <a href="{{ route('admin.groups.create') }}" class="px-4 py-2 rounded bg-indigo-600 text-white">New Group</a>
</div>

<div class="bg-white rounded-xl border">
  <table class="min-w-full divide-y">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left">Order</th>
        <th class="px-4 py-2 text-left">Name</th>
        <th class="px-4 py-2 text-left">Code</th>
        <th class="px-4 py-2">Active</th>
        <th class="px-4 py-2"></th>
      </tr>
    </thead>
    <tbody class="divide-y">
      @foreach($groups as $g)
      <tr>
        <td class="px-4 py-2">{{ $g->order_index }}</td>
        <td class="px-4 py-2">{{ $g->name }}</td>
        <td class="px-4 py-2 font-mono">{{ $g->code }}</td>
        <td class="px-4 py-2">{{ $g->is_active ? 'Yes' : 'No' }}</td>
        <td class="px-4 py-2 text-right">
          <a class="text-indigo-600" href="{{ route('admin.groups.edit',$g) }}">Edit</a>
          <form action="{{ route('admin.groups.destroy',$g) }}" method="post" class="inline"
                onsubmit="return confirm('Delete this group?')">
            @csrf @method('delete')
            <button class="ml-3 text-red-600">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
