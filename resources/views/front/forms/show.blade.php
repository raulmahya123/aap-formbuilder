{{-- resources/views/front/forms/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div
  x-data="{ submitting:false, dark: (localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark' }"
  x-init="document.documentElement.classList.toggle('dark', dark)"
  :class="dark ? 'dark' : ''"
  class="bg-ivory-100 dark:bg-coal-900 min-h-screen text-coal-800 dark:text-ivory-100">
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
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    /** ================== FILE HANDLING (normalisasi + fallback) ================== */
    $hasFile = !empty($form->pdf_path);
    $rawPath = $hasFile ? trim((string) $form->pdf_path, '/') : null;

    // buang prefix public/ atau storage/ agar tidak dobel
    $basePath = $rawPath ? preg_replace('#^(public/|storage/)#', '', $rawPath) : null;

    // kumpulkan kandidat path
    $candidates = [];
    if ($basePath) {
    $candidates[] = $basePath;
    if (!Str::startsWith($basePath, 'forms/files/')) {
    $candidates[] = 'forms/files/'.ltrim($basePath, '/');
    }
    }

    // tebak nama dari slug/kode/judul/id
    $slugGuess = Str::slug($form->slug ?? $form->code ?? $form->title ?? (string)$form->id, '-');
    if ($slugGuess) {
    $candidates[] = "forms/files/{$slugGuess}.pdf";
    }

    // hilangkan duplikat & kosong
    $candidates = array_values(array_filter(array_unique($candidates)));

    // cari kandidat yang benar-benar ada
    $resolvedPath = null;
    foreach ($candidates as $cand) {
    if (Storage::disk('public')->exists($cand)) { $resolvedPath = $cand; break; }
    }

    // fallback terakhir: kalau hanya ada 1 PDF di forms/files, pakai itu
    if (!$resolvedPath) {
    $all = collect(Storage::disk('public')->files('forms/files'))
    ->filter(fn($p) => Str::lower(pathinfo($p, PATHINFO_EXTENSION)) === 'pdf')
    ->values();
    if ($all->count() === 1) { $resolvedPath = $all->first(); }
    }

    $path = $resolvedPath; // final (bisa null)
    $fileExists = $path ? Storage::disk('public')->exists($path) : false;

    // URL publik biasa (kalau symlink /storage sehat) — tidak dipakai untuk iframe
    $urlPublic = $fileExists ? Storage::url($path) : null;

    // URL streaming (bypass symlink /storage)
    $streamUrl = $fileExists ? route('pubfile.stream', ['path' => $path]) : null;
    $downloadUrl = $fileExists ? route('pubfile.download',['path' => $path]) : null;

    $ext = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
    $isPdf = $ext === 'pdf';
    $isOffice = in_array($ext, ['doc','docx','xls','xlsx','ppt','pptx']);

    // size
    $size = null;
    if ($fileExists) {
    try {
    $bytes = Storage::disk('public')->size($path);
    $units = ['B','KB','MB','GB'];
    $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    $pow = min($pow, count($units)-1);
    $size = number_format($bytes / pow(1024, $pow), $pow ? 2 : 0).' '.$units[$pow];
    } catch (\Throwable $e) {}
    }

    /** ================== SCHEMA HANDLING (agar $hasFields terdefinisi) ================== */
    $rawSchema = $form->schema ?? [];
    if (is_string($rawSchema)) {
    $decoded = json_decode($rawSchema, true);
    if (json_last_error() === JSON_ERROR_NONE) { $rawSchema = $decoded; }
    }
    $fields = [];
    if (is_array($rawSchema)) {
    if (isset($rawSchema['fields']) && is_array($rawSchema['fields'])) {
    $fields = $rawSchema['fields'];
    } elseif (array_keys($rawSchema) === range(0, max(count($rawSchema)-1,0))) {
    $fields = $rawSchema;
    }
    }
    $hasFields = !empty($fields);

    // helpers lama
    if (!function_exists('fraw')) { function fraw($name){ return old("data.$name"); } }
    if (!function_exists('fval')) { function fval($name){ $v = old("data.$name"); return is_array($v) ? '' : $v; } }
    if (!function_exists('opt_tuple')) {
    function opt_tuple($optKey,$opt){
    if (is_array($opt)) {
    if (array_key_exists('value',$opt)) { $val=(string)$opt['value']; $lab=(string)($opt['label']??$opt['value']); return [$val,$lab]; }
    if (array_key_exists(0,$opt)) { $val=(string)$opt[0]; $lab=(string)($opt[1]??$opt[0]); return [$val,$lab]; }
    return [(string)$optKey, json_encode($opt, JSON_UNESCAPED_UNICODE)];
    }
    $val = is_int($optKey) ? (string)$opt : (string)$optKey;
    $lab = (string)$opt;
    return [$val,$lab];
    }
    }
    @endphp

    {{-- ====== PREVIEW FILE ====== --}}
    @if($hasFile)
    <div class="mb-6">
      @if($fileExists && $isPdf)
      {{-- HANYA PDF yang di-embed --}}
      <div class="rounded-xl border overflow-hidden bg-white dark:bg-coal-900 dark:border-coal-800 shadow-soft">
        <iframe
          src="{{ $streamUrl }}#toolbar=1&navpanes=0&scrollbar=1"
          class="w-full"
          style="height:75vh"></iframe>
      </div>
      <div class="mt-3 flex flex-wrap gap-2">
        <a href="{{ $streamUrl }}" target="_blank" rel="noopener"
          class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 dark:hover:bg-coal-800">
          🔎 Buka di Tab Baru
        </a>
        <a href="{{ $downloadUrl }}" download
          class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
          ⬇️ Download @if($size)<span class="opacity-80 text-xs">({{ $size }})</span>@endif
        </a>
        {{-- Opsional tampilkan juga link publik biasa untuk debug --}}
        {{-- <a href="{{ $urlPublic }}" class="text-xs underline" target="_blank">/storage (debug)</a> --}}
      </div>

      @elseif($fileExists && ($isOffice || !$isPdf))
      {{-- Word/Excel/PPT/dll: tombol saja --}}
      <div class="p-4 rounded-lg border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800">
        <div class="text-sm">
          File: <span class="font-medium uppercase">{{ $ext }}</span>
          @if($size) • <span class="text-slate-500">{{ $size }}</span> @endif
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ $streamUrl }}" target="_blank" rel="noopener"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-300 hover:bg-slate-50 dark:hover:bg-coal-800">
            🔎 Buka di Tab Baru
          </a>
          <a href="{{ $downloadUrl }}" download
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
            ⬇️ Download
          </a>
        </div>
      </div>

      @else
      <div class="rounded-lg border p-3 bg-amber-50 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-900/30">
        File tidak ditemukan.
        @if($rawPath)
        <div class="mt-1 text-xs">
          Dicari dari: <code>{{ $rawPath }}</code> → kandidat: <code>{{ implode(', ', $candidates) }}</code>
        </div>
        @endif
        Pastikan file ada di <code>storage/app/public/...</code>, symlink <code>public/storage</code> aktif,
        atau gunakan route streaming <code>pubfile.*</code> (sudah dipakai di halaman ini).
      </div>
      @endif
    </div>
    @endif
    {{-- ====== /PREVIEW FILE ====== --}}

    <form method="POST"
      action="{{ route('front.forms.store', $form) }}"
      enctype="multipart/form-data"
      @submit="submitting = true">
      @csrf
      <input type="hidden" name="form_id" value="{{ $form->id }}">

      <div class="rounded-2xl border bg-ivory-50 dark:bg-coal-900 dark:border-coal-800 p-5 space-y-5 shadow-soft">
        @if($fileExists && $isPdf && !$hasFields)
        <div class="text-sm text-coal-600 dark:text-coal-300">
          Dokumen PDF ditampilkan di atas. Tidak ada isian tambahan.
        </div>
        @else
        {{-- Render fields --}}
        @forelse($fields as $i => $field)
        @php
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? "field_$i";
        $label = $field['label'] ?? ucfirst(str_replace('_',' ', $name));
        $placeholder = $field['placeholder'] ?? '';
        $help = $field['help'] ?? '';
        $required = (bool)($field['required'] ?? false);
        $options = $field['options'] ?? [];
        $multiple = (bool)($field['multiple'] ?? false);
        $min = $field['min'] ?? null;
        $max = $field['max'] ?? null;
        $step = $field['step'] ?? null;
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
          @php $oval = fraw($name); @endphp
          @if(!empty($options))
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
              @checked((string)fval($name)==='1' )>
            <span>{{ $placeholder ?: 'Ya' }}</span>
          </label>
          @endif
          @break

          @case('file')
          @php
          $accept = '';
          if (!empty($field['accept'])) {
          $accept = is_array($field['accept'])
          ? implode(',', $field['accept'])
          : $field['accept'];
          }
          @endphp

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
            @if($accept) accept="{{ $accept }}" @endif>
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
      $showSubmit = (!$fileExists) || ($fileExists && $hasFields);
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