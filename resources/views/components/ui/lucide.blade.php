@props(['name'])
@php
    $icons = ['clipboard-list', 'save', 'shield', 'paperclip', 'wrench', 'chart-no-axes-combined', 'map-pin', 'calendar-days', 'file-text', 'folder-open', 'tag', 'truck', 'mountain', 'droplet', 'lock-keyhole', 'grip-vertical', 'heart', 'thumbs-up', 'eye', 'pencil', 'trash-2', 'users', 'circle-check', 'ban', 'download', 'upload', 'key-round', 'ellipsis', 'copy', 'user-round-plus', 'history', 'plus', 'x', 'search', 'chevron-left', 'chevron-right', 'filter', 'rotate-ccw', 'alert-triangle', 'arrow-up-down', 'loader-2'];
    $icons = array_merge($icons, ['printer', 'file-down']);
    $file = in_array($name, $icons, true) ? $name : 'ellipsis';
    $svg = file_get_contents(resource_path('icons/lucide/'.$file.'.svg'));
    $paths = preg_replace('/^<svg\b[^>]*>|<\/svg>\s*$/s', '', trim($svg));
@endphp
<svg {{ $attributes->class(['ui-lucide']) }} xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths !!}</svg>
