@props(['messages' => []])
@if($messages)
<ul {{ $attributes->class(['form-error']) }} data-field-error role="alert">@foreach((array) $messages as $message)<li>{{ \App\Support\Feedback::message($message, 'Periksa kembali nilai pada isian ini.') }}</li>@endforeach</ul>
@endif
