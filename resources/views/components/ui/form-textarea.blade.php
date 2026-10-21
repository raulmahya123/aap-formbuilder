@props(['disabled' => false, 'invalid' => false])
<textarea @disabled($disabled) @if($invalid) aria-invalid="true" @endif {{ $attributes->class(['ui-input', 'ui-textarea']) }}>{{ $slot }}</textarea>
