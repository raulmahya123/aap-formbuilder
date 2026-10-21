@props(['disabled' => false, 'invalid' => false])
<input @disabled($disabled) @if($invalid) aria-invalid="true" @endif {{ $attributes->class(['ui-input']) }}>
