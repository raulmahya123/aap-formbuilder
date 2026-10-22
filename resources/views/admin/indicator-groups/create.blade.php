@extends('layouts.app')
@section('title','New Group')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>New Group</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan grup indikator.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

@if ($errors->any())
  <div class="mb-5 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800">
    <ul class="list-disc ml-5 space-y-1 text-sm">
      @foreach ($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form action="{{ route('admin.groups.store') }}" method="post"
      class="w-full min-w-0 ui-card space-y-5 bg-white rounded-lg shadow-none border border-gray-200">
  @csrf

  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
    <input name="name" value="{{ old('name') }}"
           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:outline-none"
           required>
  </div>

  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-1">Code</label>
    <input name="code" value="{{ old('code') }}" placeholder="LAGGING"
           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:outline-none"
           required>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Order</label>
      <input type="number" name="order_index" value="{{ old('order_index',0) }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:outline-none">
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active',1) ? 'checked' : '' }}
             class="h-4 w-4 text-maroon-600 border-gray-300 rounded-lg focus:ring-maroon-500">
      <span class="text-sm text-gray-700">Active</span>
    </div>
  </div>

  <div class="flex gap-3">
    <x-ui.button variant="primary" class="px-5 py-3 rounded-lg shadow-none">
      Save
    </x-ui.button>
    <x-ui.link variant="secondary" href="{{ route('admin.groups.index') }}"
       class="px-5 py-3 rounded-lg border">
      Cancel
    </x-ui.link>
  </div>
</form>
@endsection
