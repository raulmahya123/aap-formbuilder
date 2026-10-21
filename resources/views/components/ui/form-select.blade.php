@props(['disabled' => false, 'invalid' => false])
<select @disabled($disabled) @if($invalid) aria-invalid="true" @endif {{ $attributes->class(['ui-input']) }}>{{ $slot }}</select>
