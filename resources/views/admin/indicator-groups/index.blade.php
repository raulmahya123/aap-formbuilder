@extends('layouts.app')
@section('title','Indicator Groups')

@section('content')
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-maroon-700">Indicator Groups</h1>
  <a href="{{ route('admin.groups.create') }}"
     class="px-4 py-2 rounded-lg bg-maroon-600 hover:bg-maroon-700 text-white shadow">
     + New Group
  </a>
</div>

<div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm bg-white">
  <table class="min-w-full divide-y divide-gray-200">
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
            <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Yes</span>
          @else
            <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">No</span>
          @endif
        </td>
        <td class="px-4 py-3 text-right space-x-2">
          {{-- Tombol Edit pakai maroon terang --}}
          <a href="{{ route('admin.groups.edit',$g) }}"
             class="inline-block px-3 py-1 rounded-lg bg-maroon-500 text-white hover:bg-maroon-600 shadow-sm">
             Edit
          </a>
          {{-- Tombol Delete pakai maroon gelap --}}
          <form action="{{ route('admin.groups.destroy',$g) }}" method="post" class="inline"
                onsubmit="return confirm('Delete this group?')">
            @csrf @method('delete')
            <button class="inline-block px-3 py-1 rounded-lg bg-maroon-700 text-white hover:bg-maroon-800 shadow-sm">
              Delete
            </button>
          </form>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="px-6 py-6 text-center text-gray-500">No groups yet.</td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
