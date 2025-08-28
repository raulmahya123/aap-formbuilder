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

<div class="max-w-xl mx-auto px-4 py-12">
  @if(session('ok'))
    <div class="mb-6 rounded-lg border border-maroon-200 bg-maroon-50 text-maroon-800 px-4 py-3">
      {{ session('ok') }}
    </div>
  @endif

  <div class="rounded-2xl border bg-white p-8 text-center">
    <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-maroon-100 text-maroon-700 flex items-center justify-center">
      <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
      </svg>
    </div>

    <h1 class="text-2xl font-semibold">Terima kasih!</h1>
    <p class="mt-2 text-slate-600">
      @if($formModel?->title)
        Jawaban untuk <span class="font-medium">{{ $formModel->title }}</span> sudah kami terima.
      @else
        Jawaban formulir Anda sudah kami terima.
      @endif
    </p>

    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
      @if($formModel && Route::has('front.forms.show'))
        <a href="{{ route('front.forms.show', $formModel->slug) }}"
           class="px-4 py-2 rounded-xl bg-maroon-700 text-white hover:bg-maroon-800">
          Isi Lagi
        </a>
      @endif

      @if(Route::has('dashboard'))
        <a href="{{ route('dashboard') }}"
           class="px-4 py-2 rounded-xl border border-maroon-600 text-maroon-700 hover:bg-maroon-50">
          Ke Beranda
        </a>
      @endif
    </div>

    <p class="mt-6 text-xs text-slate-500">
      Jika membutuhkan bukti pengajuan, silakan hubungi administrator.
    </p>
  </div>
</div>
@endsection
