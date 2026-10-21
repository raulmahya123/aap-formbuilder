@props(['value'])

<label {{ $attributes->merge(['class' => 'ui-label block text-[#18181B]']) }}>
    {{ $value ?? $slot }}
</label>
