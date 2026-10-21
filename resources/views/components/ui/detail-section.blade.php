@props(['title' => null])
<div {{ $attributes->class(['ui-detail-section']) }}>
  @if($title)
    <div class="ui-detail-section__header">{{ $title }}</div>
  @endif
  <div class="ui-detail-section__body">
    {{ $slot }}
  </div>
</div>
