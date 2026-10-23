@props(['chart', 'height' => 230])
@php
    $labels = $chart['labels'] ?? [];
    $series = collect($chart['series'] ?? []);
    $width = 640;
    $padL = 44;
    $padR = 16;
    $padT = 18;
    $padB = 34;
    $plotW = $width - $padL - $padR;
    $plotH = $height - $padT - $padB;
    $all = $series->flatMap(fn ($s) => $s['data'] ?? [])->map(fn ($v) => (float) $v)->all();
    $rawMax = $all ? max($all) : 0;
    $max = $rawMax > 0 ? $rawMax * 1.15 : 1;
    $count = max(1, count($labels));
    $step = $count > 1 ? $plotW / ($count - 1) : 0;
    $total = array_sum($all);
    $pointSets = $series->map(function ($s) use ($labels, $padL, $padT, $plotH, $step, $max, $count, $plotW) {
        $dots = [];
        foreach ($labels as $i => $label) {
            $v = (float) ($s['data'][$i] ?? 0);
            $x = $count > 1 ? $padL + ($i * $step) : $padL + ($plotW / 2);
            $y = $padT + $plotH - (($v / $max) * $plotH);
            $dots[] = ['x' => round($x, 1), 'y' => round($y, 1), 'v' => $v];
        }
        $points = collect($dots)->map(fn ($d) => $d['x'].','.$d['y'])->implode(' ');

        return ['name' => $s['name'] ?? '', 'color' => $s['color'] ?? '#1646A0', 'points' => $points, 'dots' => $dots];
    });
@endphp
<div {{ $attributes->class(['w-full']) }}>
    @if(empty($labels) || $total <= 0)
        <div class="flex h-40 flex-col items-center justify-center gap-1 text-center">
            <p class="text-sm font-medium text-[#475569]">Belum ada data pada periode ini</p>
            <p class="text-xs text-[#94A3B8]">Data akan tampil setelah tersedia.</p>
        </div>
    @else
        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="h-auto w-full" role="img"
             aria-label="{{ $series->map(fn ($s) => $s['name'].': '.implode(', ', $s['data'] ?? []))->implode('; ') }}">
            @for($g = 0; $g <= 4; $g++)
                @php
                    $value = $max * ($g / 4);
                    $y = $padT + $plotH - (($g / 4) * $plotH);
                @endphp
                <line x1="{{ $padL }}" y1="{{ $y }}" x2="{{ $width - $padR }}" y2="{{ $y }}" stroke="#E2E8F0" stroke-width="1" />
                <text x="{{ $padL - 8 }}" y="{{ $y + 3 }}" text-anchor="end" font-size="9" fill="#94A3B8">{{ number_format($value, $value >= 10 ? 0 : 1, ',', '.') }}</text>
            @endfor

            @foreach($labels as $i => $label)
                @php $x = $count > 1 ? $padL + ($i * $step) : $padL + ($plotW / 2); @endphp
                <text x="{{ $x }}" y="{{ $height - 10 }}" text-anchor="middle" font-size="9" fill="#64748B">{{ $label }}</text>
            @endforeach

            @foreach($pointSets as $set)
                <polyline points="{{ $set['points'] }}" fill="none" stroke="{{ $set['color'] }}" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                @foreach($set['dots'] as $dot)
                    <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="2.6" fill="#fff" stroke="{{ $set['color'] }}" stroke-width="2">
                        <title>{{ $set['name'] }}: {{ number_format($dot['v'], 0, ',', '.') }}</title>
                    </circle>
                @endforeach
            @endforeach
        </svg>

        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1">
            @foreach($series as $s)
                <span class="inline-flex items-center gap-1.5 text-xs text-[#475569]">
                    <span class="inline-block h-2.5 w-2.5 rounded-full" style="background:{{ $s['color'] ?? '#1646A0' }}"></span>
                    {{ $s['name'] ?? '' }}
                </span>
            @endforeach
        </div>
    @endif
</div>
