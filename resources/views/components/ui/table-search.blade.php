@props(['name' => 'search', 'placeholder' => 'Cari...', 'value' => null, 'autoSubmit' => true])
@php $val = $value ?? request($name, ''); @endphp
<div class="ui-table-search" x-data="{ q: @js($val) }">
  <span class="ui-table-search__icon"><x-ui.lucide name="search" /></span>
  <input
    {{ $attributes->merge([
      'type' => 'search',
      'name' => $name,
      'placeholder' => $placeholder,
      'autocomplete' => 'off',
      'class' => 'ui-table-search__input',
    ]) }}
    x-model="q"
    @if($autoSubmit) @input.debounce.400ms="$el.closest('form')?.requestSubmit()" @endif
  >
  <button
    type="button"
    class="ui-table-search__clear"
    x-show="q"
    x-cloak
    @if($autoSubmit) @click="q=''; $nextTick(() => $el.closest('form')?.requestSubmit())" @else @click="q=''" @endif
    aria-label="Hapus pencarian"
  ><x-ui.lucide name="x" /></button>
</div>
