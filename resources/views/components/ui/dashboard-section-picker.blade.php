@props(['items'])
<section class="space-y-4" x-data="{ active: null }" aria-label="Pilih bagian dashboard">
    <div class="grid gap-3 sm:grid-cols-2 {{ count($items) === 4 ? 'xl:grid-cols-4' : (count($items) === 2 ? 'xl:grid-cols-2' : 'xl:grid-cols-3') }}">
        @foreach($items as $item)
            <button
                type="button"
                class="group flex min-h-[112px] w-full items-start justify-between gap-4 rounded-lg border border-zinc-300 bg-white p-4 text-left transition hover:border-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700"
                :class="active === '{{ $item['key'] }}' ? 'border-blue-700 ring-1 ring-blue-700' : ''"
                :aria-pressed="active === '{{ $item['key'] }}'"
                @click="active = '{{ $item['key'] }}'"
            >
                <span class="flex min-w-0 items-start gap-3">
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700 group-hover:bg-blue-50 group-hover:text-blue-700">
                        <x-ui.lucide :name="$item['icon']" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-zinc-900">{{ $item['title'] }}</span>
                        <span class="mt-1 block text-xs leading-5 text-zinc-600">{{ $item['description'] }}</span>
                    </span>
                </span>
                <span class="shrink-0 rounded-md bg-zinc-100 px-2 py-1 text-sm font-semibold tabular-nums text-zinc-900">{{ $item['count'] }}</span>
            </button>
        @endforeach
    </div>

    {{ $slot }}
</section>
