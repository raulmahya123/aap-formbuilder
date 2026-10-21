@props(['label', 'value' => null])
<div {{ $attributes->class(['ui-detail-row']) }}>
  <div class="ui-detail-row__label">{{ $label }}</div>
  <div class="ui-detail-row__value">
    {{ $value ?? $slot }}
  </div>
</div>
