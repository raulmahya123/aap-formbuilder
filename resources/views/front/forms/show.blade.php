{{-- resources/views/front/forms/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div
  x-data="{ submitting:false, dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100"
>
  <div class="max-w-3xl mx-auto px-4 py-8">
    {{-- Breadcrumbs --}}
    <nav class="text-sm text-coal-500 dark:text-coal-400 mb-4">
      <a href="{{ route('dashboard') }}" class="hover:underline text-maroon-700 dark:text-maroon-300">Beranda</a>
      <span class="mx-2">/</span>
      <span class="text-coal-700 dark:text-ivory-100">{{ $form->title ?? 'Form' }}</span>
    </nav>

    {{-- Header --}}
    <header class="mb-6">
      <h1 class="text-2xl md:text-3xl font-serif tracking-tight">{{ $form->title }}</h1>
      @if(!empty($form->description))
        <p class="mt-2 text-coal-600 dark:text-coal-300">{{ $form->description }}</p>
      @endif
    </header>

    {{-- Flash sukses --}}
    @if(session('success'))
      <div class="mb-4 rounded-lg border px-4 py-3
                  bg-maroon-50 text-maroon-700 border-maroon-200
                  dark:bg-maroon-900/20 dark:text-maroon-300 dark:border-maroon-900/30">
        {{ session('success') }}
      </div>
    @endif

    {{-- Error global --}}
    @if ($errors->any())
      <div class="mb-4 rounded-lg border px-4 py-3
                  bg-rose-50 text-rose-700 border-rose-200
                  dark:bg-rose-900/20 dark:text-rose-300 dark:border-rose-900/30">
        <div class="font-medium">Terjadi kesalahan pada input:</div>
        <ul class="list-disc pl-5 mt-2 space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @php
      // ============== TIPE & PDF ==============
      $isPdf = ($form->type ?? '') === 'pdf';
      $pdfExists = false;
      $pdfUrl = null;

      if ($isPdf && $form->pdf_path) {
        $pdfExists = Storage::disk('public')->exists($form->pdf_path);
        if ($pdfExists) {
          // pastikan sudah: php artisan storage:link
          $pdfUrl = asset('storage/'.$form->pdf_path);
        }
      }

      // ============== NORMALISASI SCHEMA ==============
      $raw = $form->schema ?? [];
      if (is_string($raw)) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) $raw = $decoded;
      }

      $fields = [];
      if (is_array($raw)) {
        if (isset($raw['fields']) && is_array($raw['fields'])) {
          $fields = $raw['fields'];     // bentuk { "fields": [...] }
        } elseif (array_keys($raw) === range(0, max(count($raw)-1, 0))) {
          $fields = $raw;               // bentuk [...]
        }
      }
      $hasFields = !empty($fields);

      // ============== Helper OLD aman ==============
      if (!function_exists('fraw')) {
        function fraw($name) { return old("data.$name"); } // boleh array
      }
      if (!function_exists('fval')) {
        function fval($name) {
          $v = old("data.$name");
          return is_array($v) ? '' : $v; // string-only untuk value=""
        }
      }

      // ============== Helper opsi (value,label) ==============
      if (!function_exists('opt_tuple')) {
        function opt_tuple($optKey, $opt) {
          if (is_array($opt)) {
            if (array_key_exists('value', $opt)) {
              $val = (string)$opt['value'];
              $lab = (string)($opt['label'] ?? $opt['value']);
              return [$val, $lab];
            }
            if (array_key_exists(0, $opt)) {
              $val = (string)$opt[0];
              $lab = (string)($opt[1] ?? $opt[0]);
              return [$val, $lab];
            }
            return [(string)$optKey, json_encode($opt, JSON_UNESCAPED_UNICODE)];
          }
          $val = is_int($optKey) ? (string)$opt : (string)$optKey;
          $lab = (string)$opt;
          return [$val, $lab];
        }
      }
    @endphp

    {{-- PREVIEW PDF (khusus tipe PDF) --}}
    @if($isPdf)
      <div class="mb-6">
        @if($pdfExists)
          <div class="rounded-xl border overflow-hidden bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 shadow-soft">
            <iframe
              src="{{ $pdfUrl }}#toolbar=1&navpanes=0&scrollbar=1"
              class="w-full"
              style="height: 75vh;"
            ></iframe>
          </div>
          <div class="mt-2">
            <a class="text-sm text-maroon-700 hover:underline dark:text-maroon-300" href="{{ $pdfUrl }}" target="_blank">Buka / Unduh PDF</a>
          </div>
        @else
          <div class="rounded-lg border p-3 bg-amber-50 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-900/30">
            File PDF belum tersedia. Silakan hubungi admin untuk mengunggah PDF terlebih dahulu.
          </div>
        @endif
      </div>
    @endif

    <form method="POST"
          action="{{ route('front.forms.store', $form) }}"
          enctype="multipart/form-data"
          @submit="submitting = true">
      @csrf
      <input type="hidden" name="form_id" value="{{ $form->id }}">

      <div class="rounded-2xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 p-5 space-y-5 shadow-soft">
        @if($isPdf && !$hasFields)
          {{-- PDF tanpa field: hanya info (tanpa input apa pun) --}}
          @if($pdfExists)
            <div class="text-sm text-coal-600 dark:text-coal-300">
              Dokumen PDF ditampilkan di atas. Tidak ada isian tambahan.
            </div>
          @endif
        @else
          {{-- Render fields --}}
          @forelse($fields as $i => $field)
            @php
              $type        = $field['type']        ?? 'text';
              $name        = $field['name']        ?? "field_$i";
              $label       = $field['label']       ?? ucfirst(str_replace('_',' ', $name));
              $placeholder = $field['placeholder'] ?? '';
              $help        = $field['help']        ?? '';
              $required    = (bool)($field['required'] ?? false);
              $options     = $field['options']     ?? [];
              $multiple    = (bool)($field['multiple'] ?? false);
              $min         = $field['min'] ?? null;
              $max         = $field['max'] ?? null;
              $step        = $field['step'] ?? null;
            @endphp

            <div class="space-y-1">
              <label for="f_{{ $i }}" class="block text-sm font-medium text-coal-700 dark:text-coal-300">
                {{ $label }} @if($required)<span class="text-rose-600">*</span>@endif
              </label>

              @switch($type)
                @case('textarea')
                  <textarea id="f_{{ $i }}" name="data[{{ $name }}]"
                            class="mt-1 w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-950 dark:border-coal-700
                                   focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500"
                            placeholder="{{ $placeholder }}"
                            @if($required) required @endif
                            rows="{{ $field['rows'] ?? 4 }}">{{ fval($name) }}</textarea>
                  @break

                @case('select')
                  <select id="f_{{ $i }}" name="data[{{ $name }}]"
                          class="mt-1 w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-950 dark:border-coal-700
                                 focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500"
                          @if($required) required @endif>
                    <option value="">— Pilih —</option>
                    @foreach($options as $optKey => $opt)
                      @php [$optVal2,$optLab2] = opt_tuple($optKey, $opt); @endphp
                      <option value="{{ $optVal2 }}" @selected(fval($name)==$optVal2)>{{ $optLab2 }}</option>
                    @endforeach
                  </select>
                  @break

                @case('radio')
                  <div class="mt-1 space-y-2">
                    @foreach($options as $optKey => $opt)
                      @php [$v,$l] = opt_tuple($optKey, $opt); @endphp
                      <label class="inline-flex items-center gap-2">
                        <input type="radio" name="data[{{ $name }}]" value="{{ $v }}"
                               class="rounded border-coal-300 text-maroon-700 focus:ring-maroon-500"
                               @checked(fval($name)===$v)
                               @if($required) required @endif>
                        <span>{{ $l }}</span>
                      </label>
                    @endforeach
                  </div>
                  @break

                @case('checkbox')
                  @if($multiple && !empty($options))
                    @php $oval = fraw($name); @endphp
                    <div class="mt-1 space-y-2">
                      @foreach($options as $optKey => $opt)
                        @php [$v,$l] = opt_tuple($optKey, $opt); @endphp
                        <label class="flex items-center gap-2">
                          <input type="checkbox" name="data[{{ $name }}][]" value="{{ $v }}"
                                 class="rounded border-coal-300 text-maroon-700 focus:ring-maroon-500"
                                 @checked(is_array($oval) && in_array($v, (array)$oval, true))>
                          <span>{{ $l }}</span>
                        </label>
                      @endforeach
                    </div>
                  @else
                    <label class="inline-flex items-center gap-2 mt-1">
                      <input type="checkbox" id="f_{{ $i }}" name="data[{{ $name }}]" value="1"
                             class="rounded border-coal-300 text-maroon-700 focus:ring-maroon-500"
                             @checked((string)fval($name)==='1')>
                      <span>{{ $placeholder ?: 'Ya' }}</span>
                    </label>
                  @endif
                  @break

                @case('file')
                  <input type="file" id="f_{{ $i }}"
                         name="data[{{ $name }}]{{ $multiple ? '[]' : '' }}"
                         class="mt-1 block w-full text-sm
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:bg-maroon-50 file:text-maroon-700
                                hover:file:bg-maroon-100
                                dark:file:bg-maroon-900/20 dark:file:text-maroon-300"
                         @if($multiple) multiple @endif
                         @if($required) required @endif
                         @if(!empty($field['accept'])) accept="{{ $field['accept'] }}" @endif>
                  @break

                @case('number')
                  <input type="number" id="f_{{ $i }}" name="data[{{ $name }}]"
                         class="mt-1 w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-950 dark:border-coal-700
                                focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500"
                         placeholder="{{ $placeholder }}"
                         value="{{ fval($name) }}"
                         @if($required) required @endif
                         @if(!is_null($min)) min="{{ $min }}" @endif
                         @if(!is_null($max)) max="{{ $max }}" @endif
                         @if(!is_null($step)) step="{{ $step }}" @endif>
                  @break

                @case('date')
                  <input type="date" id="f_{{ $i }}" name="data[{{ $name }}]"
                         class="mt-1 w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-950 dark:border-coal-700
                                focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500"
                         value="{{ fval($name) }}"
                         @if($required) required @endif>
                  @break

                @default
                  <input type="{{ in_array($type,['email','tel','url','password']) ? $type : 'text' }}"
                         id="f_{{ $i }}" name="data[{{ $name }}]"
                         class="mt-1 w-full rounded-lg border px-3 py-2 bg-white dark:bg-coal-950 dark:border-coal-700
                                focus:outline-none focus:ring-2 focus:ring-maroon-500/60 focus:border-maroon-500"
                         placeholder="{{ $placeholder }}"
                         value="{{ fval($name) }}"
                         @if($required) required @endif>
              @endswitch

              @if(!empty($help))
                <p class="text-xs text-coal-500 dark:text-coal-400 mt-1">{{ $help }}</p>
              @endif

              @error("data.$name")
                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
              @enderror
            </div>
          @empty
            <div class="text-coal-500 dark:text-coal-400">Skema form belum diatur. Hubungi administrator.</div>
          @endforelse
        @endif
      </div>

      @php
        // Tampilkan tombol submit hanya jika:
        // - BUKAN PDF, atau
        // - PDF & file-nya ada & ADA field tambahan
        $showSubmit = (!$isPdf) || ($isPdf && $pdfExists && $hasFields);
      @endphp

      @if($showSubmit)
        <div class="mt-6 flex items-center gap-3">
          <button type="submit" :disabled="submitting"
                  class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-maroon-700 text-ivory-50 hover:bg-maroon-600 disabled:opacity-60 transition">
            <span x-text="submitting ? 'Mengirim…' : 'Kirim'"></span>
          </button>
          <a href="{{ url()->previous() }}"
             class="px-4 py-2 rounded-xl border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60 transition
                    dark:text-maroon-300 dark:border-maroon-900/30 dark:hover:bg-maroon-900/20">
            Batal
          </a>
        </div>
      @else
        {{-- PDF tanpa field: hanya tombol kembali --}}
        <div class="mt-6">
          <a href="{{ url()->previous() }}"
             class="px-4 py-2 rounded-xl border border-maroon-600 text-maroon-700 hover:bg-maroon-50/60 transition
                    dark:text-maroon-300 dark:border-maroon-900/30 dark:hover:bg-maroon-900/20">
            Kembali
          </a>
        </div>
      @endif

      <p class="text-xs text-coal-500 dark:text-coal-400 mt-3">
        Dengan mengirimkan formulir ini, Anda menyetujui pemrosesan data sesuai kebijakan privasi perusahaan.
      </p>
    </form>
  </div>
</div>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
