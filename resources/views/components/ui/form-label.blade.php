@props(['value' => null, 'required' => false])
<label {{ $attributes->class(['ui-label']) }}>{{ $value ?? $slot }}@if($required) <span class="ui-required" aria-hidden="true">*</span>@endif</label>
