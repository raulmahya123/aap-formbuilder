@props(['name', 'label', 'id' => null, 'required' => false, 'helper' => null, 'bag' => 'default'])
@php($fieldId = $id ?? str_replace(['.', '[', ']'], '-', $name))
<div {{ $attributes->class(['ui-form-field']) }} data-form-field data-field-name="{{ $name }}" data-error-bag="{{ $bag }}">
    <x-ui.form-label :for="$fieldId" :required="$required">{{ $label }}</x-ui.form-label>
    {{ $slot }}
    @if($helper)<x-ui.form-helper-text :id="$fieldId.'-helper'">{{ $helper }}</x-ui.form-helper-text>@endif
    <x-ui.form-error :id="$fieldId.'-error'" :messages="$errors->getBag($bag)->get($name)" />
</div>
