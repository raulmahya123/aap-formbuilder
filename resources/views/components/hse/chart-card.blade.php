@props(['title', 'subtitle' => null])
<section {{ $attributes->class(['flex h-full flex-col rounded-lg border border-[var(--border)] bg-white']) }}>
    <header class="flex items-start justify-between gap-3 border-b border-[var(--border)] px-4 py-3">
        <div>
            <h2 class="text-sm font-semibold text-[var(--primary-dark)]">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-0.5 text-xs text-[var(--gray)]">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
        @endisset
    </header>
    <div class="flex-1 p-4">{{ $slot }}</div>
    @isset($footer)
        <div class="ui-pagination-footer">{{ $footer }}</div>
    @endisset
</section>
