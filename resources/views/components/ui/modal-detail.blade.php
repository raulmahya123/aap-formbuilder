@props([
  'id',
  'title',
  'description' => null,
  'icon' => 'eye',
  'variant' => 'default',
  'size' => 'md',
  'editHref' => null,
  'editLabel' => 'Edit Data',
  'closeLabel' => 'Tutup',
])
<dialog id="{{ $id }}" {{ $attributes->class(['ui-modal', 'ui-modal--'.$size]) }} aria-labelledby="{{ $id }}-title">
  <div class="ui-modal__header">
    @if($icon)
      <div class="ui-modal__icon {{ $variant !== 'default' ? 'ui-modal__icon--'.$variant : '' }}">
        <x-ui.lucide :name="$icon" />
      </div>
    @endif
    <div class="ui-modal__header-content">
      <h2 id="{{ $id }}-title" class="ui-modal__title">{{ $title }}</h2>
      @if($description)
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

  <div class="ui-modal__footer">
    <x-ui.button type="button" data-dialog-cancel>{{ $closeLabel }}</x-ui.button>
    @if($editHref)
      <x-ui.link variant="primary" href="{{ $editHref }}">
        <x-ui.lucide name="pencil" />
        {{ $editLabel }}
      </x-ui.link>
    @endif
  </div>
</dialog>
