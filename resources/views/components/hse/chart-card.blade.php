@props(['title', 'subtitle' => null])
<section {{ $attributes->class(['flex h-full flex-col rounded-lg border border-[#E2E8F0] bg-white']) }}>
    <header class="flex items-start justify-between gap-3 border-b border-[#F1F5F9] px-4 py-3">
        <div>
            <h2 class="text-sm font-semibold text-[#0F172A]">{{ $title }}</h2>
            @if($subtitle)
                <p class="mt-0.5 text-xs text-[#64748B]">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
        @endisset
    </header>
    <div class="flex-1 p-4">{{ $slot }}</div>
</section>
