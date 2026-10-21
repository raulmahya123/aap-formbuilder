@props(['fields' => 4])
<div {{ $attributes->class(['ui-skeleton-form']) }} role="status" aria-busy="true" aria-label="Memuat formulir"><span class="sr-only">Memuat formulir…</span>@for($i = 0; $i < $fields; $i++)<div><div class="ui-skeleton-bar ui-skeleton-short"></div><div class="ui-skeleton-bar ui-skeleton-input"></div></div>@endfor</div>
