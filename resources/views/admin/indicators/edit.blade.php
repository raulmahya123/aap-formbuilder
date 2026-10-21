@extends('layouts.app')
@section('title','Edit Indicator')

@section('content')
<x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Edit Indicator</x-ui.page-title>
<x-ui.page-description>Perbarui informasi indikator.</x-ui.page-description></div>
  <x-ui.page-actions></x-ui.page-actions>
</x-ui.page-header>

<form action="{{ route('admin.indicators.update', $indicator) }}" method="post"
      class="ui-card space-y-5 max-w-2xl bg-white rounded-lg border border-gray-200 shadow-none">
  @csrf
  @method('put')

  <div>
    <label class="block text-sm font-semibold text-gray-700 mb-1">Group</label>
    <select name="indicator_group_id"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
            required>
      @foreach($groups as $g)
        <option value="{{ $g->id }}" @selected(old('indicator_group_id', $indicator->indicator_group_id) == $g->id)>
          {{ $g->name }}
        </option>
      @endforeach
    </select>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
      <input name="name" value="{{ old('name', $indicator->name) }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             required>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Code</label>
      <input name="code" value="{{ old('code', $indicator->code) }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             placeholder="LTI" required>
    </div>
  </div>

  <div class="grid grid-cols-4 gap-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Data Type</label>
      <select name="data_type"
              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
        @php $dataTypes = ['int','decimal','currency','rate']; @endphp
        @foreach($dataTypes as $dt)
          <option value="{{ $dt }}" @selected(old('data_type', $indicator->data_type ?? 'decimal') === $dt)>{{ $dt }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Agg</label>
      <select name="agg"
              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
        @php $aggs = ['sum','avg','max','min']; @endphp
        @foreach($aggs as $agg)
          <option value="{{ $agg }}" @selected(old('agg', $indicator->agg ?? 'sum') === $agg)>{{ $agg }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Threshold</label>
      <input type="text" name="threshold"
             value="{{ old('threshold', $indicator->threshold ?? '') }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             placeholder="mis: 95.5 / HIGH / OK">
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Bobot (%)</label>
      <input type="number" step="0.01" name="weight"
             value="{{ old('weight', $indicator->weight) }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             placeholder="20">
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Unit</label>
      <input name="unit" value="{{ old('unit', $indicator->unit) }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             placeholder="jam/kasus/Rp">
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Order</label>
      <input type="number" name="order_index"
             value="{{ old('order_index', $indicator->order_index ?? 0) }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">
    </div>
  </div>

  <div class="p-4 rounded-lg border border-gray-200 bg-gray-50">
    <label class="flex items-center gap-2 text-gray-700 font-medium">
      {{-- pastikan selalu kirim 0 saat uncheck --}}
      <input type="hidden" name="is_derived" value="0">
      <input type="checkbox" name="is_derived" value="1" id="chkDerived"
             class="h-4 w-4 text-maroon-600 border-gray-300 rounded-lg focus:ring-maroon-500"
             @checked(old('is_derived', $indicator->is_derived))>
      <span>Derived (pakai formula)</span>
    </label>
    <div class="mt-2" id="formulaWrap">
      <label class="block text-sm text-gray-700 mb-1">
        Formula (gunakan CODE indikator, mis:
        <code class="font-mono text-sm text-maroon-700">LTI / MAN_HOURS * 1e6</code>)
      </label>
      <textarea name="formula" rows="2"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400">{{ old('formula', $indicator->formula) }}</textarea>
    </div>
  </div>

  <div class="flex gap-3">
    <x-ui.button variant="primary" class="px-5 py-3 rounded-lg shadow-none">
      Update
    </x-ui.button>
    <x-ui.link variant="secondary" href="{{ route('admin.indicators.index') }}"
       class="px-5 py-3 rounded-lg border">
      Cancel
    </x-ui.link>
  </div>
</form>

@push('scripts')
<script>
(function () {
  const chk = document.getElementById('chkDerived');
  const wrap = document.getElementById('formulaWrap');
  const toggle = () => wrap.style.display = chk.checked ? '' : 'none';
  toggle();
  chk.addEventListener('change', toggle);
})();
</script>
@endpush
@endsection
