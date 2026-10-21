@props(['variant' => 'neutral', 'solid' => false])
@php $cls = $solid ? 'ui-status-badge--solid-'.$variant : 'ui-status-badge--'.$variant; @endphp
<span {{ $attributes->class(['ui-status-badge', $cls]) }}>{{ $slot }}</span>
