@props(['paginator' => null])
<x-ui.data-table>
  <div class="ui-table-scroll" role="region" aria-label="Tabel data" tabindex="0">
    <table {{ $attributes->class(['ui-table']) }}>{{ $slot }}</table>
  </div>
  <x-slot:pagination>
    @if($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
      <x-ui.table-pagination :paginator="$paginator" />
    @endif
  </x-slot:pagination>
</x-ui.data-table>
