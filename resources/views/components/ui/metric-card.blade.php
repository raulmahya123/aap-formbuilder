@props(['label', 'value', 'href' => null, 'icon' => 'circle-check'])
@if($href)<a href="{{ $href }}" {{ $attributes->class(['ui-metric-card']) }}>@else<div {{ $attributes->class(['ui-metric-card']) }}>@endif
    <div class="ui-metric-card__label"><span>{{ $label }}</span><x-ui.lucide :name="$icon" /></div>
    <strong>{{ is_numeric($value) ? number_format($value, 0, ',', '.') : $value }}</strong>
    @if($href)<span class="ui-metric-card__link">Lihat data <span aria-hidden="true">→</span></span>@endif
@if($href)</a>@else</div>@endif
