@props(['value'])
@php
    $text = (string) $value;
    $key = strtolower($text);
    $variant = match (true) {
        str_contains($key, 'overdue'), str_contains($key, 'expired'), str_contains($key, 'critical'),
        str_contains($key, 'extreme'), str_contains($key, 'high'), str_contains($key, 'non-compliant'),
        str_contains($key, 'late'), str_contains($key, 'rejected') => 'danger',
        str_contains($key, 'closed'), str_contains($key, 'completed'), str_contains($key, 'compliant'),
        str_contains($key, 'on time'), str_contains($key, 'verified'), str_contains($key, 'low') => 'success',
        str_contains($key, 'pending'), str_contains($key, 'scheduled'), str_contains($key, 'in progress'),
        str_contains($key, 'open'), str_contains($key, 'medium'), str_contains($key, 'expiring'),
        str_contains($key, 'warning') => 'warning',
        str_contains($key, 'active'), str_contains($key, 'draft') => 'info',
        default => 'neutral',
    };
@endphp
<x-ui.status-badge :variant="$variant">{{ $text }}</x-ui.status-badge>
