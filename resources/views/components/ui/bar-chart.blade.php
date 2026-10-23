@props(['series', 'label' => 'Jumlah data per modul'])
@php
    $series = is_array($series) ? $series : $series->all();
    $max = max(1, ...array_map(fn ($value) => (float) $value, array_values($series) ?: [1]));
@endphp
@if(empty($series))
    <div class="flex h-32 flex-col items-center justify-center gap-1 text-center">
        <p class="text-sm font-medium text-[#475569]">Belum ada data pada periode ini</p>
        <p class="text-xs text-[#94A3B8]">Data akan tampil setelah tersedia.</p>
    </div>
@else
<div {{ $attributes->class(['ui-bar-chart']) }} role="img" aria-label="{{ $label }}: {{ collect($series)->map(fn ($value, $key) => $key.': '.$value)->implode(', ') }}">
    @foreach($series as $name => $value)<div class="ui-bar-chart__row"><span>{{ $name }}</span><div class="ui-bar-chart__track"><div style="width:{{ max(0, (float) $value) / $max * 100 }}%"></div></div><strong>{{ number_format((float) $value, 0, ',', '.') }}</strong></div>@endforeach
</div>
@endif
