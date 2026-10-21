@props([
  'id',
  'title',
  'description' => null,
  'icon' => 'plus',
  'variant' => 'default',
  'size' => 'md',
  'action' => '',
  'method' => 'POST',
  'submitLabel' => 'Simpan Data',
  'submitIcon' => null,
  'cancelLabel' => 'Batal',
  'dirtyCheck' => true,
  'enctype' => null,
])
@php
  $methodUpper = strtoupper($method);
  $isNativeMethod = in_array($methodUpper, ['GET', 'POST'], true);
@endphp
<dialog id="{{ $id }}" {{ $attributes->class(['ui-modal', 'ui-modal--'.$size]) }} aria-labelledby="{{ $id }}-title" @if($dirtyCheck) data-dirty-check="true" @endif>
  <form method="{{ $isNativeMethod ? $methodUpper : 'POST' }}" action="{{ $action }}" @if($enctype) enctype="{{ $enctype }}" @endif class="flex flex-col flex-1 min-h-0">
    @csrf
    @if(!$isNativeMethod)
      @method($methodUpper)
    @endif

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
      <x-ui.button type="button" data-dialog-cancel>{{ $cancelLabel }}</x-ui.button>
      <x-ui.button variant="primary" type="submit">
        @if($submitIcon)<x-ui.lucide :name="$submitIcon" />@endif
        {{ $submitLabel }}
      </x-ui.button>
    </div>
  </form>
</dialog>
