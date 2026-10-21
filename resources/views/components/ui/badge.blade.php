@props(['variant' => 'neutral'])
<span {{ $attributes->class(['ui-status', 'ui-status--'.$variant]) }}>{{ $slot }}</span>
