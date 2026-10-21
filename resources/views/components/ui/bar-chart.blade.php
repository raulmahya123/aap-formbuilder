@props(['series', 'label' => 'Jumlah data per modul'])
@php($max = max(1, max(array_values($series))))
<div {{ $attributes->class(['ui-bar-chart']) }} role="img" aria-label="{{ $label }}: {{ collect($series)->map(fn ($value, $key) => $key.': '.$value)->implode(', ') }}">
    @foreach($series as $name => $value)<div class="ui-bar-chart__row"><span>{{ $name }}</span><div class="ui-bar-chart__track"><div style="width:{{ max(0, $value) / $max * 100 }}%"></div></div><strong>{{ number_format($value, 0, ',', '.') }}</strong></div>@endforeach
</div>
