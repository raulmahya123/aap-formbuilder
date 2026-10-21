@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-[#111111]']) }}>
    {{ $value ?? $slot }}
</label>
