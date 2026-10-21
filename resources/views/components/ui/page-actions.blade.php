<div {{ $attributes->class(['ui-page-actions']) }}>@if($slot->isEmpty())@yield('actions')@else{{ $slot }}@endif</div>
