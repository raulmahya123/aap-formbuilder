@props(['name' => 'search', 'placeholder' => 'Cari...', 'value' => null])
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
    @input.debounce.400ms="$el.closest('form')?.requestSubmit()"
  >
  <button
    type="button"
    class="ui-table-search__clear"
    x-show="q"
    x-cloak
    @click="q=''; $nextTick(() => $el.closest('form')?.requestSubmit())"
    aria-label="Hapus pencarian"
  ><x-ui.lucide name="x" /></button>
</div>
