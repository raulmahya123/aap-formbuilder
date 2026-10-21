@props(['value'])

<x-ui.form-label :value="$value ?? null" {{ $attributes }}>{{ $slot }}</x-ui.form-label>
