@extends('layouts.app')
@section('title','New Indicator')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-maroon-700">New Indicator</h1>

<form action="{{ route('admin.indicators.store') }}" method="post"
      class="space-y-5 max-w-2xl bg-white rounded-xl border border-gray-200 shadow-sm p-6">
  @csrf

  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-1">Group</label>
    <select name="indicator_group_id"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
            required>
      @foreach($groups as $g)
        <option value="{{ $g->id }}">{{ $g->name }}</option>
      @endforeach
    </select>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
      <input name="name"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             required>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Code</label>
      <input name="code"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             placeholder="LTI" required>
    </div>
  </div>

  <div class="grid grid-cols-4 gap-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Data Type</label>
      <select name="data_type"
              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
        <option>int</option>
        <option selected>decimal</option>
        <option>currency</option>
        <option>rate</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Agg</label>
      <select name="agg"
              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
        <option selected>sum</option>
        <option>avg</option>
        <option>max</option>
        <option>min</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Unit</label>
      <input name="unit"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             placeholder="jam/kasus/Rp">
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Order</label>
      <input type="number" name="order_index" value="0"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
    </div>
  </div>

  <div class="p-4 rounded-lg border border-gray-200 bg-gray-50">
    <label class="flex items-center gap-2 text-gray-700 font-medium">
      <input type="checkbox" name="is_derived" value="1" id="chkDerived"
             class="h-4 w-4 text-maroon-600 border-gray-300 rounded focus:ring-maroon-500">
      <span>Derived (pakai formula)</span>
    </label>
    <div class="mt-2">
      <label class="block text-sm text-gray-700 mb-1">
        Formula (gunakan CODE indikator, mis:
        <code class="font-mono text-sm text-maroon-700">LTI / MAN_HOURS * 1e6</code>)
      </label>
      <textarea name="formula" rows="2"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"></textarea>
    </div>
  </div>

  <div class="flex gap-3">
    <button class="px-5 py-2.5 rounded-lg bg-maroon-600 hover:bg-maroon-700 text-white shadow">
      Save
    </button>
    <a href="{{ route('admin.indicators.index') }}"
       class="px-5 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700">
      Cancel
    </a>
  </div>
</form>
@endsection
