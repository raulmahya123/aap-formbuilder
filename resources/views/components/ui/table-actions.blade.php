@php($actionCount = preg_match_all('/<(?:a|button)\b/', (string) $slot))
<div {{ $attributes->class(['ui-table-actions']) }}>@if($actionCount > 3 && !str_contains((string) $slot, 'ui-action-menu'))<x-ui.action-menu>{{ $slot }}</x-ui.action-menu>@else{{ $slot }}@endif</div>
