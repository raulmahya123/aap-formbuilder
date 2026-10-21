@props(['variant' => 'secondary', 'icon' => null])
<a {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }}>@if($icon)<x-ui.lucide :name="$icon" />@endif{{ $slot }}</a>
