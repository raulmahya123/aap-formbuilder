@props(['matrix'])
@php
    $cellColor = function (int $score) {
        return match (true) {
            $score >= 15 => ['#DC2626', '#fff'],
            $score >= 9 => ['#F59E0B', '#1F2937'],
            $score >= 4 => ['#FCD34D', '#1F2937'],
            default => ['#16A34A', '#fff'],
        };
    };
    $total = 0;
    foreach ($matrix as $row) {
        $total += array_sum($row);
    }
@endphp
<div {{ $attributes->class(['w-full']) }}>
    @if($total <= 0)
        <div class="flex h-40 flex-col items-center justify-center gap-1 text-center">
            <p class="text-sm font-medium text-[#475569]">Belum ada risiko aktif</p>
            <p class="text-xs text-[#94A3B8]">Matriks akan tampil setelah risk assessment tersedia.</p>
        </div>
    @else
        <div class="grid gap-1" style="grid-template-columns: 72px repeat(5, minmax(0, 1fr));">
            <div></div>
            @foreach(range(1, 5) as $consequence)
                <div class="pb-1 text-center text-[10px] font-semibold text-[#64748B]">C{{ $consequence }}</div>
            @endforeach

            @for($likelihood = 5; $likelihood >= 1; $likelihood--)
                <div class="flex items-center justify-end pr-2 text-[10px] font-semibold text-[#64748B]">L{{ $likelihood }}</div>
                @foreach(range(1, 5) as $consequence)
                    @php
                        [$background, $foreground] = $cellColor($likelihood * $consequence);
                        $count = (int) ($matrix[$likelihood][$consequence] ?? 0);
                    @endphp
                    <div class="flex aspect-square items-center justify-center rounded text-sm font-bold"
                         style="background:{{ $background }}; color:{{ $foreground }}; opacity:{{ $count > 0 ? '1' : '.35' }}"
                         title="Likelihood {{ $likelihood }} × Consequence {{ $consequence }} = {{ $likelihood * $consequence }} ({{ $count }} risiko)">
                        {{ $count }}
                    </div>
                @endforeach
            @endfor

            <div></div>
            <div class="col-span-5 pt-1 text-center text-[10px] text-[#64748B]">Consequence →</div>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-3 text-[10px] text-[#64748B]">
            <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#16A34A]"></span>Low</span>
            <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#FCD34D]"></span>Medium</span>
            <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#F59E0B]"></span>High</span>
            <span class="inline-flex items-center gap-1"><span class="h-2.5 w-2.5 rounded bg-[#DC2626]"></span>Extreme</span>
        </div>
    @endif
</div>
