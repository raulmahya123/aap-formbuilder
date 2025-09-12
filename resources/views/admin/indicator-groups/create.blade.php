@extends('layouts.app')
@section('title','New Group')

@section('content')
<form action="{{ route('admin.groups.store') }}" method="post" class="space-y-4 max-w-xl">
  @csrf
  <div>
    <label class="block text-sm font-medium mb-1">Name</label>
    <input name="name" class="w-full border rounded px-3 py-2" required>
  </div>
  <div>
    <label class="block text-sm font-medium mb-1">Code</label>
    <input name="code" class="w-full border rounded px-3 py-2" placeholder="LAGGING" required>
  </div>
  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Order</label>
      <input type="number" name="order_index" value="0" class="w-full border rounded px-3 py-2">
    </div>
    <div class="flex items-end gap-2">
      <input type="checkbox" name="is_active" value="1" checked>
      <span>Active</span>
    </div>
  </div>
  <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
</form>
@endsection
