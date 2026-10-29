@props(['variant' => 'listing'])
<section {{ $attributes->class(['ui-filter-panel', 'ui-filter-panel--'.$variant]) }} aria-label="Filter dan pencarian">
    {{ $slot }}
</section>
