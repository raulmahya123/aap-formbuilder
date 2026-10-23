@props(['points', 'missing' => 0])
@php
    $lats = array_column($points, 'latitude');
    $lngs = array_column($points, 'longitude');
    $minLat = $lats ? min($lats) : 0;
    $maxLat = $lats ? max($lats) : 1;
    $minLng = $lngs ? min($lngs) : 0;
    $maxLng = $lngs ? max($lngs) : 1;
    $latSpan = max(0.0001, $maxLat - $minLat);
    $lngSpan = max(0.0001, $maxLng - $minLng);
    $width = 600;
    $height = 320;
    $pad = 36;
@endphp
<div {{ $attributes->class(['w-full']) }}>
    @if(empty($points))
        <div class="flex h-48 flex-col items-center justify-center gap-1 text-center">
            <p class="text-sm font-medium text-[#475569]">Belum ada koordinat site</p>
            <p class="text-xs text-[#94A3B8]">Lengkapi latitude dan longitude pada master site.</p>
        </div>
    @else
        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="h-auto w-full rounded bg-[#F8FAFC]" role="img" aria-label="Peta sebaran site">
            @for($i = 1; $i < 6; $i++)
                <line x1="{{ $pad }}" y1="{{ $pad + ($i * (($height - 2 * $pad) / 6)) }}" x2="{{ $width - $pad }}" y2="{{ $pad + ($i * (($height - 2 * $pad) / 6)) }}" stroke="#E2E8F0" stroke-width="1" />
                <line x1="{{ $pad + ($i * (($width - 2 * $pad) / 6)) }}" y1="{{ $pad }}" x2="{{ $pad + ($i * (($width - 2 * $pad) / 6)) }}" y2="{{ $height - $pad }}" stroke="#E2E8F0" stroke-width="1" />
            @endfor
            @foreach($points as $point)
                @php
                    $x = $pad + ((($point['longitude'] - $minLng) / $lngSpan) * ($width - 2 * $pad));
                    $y = $height - $pad - ((($point['latitude'] - $minLat) / $latSpan) * ($height - 2 * $pad));
                @endphp
                <circle cx="{{ round($x, 1) }}" cy="{{ round($y, 1) }}" r="6" fill="#1646A0" fill-opacity=".85" stroke="#fff" stroke-width="2">
                    <title>{{ $point['name'] }} ({{ $point['code'] }}): {{ $point['incidents'] }} insiden, {{ $point['open_actions'] }} open action</title>
                </circle>
                <text x="{{ round($x, 1) }}" y="{{ round($y - 10, 1) }}" text-anchor="middle" font-size="9" font-weight="600" fill="#0F172A">{{ $point['code'] ?: $point['name'] }}</text>
            @endforeach
        </svg>
        @if($missing > 0)
            <p class="mt-2 text-xs text-[#94A3B8]">{{ $missing }} site belum memiliki koordinat dan tidak ditampilkan pada peta.</p>
        @endif
    @endif
</div>
