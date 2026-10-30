@props(['name'])
@php
    $file = strtolower(trim((string) $name));
    $file = preg_match('/^[a-z0-9-]+$/', $file) ? $file : '';
    $path = $file ? resource_path('icons/lucide/'.$file.'.svg') : '';
    if (!$path || !is_file($path)) $path = resource_path('icons/lucide/circle-help.svg');
    $svg = file_get_contents($path);
    $paths = preg_replace('/^<svg\b[^>]*>|<\/svg>\s*$/s', '', trim($svg));
@endphp
<svg {{ $attributes->class(['ui-lucide']) }} xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths !!}</svg>
