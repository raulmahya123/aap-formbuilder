<x-ui.link variant="secondary" {{ $attributes }}>
    <x-ui.icon name="arrow-left" class="h-4 w-4 shrink-0" />
    <span>{{ $slot->isEmpty() ? 'Kembali' : $slot }}</span>
</x-ui.link>
