@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded-xl">
  <h1 class="text-xl font-semibold mb-4">{{ $form->title }}</h1>

  {{-- Preview PDF referensi jika tipe = pdf --}}
  @if($form->type === 'pdf' && $form->pdf_path)
    <iframe
      class="w-full h-[70vh] border rounded mb-4"
      src="{{ asset('storage/'.$form->pdf_path) }}#view=FitH">
    </iframe>
  @endif

  {{-- Error summary --}}
  @if ($errors->any())
    <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-800 p-3 text-sm">
      <div class="font-medium mb-1">Ada kesalahan pada isian kamu:</div>
      <ul class="list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form
    method="post"
    action="{{ route('front.forms.store', $form->slug) }}"
    enctype="multipart/form-data">
    @csrf

    @if ($form->type === 'builder')
      @foreach (($form->schema['fields'] ?? []) as $field)
        @php
          $name     = $field['name'] ?? Str::slug($field['label'] ?? 'field','_');
          $type     = $field['type'] ?? 'text';
          $label    = $field['label'] ?? ucfirst($name);
          $required = !empty($field['required']);
          $options  = $field['options'] ?? [];
          $help     = $field['help'] ?? null;   // opsional di schema
        @endphp

        <div class="mb-4">
          <label class="block mb-1 font-medium" for="{{ $name }}">
            {{ $label }} @if($required)<span class="text-red-600">*</span>@endif
          </label>

          {{-- Text-like inputs --}}
          @if (in_array($type, ['text','email','date','number']))
            <input
              id="{{ $name }}"
              type="{{ $type }}"
              name="{{ $name }}"
              class="border rounded w-full px-3 py-2"
              value="{{ old($name) }}"
              @if($required) required @endif>

          {{-- Textarea --}}
          @elseif ($type === 'textarea')
            <textarea
              id="{{ $name }}"
              name="{{ $name }}"
              rows="{{ $field['rows'] ?? 4 }}"
              class="border rounded w-full px-3 py-2"
              @if($required) required @endif>{{ old($name) }}</textarea>

          {{-- Select --}}
          @elseif ($type === 'select')
            <select
              id="{{ $name }}"
              name="{{ $name }}"
              class="border rounded w-full px-3 py-2"
              @if($required) required @endif>
              <option value="">— Pilih —</option>
              @foreach ($options as $opt)
                @php([$val, $text] = is_array($opt) ? $opt : [$opt, $opt])
                <option value="{{ $val }}" @selected(old($name) == $val)>{{ $text }}</option>
              @endforeach
            </select>

          {{-- Radio --}}
          @elseif ($type === 'radio')
            <div class="flex flex-wrap gap-3">
              @foreach ($options as $i => $opt)
                @php([$val, $text] = is_array($opt) ? $opt : [$opt, $opt])
                <label class="inline-flex items-center gap-2">
                  <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $val }}"
                    @checked(old($name) == $val)
                    @if($required) required @endif>
                  <span>{{ $text }}</span>
                </label>
              @endforeach
            </div>

          {{-- Checkbox (multi) --}}
          @elseif ($type === 'checkbox')
            <div class="flex flex-wrap gap-3">
              @php($oldArr = collect(old($name, [])))
              @foreach ($options as $i => $opt)
                @php([$val, $text] = is_array($opt) ? $opt : [$opt, $opt])
                <label class="inline-flex items-center gap-2">
                  <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $val }}"
                    @checked($oldArr->contains($val))>
                  <span>{{ $text }}</span>
                </label>
              @endforeach
            </div>

          {{-- File upload --}}
          @elseif ($type === 'file')
            <input
              id="{{ $name }}"
              type="file"
              name="{{ $name }}"
              class="border rounded w-full px-3 py-2"
              @if($required) required @endif>
            @if(!empty($field['mimes']))
              <p class="text-xs text-slate-500 mt-1">Format: {{ $field['mimes'] }}</p>
            @endif
            @if(!empty($field['max']))
              <p class="text-xs text-slate-500">Maks: {{ $field['max'] }} KB</p>
            @endif

          {{-- Default (fallback) --}}
          @else
            <input
              id="{{ $name }}"
              type="text"
              name="{{ $name }}"
              class="border rounded w-full px-3 py-2"
              value="{{ old($name) }}"
              @if($required) required @endif>
          @endif

          {{-- Help text (opsional) --}}
          @if ($help)
            <p class="text-xs text-slate-500 mt-1">{{ $help }}</p>
          @endif

          {{-- Error per-field --}}
          @error($name)
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>
      @endforeach

    @else
      {{-- Untuk tipe PDF, sediakan beberapa meta isian opsional --}}
      <div class="mb-4">
        <label class="block mb-1 font-medium" for="catatan">Catatan</label>
        <textarea id="catatan" name="catatan" class="border rounded w-full px-3 py-2" rows="3">{{ old('catatan') }}</textarea>
        @error('catatan')
          <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>
    @endif

    <button class="px-4 py-2 bg-emerald-600 text-white rounded">
      Kirim
    </button>
  </form>
</div>
@endsection
