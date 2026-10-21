@props(['variant' => 'secondary', 'icon' => null])
<button {{ $attributes->class(['ui-button', 'ui-button--'.$variant]) }}>@if($icon)<x-ui.lucide :name="$icon" />@endif{{ $slot }}</button>
