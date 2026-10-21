@props(['id', 'title', 'size' => 'md', 'icon' => null, 'variant' => 'default'])
<dialog id="{{ $id }}" {{ $attributes->class(['ui-modal', 'ui-modal--'.$size]) }} aria-labelledby="{{ $id }}-title">
  <div class="ui-modal__header">
    @if($icon)
      <div class="ui-modal__icon {{ $variant !== 'default' ? 'ui-modal__icon--'.$variant : '' }}">
        <x-ui.lucide :name="$icon" />
      </div>
    @endif
    <div class="ui-modal__header-content">
      <h2 id="{{ $id }}-title" class="ui-modal__title">{{ $title }}</h2>
      @if(isset($description))
        <p class="ui-modal__description">{{ $description }}</p>
      @endif
    </div>
    <button type="button" class="ui-modal__close" data-dialog-cancel aria-label="Tutup">
      <x-ui.lucide name="x" />
    </button>
  </div>
  <div class="ui-modal__body">
    {{ $slot }}
  </div>
  @if(isset($footer))
    <div class="ui-modal__footer">
      {{ $footer }}
    </div>
  @endif
</dialog>
