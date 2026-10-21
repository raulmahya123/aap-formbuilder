@props(['name', 'options' => [], 'placeholder' => 'Semua', 'value' => null, 'label' => null])
@php $val = $value ?? (request()->query->has($name) ? request($name, '') : ($name === 'site_id' ? session('active_site_id', '') : '')); @endphp
@if($label)<label class="sr-only" for="filter-{{ $name }}">{{ $label }}</label>@endif
<select
  id="filter-{{ $name }}"
  name="{{ $name }}"
  {{ $attributes->class(['ui-table-filter']) }}
  @change="$el.closest('form')?.requestSubmit()"
  x-data
>
  <option value="">{{ $placeholder }}</option>
  @foreach($options as $key => $text)
    <option value="{{ $key }}" @selected((string)$key === (string)$val)>{{ $text }}</option>
  @endforeach
</select>
