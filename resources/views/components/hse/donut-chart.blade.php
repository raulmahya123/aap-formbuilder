@props(['chart', 'title' => null])
@php
    $labels = $chart['labels'] ?? [];
    $data = array_map(fn ($v) => (float) $v, $chart['data'] ?? []);
    $total = array_sum($data);
    $palette = ['#1646A0', '#16A34A', '#F59E0B', '#DC2626', '#7C3AED', '#0EA5E9', '#64748B', '#84CC16'];
    $radius = 60;
    $circumference = 2 * M_PI * $radius;
    $offset = 0;
    $segments = [];
    foreach ($data as $i => $value) {
        $length = $total > 0 ? ($value / $total) * $circumference : 0;
        $segments[] = [
            'label' => $labels[$i] ?? '—',
            'value' => $value,
            'color' => $palette[$i % count($palette)],
            'length' => $length,
            'offset' => $offset,
            'percent' => $total > 0 ? ($value / $total) * 100 : 0,
        ];
        $offset += $length;
    }
@endphp
<div {{ $attributes->class(['w-full']) }}>
    @if($total <= 0)
        <div class="flex h-40 flex-col items-center justify-center gap-1 text-center">
            <p class="text-sm font-medium text-[#475569]">Belum ada data pada periode ini</p>
            <p class="text-xs text-[#94A3B8]">Data akan tampil setelah tersedia.</p>
        </div>
    @else
        <div class="flex flex-col items-center gap-4 sm:flex-row">
            <svg viewBox="0 0 160 160" class="h-40 w-40 shrink-0" role="img" aria-label="{{ $title ?? 'Distribusi data' }}">
                <circle cx="80" cy="80" r="{{ $radius }}" fill="none" stroke="#F1F5F9" stroke-width="22" />
                @foreach($segments as $segment)
                    @if($segment['value'] > 0)
                        <circle cx="80" cy="80" r="{{ $radius }}" fill="none"
                                stroke="{{ $segment['color'] }}" stroke-width="22"
                                stroke-dasharray="{{ round($segment['length'], 2) }} {{ round($circumference - $segment['length'], 2) }}"
                                stroke-dashoffset="{{ round(-$segment['offset'], 2) }}"
                                transform="rotate(-90 80 80)">
                            <title>{{ $segment['label'] }}: {{ number_format($segment['value'], 0, ',', '.') }}</title>
                        </circle>
                    @endif
                @endforeach
                <text x="80" y="76" text-anchor="middle" font-size="20" font-weight="700" fill="#0F172A">{{ number_format($total, 0, ',', '.') }}</text>
                <text x="80" y="93" text-anchor="middle" font-size="9" fill="#64748B">Total</text>
            </svg>
            <ul class="grid w-full gap-1.5">
                @foreach($segments as $segment)
                    <li class="flex items-center justify-between gap-3 text-xs">
                        <span class="flex min-w-0 items-center gap-2 text-[#475569]">
                            <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full" style="background:{{ $segment['color'] }}"></span>
                            <span class="truncate">{{ $segment['label'] }}</span>
                        </span>
                        <span class="shrink-0 font-semibold text-[#0F172A]">{{ number_format($segment['value'], 0, ',', '.') }} <span class="font-normal text-[#94A3B8]">({{ number_format($segment['percent'], 1, ',', '.') }}%)</span></span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
