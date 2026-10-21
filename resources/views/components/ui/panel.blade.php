@props(['padded' => true])
<div {{ $attributes->class(['ui-panel', 'ui-panel--flush' => !$padded]) }}>{{ $slot }}</div>
