@props(['rows' => 5])
<div {{ $attributes->class(['ui-skeleton-detail']) }} role="status" aria-busy="true" aria-label="Memuat detail"><span class="sr-only">Memuat detail…</span>@for($i = 0; $i < $rows; $i++)<div class="ui-skeleton-detail-row"><div class="ui-skeleton-bar ui-skeleton-short"></div><div class="ui-skeleton-bar"></div></div>@endfor</div>
