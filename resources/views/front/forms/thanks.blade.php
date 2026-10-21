@extends('layouts.app')

@section('content')
@php
  use App\Models\Form as _Form;

  // Normalisasi $form → selalu jadi model atau null
  $formModel = null;

  if (isset($form) && $form instanceof _Form) {
      $formModel = $form;
  } else {
      $param = request()->route('form');
      if ($param instanceof _Form) {
          $formModel = $param;
      } elseif (is_string($param)) {
          $formModel = _Form::where('slug', $param)->first();
      }
  }
@endphp


<x-ui.page>

  <x-ui.page-header>
    <div class="ui-page-header__copy">
      <x-ui.page-title>Terima kasih!</x-ui.page-title>
      <x-ui.page-description>@if($formModel?->title)Jawaban untuk {{ $formModel->title }} sudah kami terima.@else Jawaban formulir Anda sudah kami terima.@endif</x-ui.page-description>
    </div>
    <x-ui.page-actions>
      @if(Route::has('dashboard'))<x-ui.link variant="secondary" href="{{ route('dashboard') }}">Ke Beranda</x-ui.link>@endif
      @if($formModel && Route::has('front.forms.show'))<x-ui.link variant="primary" href="{{ route('front.forms.show', $formModel->slug) }}">Isi Lagi</x-ui.link>@endif
    </x-ui.page-actions>
  </x-ui.page-header>
  <p class="text-sm text-coal-500">Jika membutuhkan bukti pengajuan, silakan hubungi administrator.</p>
</x-ui.page>
@endsection
