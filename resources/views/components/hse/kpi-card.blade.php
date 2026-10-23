@props(['kpi'])
@php
    $value = $kpi['value'];
    $decimals = (int) ($kpi['decimals'] ?? 0);
    $unit = $kpi['unit'] ?? '';
    $formatted = $value === null || $value === ''
        ? 'N/A'
        : number_format((float) $value, $decimals, ',', '.');
    $suffix = match ($unit) {
        '%' => '%',
        'rate' => '',
        'jam', 'hari', 'record', 'orang', 'sesi' => ' '.$unit,
        default => $unit ? ' '.$unit : '',
    };
    $border = match ($kpi['status'] ?? 'neutral') {
        'success' => '#16A34A',
        'warning' => '#F59E0B',
        'danger' => '#DC2626',
        default => '#CBD5E1',
    };
    $change = $kpi['change'];
    $isGood = $kpi['is_good'] ?? null;
@endphp
<div class="relative flex h-full flex-col gap-2 rounded-lg border border-[#E2E8F0] bg-white p-4" style="border-left:4px solid {{ $border }}">
    <div class="flex items-start justify-between gap-2">
        <span class="text-xs font-semibold uppercase tracking-wide text-[#64748B]">{{ $kpi['label'] }}</span>
        <span class="cursor-help text-[#94A3B8]" title="{{ $kpi['formula'] ?? '' }}" aria-label="{{ $kpi['formula'] ?? '' }}">i</span>
    </div>
    <div class="flex items-baseline gap-1">
        <strong class="text-2xl font-bold text-[#0F172A]">{{ $formatted }}</strong>
        <span class="text-sm text-[#64748B]">{{ $suffix }}</span>
    </div>
    <div class="flex flex-wrap items-center gap-2 text-xs">
        @if($change !== null)
            <span class="inline-flex items-center gap-1 font-semibold {{ $isGood ? 'text-[#15803D]' : 'text-[#B91C1C]' }}">
                {{ $change > 0 ? '▲' : ($change < 0 ? '▼' : '■') }} {{ number_format(abs((float) $change), 1, ',', '.') }}%
            </span>
        @else
            <span class="text-[#94A3B8]">—</span>
        @endif
        <span class="text-[#94A3B8]">vs periode lalu</span>
    </div>
    <div class="mt-auto flex items-center justify-between gap-2 border-t border-[#F1F5F9] pt-2 text-xs">
        <span class="text-[#64748B]">Target: {{ $kpi['target'] === null ? '—' : number_format((float) $kpi['target'], $decimals, ',', '.').$suffix }}</span>
        @if(!empty($kpi['href']))
            <a href="{{ $kpi['href'] }}" class="font-semibold text-[#1646A0] hover:underline">Detail</a>
        @endif
    </div>
</div>
