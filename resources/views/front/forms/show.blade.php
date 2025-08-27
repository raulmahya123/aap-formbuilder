{{-- resources/views/front/forms/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8"
     x-data="{ submitting:false }">
  {{-- Breadcrumbs sederhana (opsional) --}}
  <nav class="text-sm text-slate-500 mb-4">
    <a href="{{ route('dashboard') }}" class="hover:underline">Beranda</a>
    <span class="mx-2">/</span>
    <span class="text-slate-700">{{ $form->title ?? 'Form' }}</span>
  </nav>

  {{-- Header --}}
  <header class="mb-6">
    <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
      {{ $form->title }}
    </h1>
    @if(!empty($form->description))
      <p class="mt-2 text-slate-600">{{ $form->description }}</p>
    @endif
  </header>

  {{-- Alert sukses (opsional: tampilkan via session) --}}
  @if(session('success'))
    <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-3">
      {{ session('success') }}
    </div>
  @endif

  {{-- Error global --}}
  @if ($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 px-4 py-3">
      <div class="font-medium">Terjadi kesalahan pada input:</div>
      <ul class="list-disc pl-5 mt-2 space-y-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    // Normalisasi schema → array field
    $fields = $form->schema ?? [];
    if (is_string($fields)) {
      $decoded = json_decode($fields, true);
      if (json_last_error() === JSON_ERROR_NONE) $fields = $decoded;
    }
    if (!is_array($fields)) $fields = [];
    // Helper kecil
    function fval($name){ return old("data.$name"); }
  @endphp

  <form method="POST"
        action="{{ isset($postUrl) ? $postUrl : (Route::has('front.entries.store') ? route('front.entries.store',$form) : '#') }}"
        enctype="multipart/form-data"
        @submit="submitting = true">
    @csrf

    <input type="hidden" name="form_id" value="{{ $form->id }}">

    <div class="rounded-2xl border bg-white dark:bg-slate-900 dark:border-slate-800 p-5 space-y-5">
      @forelse($fields as $i => $field)
        @php
          $type        = $field['type']        ?? 'text';
          $name        = $field['name']        ?? "field_$i";
          $label       = $field['label']       ?? ucfirst(str_replace('_',' ', $name));
          $placeholder = $field['placeholder'] ?? '';
          $help        = $field['help']        ?? '';
          $required    = (bool)($field['required'] ?? false);
          $options     = $field['options']     ?? []; // untuk select/radio/checkbox
          $multiple    = (bool)($field['multiple'] ?? false); // utk file/checkbox
          $min         = $field['min'] ?? null;
          $max         = $field['max'] ?? null;
          $step        = $field['step'] ?? null;
        @endphp

        <div class="space-y-1">
          <label for="f_{{ $i }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if($required)
              <span class="text-rose-600">*</span>
            @endif
          </label>

          {{-- Field Renderer --}}
          @switch($type)
            @case('textarea')
              <textarea id="f_{{ $i }}"
                        name="data[{{ $name }}]"
                        class="mt-1 w-full rounded-lg border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:bg-slate-950 dark:border-slate-700"
                        placeholder="{{ $placeholder }}"
                        @if($required) required @endif
                        rows="{{ $field['rows'] ?? 4 }}">{{ fval($name) }}</textarea>
              @break

            @case('select')
              <select id="f_{{ $i }}"
                      name="data[{{ $name }}]"
                      class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-slate-950 dark:border-slate-700"
                      @if($required) required @endif>
                <option value="">— Pilih —</option>
                @foreach($options as $optVal => $optLabel)
                  @php
                    // options bisa bentuk ['value'=>'Label'] atau [['value'=>'v','label'=>'L']]
                    if (is_array($optLabel) && isset($optLabel['value'])) {
                      $optVal2 = $optLabel['value']; $optLab2 = $optLabel['label'] ?? $optLabel['value'];
                    } else {
                      $optVal2 = is_int($optVal) ? $optLabel : $optVal;
                      $optLab2 = is_int($optVal) ? $optLabel : $optLabel;
                    }
                  @endphp
                  <option value="{{ $optVal2 }}" @selected(fval($name)==$optVal2)>{{ $optLab2 }}</option>
                @endforeach
              </select>
              @break

            @case('radio')
              <div class="mt-1 space-y-2">
                @foreach($options as $optVal => $optLabel)
                  @php
                    if (is_array($optLabel) && isset($optLabel['value'])) {
                      $v = $optLabel['value']; $l = $optLabel['label'] ?? $optLabel['value'];
                    } else {
                      $v = is_int($optVal) ? $optLabel : $optVal;
                      $l = is_int($optVal) ? $optLabel : $optLabel;
                    }
                  @endphp
                  <label class="inline-flex items-center gap-2">
                    <input type="radio"
                           name="data[{{ $name }}]"
                           value="{{ $v }}"
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                           @checked(fval($name)==$v)
                           @if($required) required @endif>
                    <span>{{ $l }}</span>
                  </label>
                @endforeach
              </div>
              @break

            @case('checkbox')
              @if($multiple && !empty($options))
                <div class="mt-1 space-y-2">
                  @foreach($options as $optVal => $optLabel)
                    @php
                      if (is_array($optLabel) && isset($optLabel['value'])) {
                        $v = $optLabel['value']; $l = $optLabel['label'] ?? $optLabel['value'];
                      } else {
                        $v = is_int($optVal) ? $optLabel : $optVal;
                        $l = is_int($optVal) ? $optLabel : $optLabel;
                      }
                      $checked = collect((array)fval($name))->contains($v);
                    @endphp
                    <label class="flex items-center gap-2">
                      <input type="checkbox"
                             name="data[{{ $name }}][]"
                             value="{{ $v }}"
                             class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                             @checked($checked)>
                      <span>{{ $l }}</span>
                    </label>
                  @endforeach
                </div>
              @else
                <label class="inline-flex items-center gap-2 mt-1">
                  <input type="checkbox"
                         id="f_{{ $i }}"
                         name="data[{{ $name }}]"
                         value="1"
                         class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                         @checked((string)fval($name)==='1')>
                  <span>{{ $placeholder ?: 'Ya' }}</span>
                </label>
              @endif
              @break

            @case('file')
              <input type="file"
                     id="f_{{ $i }}"
                     name="data[{{ $name }}]{{ $multiple ? '[]' : '' }}"
                     class="mt-1 block w-full text-sm file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0 file:text-sm
                            file:bg-emerald-50 file:text-emerald-700
                            hover:file:bg-emerald-100 dark:file:bg-emerald-900/20 dark:file:text-emerald-300"
                     @if($multiple) multiple @endif
                     @if($required) required @endif
                     @if(!empty($field['accept'])) accept="{{ $field['accept'] }}" @endif>
              @break

            @case('number')
              <input type="number"
                     id="f_{{ $i }}"
                     name="data[{{ $name }}]"
                     class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-slate-950 dark:border-slate-700"
                     placeholder="{{ $placeholder }}"
                     value="{{ fval($name) }}"
                     @if($required) required @endif
                     @if(!is_null($min)) min="{{ $min }}" @endif
                     @if(!is_null($max)) max="{{ $max }}" @endif
                     @if(!is_null($step)) step="{{ $step }}" @endif>
              @break

            @case('date')
              <input type="date"
                     id="f_{{ $i }}"
                     name="data[{{ $name }}]"
                     class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-slate-950 dark:border-slate-700"
                     value="{{ fval($name) }}"
                     @if($required) required @endif>
              @break

            @default
              <input type="{{ in_array($type,['email','tel','url','password']) ? $type : 'text' }}"
                     id="f_{{ $i }}"
                     name="data[{{ $name }}]"
                     class="mt-1 w-full rounded-lg border px-3 py-2 dark:bg-slate-950 dark:border-slate-700"
                     placeholder="{{ $placeholder }}"
                     value="{{ fval($name) }}"
                     @if($required) required @endif>
          @endswitch

          {{-- Help text --}}
          @if($help)
            <p class="text-xs text-slate-500 mt-1">{{ $help }}</p>
          @endif

          {{-- Error untuk field ini --}}
          @error("data.$name")
            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
          @enderror
        </div>
      @empty
        <div class="text-slate-500">
          Skema form belum diatur. Hubungi administrator.
        </div>
      @endforelse
    </div>

    {{-- Aksi --}}
    <div class="mt-6 flex items-center gap-3">
      <button type="submit"
              :disabled="submitting"
              class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-60">
        <svg x-show="submitting" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <span x-text="submitting ? 'Mengirim…' : 'Kirim'"></span>
      </button>

      <a href="{{ url()->previous() }}"
         class="px-4 py-2 rounded-xl border hover:bg-slate-50">
        Batal
      </a>
    </div>

    {{-- Catatan privasi (opsional) --}}
    <p class="text-xs text-slate-500 mt-3">
      Dengan mengirimkan formulir ini, Anda menyetujui pemrosesan data sesuai kebijakan privasi perusahaan.
    </p>
  </form>
</div>

{{-- Alpine (jika belum ada di layout) --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
