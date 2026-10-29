@extends('layouts.app')
@section('title','New Indicator')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>New Indicator</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan indikator.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

<form action="{{ route('admin.indicators.store') }}" method="post"
  class="w-full min-w-0 ui-card">
  @csrf

  <div>
    <label class="block">Group</label>
    <select name="indicator_group_id"
      class="w-full ui-input"
      required>
      @foreach($groups as $g)
      <option value="{{ $g->id }}">{{ $g->name }}</option>
      @endforeach
    </select>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block">Name</label>
      <input name="name"
        class="w-full ui-input"
        required>
    </div>
    <div>
      <label class="block">Code</label>
      <input name="code"
        class="w-full ui-input"
        placeholder="LTI" required>
    </div>
  </div>

  <div class="grid grid-cols-4 gap-4">
    <div>
      <label class="block">Data Type</label>
      <select name="data_type"
        class="w-full ui-input">
        <option>int</option>
        <option selected>decimal</option>
        <option>currency</option>
        <option>rate</option>
      </select>
    </div>
    <div>
      <label class="block">Agg</label>
      <select name="agg"
        class="w-full ui-input">
        <option selected>sum</option>
        <option>avg</option>
        <option>max</option>
        <option>min</option>
      </select>
    </div>
    <div>
      <label class="block">Threshold</label>
      <input type="text" name="threshold" value="{{ old('threshold', $indicator->threshold ?? '') }}"
        class="w-full ui-input"
        placeholder="mis: 95.5 / HIGH / OK">
    </div>
    <div>
      <label class="block">Bobot (%)</label>
      <input type="number" step="0.01" name="weight" value="{{ old('weight') }}"
        class="w-full ui-input"
        placeholder="20">
    </div>
    <div>
      <label class="block">Unit</label>
      <input name="unit"
        class="w-full ui-input"
        placeholder="jam/kasus/Rp">
    </div>
    <div>
      <label class="block">Order</label>
      <input type="number" name="order_index" value="0"
        class="w-full ui-input">
    </div>
  </div>

  <div class="p-4 rounded-lg border border-gray-200 bg-gray-50">
    <label class="flex items-center">
      <input type="checkbox" name="is_derived" value="1" id="chkDerived"
        class="h-4 w-4">
      <span>Derived (pakai formula)</span>
    </label>
    <div class="mt-2">
      <label class="block">
        Formula (gunakan CODE indikator, mis:
        <code class="font-mono text-sm text-maroon-700">LTI / MAN_HOURS * 1e6</code>)
      </label>
      <textarea name="formula" rows="2"
        class="w-full ui-input"></textarea>
    </div>
  </div>

  <div class="flex gap-3">
    <x-ui.button variant="primary" >
      Save
    </x-ui.button>
    <x-ui.link variant="secondary" href="{{ route('admin.indicators.index') }}"
      >
      Cancel
    </x-ui.link>
  </div>
</form>
@endsection
