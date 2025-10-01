@extends('layouts.app')

@section('title','Input Harian')

@section('content')
{{-- ===== Overlay Watermark TELAT (opsional, kirim $showLateWatermark dari controller) ===== --}}
@if(!empty($showLateWatermark))
<div class="pointer-events-none select-none fixed inset-0 flex items-center justify-center" style="z-index:0">
  <div class="font-black tracking-widest uppercase"
    style="color: rgba(220,38,38,.12); font-size: clamp(3rem,12vw,10rem); transform: rotate(-20deg);">
    LATE INPUT
  </div>
</div>
@endif

<h1 class="text-2xl font-bold mb-6 text-maroon-700">
  Input Harian Per Site
  @isset($currentShift)
  <span class="ml-2 text-sm px-2 py-1 rounded bg-gray-100 border">
    Shift {{ $currentShift ?? '—' }}
  </span>
  @endisset
</h1>

@php
// dari controller: $sites (sudah terfilter), $date, $groups
$selectedSite = old('site_id', request('site_id'));
$hasAnyAllowedSite = $sites->filter(fn($s) => auth()->user()?->can('daily.manage', $s->id))->count() > 0;

// ==== READ-ONLY MODE ====
$computedReadonly = false;
if (isset($readOnly)) {
$computedReadonly = (bool) $readOnly;
} elseif ($selectedSite) {
$computedReadonly = !auth()->user()?->can('daily.manage', (int)$selectedSite);
} else {
$computedReadonly = !$hasAnyAllowedSite;
}
@endphp

@if($computedReadonly)
<div class="max-w-6xl mb-4 p-3 rounded-lg border border-blue-200 bg-blue-50 text-blue-900 text-sm">
  Mode tampilan saja. Kamu tidak dapat mengubah data pada halaman ini.
</div>
@endif

@if(!$hasAnyAllowedSite)
<div class="max-w-6xl mb-6 p-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-900">
  Kamu belum memiliki akses ke site mana pun untuk input harian.
  Hubungi admin untuk diberikan akses site.
</div>
@endif

<form action="{{ route('admin.daily.store') }}" method="post"
  x-data="{
        sanitize($el) {
          // normalisasi koma → titik
          let v = ($el.value || '').replaceAll(',', '.').trim();

          // jika user baru ngetik '-' atau '+' → jadikan 0
          if (v === '-' || v === '+') v = '0';

          // kalau kosong, biarkan (anggap null)
          if (v === '') { $el.value = ''; return; }

          // bukan angka? biarkan apa adanya supaya kelihatan error state
          if (isNaN(Number(v))) { $el.value = v; return; }

          // cast number
          let num = Number(v);

          // ambil batas min/max dari atribut
          const minAttr = $el.getAttribute('min');
          const maxAttr = $el.getAttribute('max');
          let min = (minAttr !== null) ? Number(minAttr) : -Infinity;
          let max = (maxAttr !== null) ? Number(maxAttr) :  Infinity;

          // kalau diberi data-nonneg → paksa minimal 0
          if ($el.dataset.nonneg === '1' && !isFinite(min)) min = 0;

          // clamp
          if (num < min) num = min;
          if (num > max) num = max;

          // pembulatan sesuai step (jika ada)
          const stepAttr = $el.getAttribute('step') || '0.0001';
          const step = Number(stepAttr);
          if (isFinite(step) && step > 0) {
            const base = isFinite(min) ? min : 0;
            num = Math.round((num - base) / step) * step + base;
          }

          // hilangkan -0
          if (Object.is(num, -0)) num = 0;

          $el.value = String(num);
        },
        keyguard(e) {
          // blokir karakter yang menghasilkan negatif atau format eksponensial
          const blocked = ['-','+','e','E'];
          if (blocked.includes(e.key)) e.preventDefault();
        },
        pasteguard(e) {
          // pada paste, bersihkan setelah masuk
          requestAnimationFrame(() => this.sanitize(e.target));
        }
      }"
  class="relative space-y-6 max-w-6xl bg-white rounded-xl border border-gray-200 shadow-sm p-6 {{ $computedReadonly ? 'opacity-95' : '' }}">
  @csrf

  {{-- Jika readonly total, blok pointer supaya benar2 tidak bisa edit --}}
  @if($computedReadonly)
  <div class="absolute inset-0 rounded-xl" style="pointer-events: none;"></div>
  @endif

  {{-- Header input: site & tanggal --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Site</label>
      <select name="site_id"
        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400 {{ $computedReadonly ? 'bg-gray-100 text-gray-600 cursor-not-allowed' : '' }}"
        @if($computedReadonly) disabled @endif required>
        @php $printed = false; @endphp
        @foreach($sites as $s)
        @can('daily.manage', $s->id)
        @php $printed = true; @endphp
        <option value="{{ $s->id }}" @selected($selectedSite==$s->id)">
          {{ $s->code }} — {{ $s->name }}
        </option>
        @endcan
        @endforeach

        @unless($printed)
        <option value="">— Tidak ada site yang diizinkan —</option>
        @endunless
      </select>
      @error('site_id') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
      <input type="date" name="date" value="{{ old('date', $date) }}"
        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400 {{ $computedReadonly ? 'bg-gray-100 text-gray-600 cursor-not-allowed' : '' }}"
        @if($computedReadonly) readonly disabled @endif required>
      @error('date') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>
  </div>

  {{-- Per group indikator --}}
  @foreach($groups as $g)
  <div class="mt-8">
    <div class="px-4 py-2 font-semibold bg-maroon-700 text-white rounded-t">
      {{ $g->name }}
    </div>
    <div class="bg-white border-x border-b rounded-b-lg overflow-hidden">
      <table class="min-w-full text-sm">
        <thead class="bg-maroon-50 text-maroon-800">
          <tr>
            <th class="px-3 py-2 text-left w-10">#</th>
            <th class="px-3 py-2 text-left">Indicator</th>
            <th class="px-3 py-2 text-left w-44">Value</th>
            <th class="px-3 py-2 text-left w-40">Threshold</th>
            <th class="px-3 py-2 text-left">Unit/Note</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($g->indicators as $i)
          @php
          // --- Penentuan step & inputmode dasar dari data_type ---
          $step = match($i->data_type) {
          'int' => '1',
          'currency' => '0.01',
          'rate' => '0.0001',
          'decimal' => '0.0001',
          default => '0.0001',
          };
          $inputMode = $i->data_type === 'int' ? 'numeric' : 'decimal';

          // --- Jika unit adalah persen, force desimal & range 0..100 ---
          $isPercent = trim((string) $i->unit) === '%';
          $min = null; $max = null;
          if ($isPercent) {
          $step = '0.01';
          $inputMode = 'decimal';
          $min = '0';
          $max = '100';
          }

          $disabledClass = ($computedReadonly || $i->is_derived) ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '';
          @endphp

          <tr class="hover:bg-gray-50 transition">
            <td class="px-3 py-2 text-gray-700">{{ $i->order_index }}</td>

            <td class="px-3 py-2">
              <div class="font-medium">
                {{ $i->name }}
                @if($i->is_derived)
                <span class="ml-2 px-2 py-0.5 text-[10px] rounded-full bg-gray-100 text-gray-600 border">Derived</span>
                @endif
              </div>
              <div class="text-xs text-gray-500 font-mono">CODE: {{ $i->code }}</div>
            </td>

            <td class="px-3 py-2">
              @if($i->is_derived)
              <input disabled placeholder="Derived"
                class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-500">
              @else
              <input
                name="values[{{ $i->id }}]"
                type="number"
                step="{{ $step }}"
                @if($min !==null) min="{{ $min }}" @endif
                @if($max !==null) max="{{ $max }}" @endif
                inputmode="{{ $inputMode }}"
                value="{{ old("values.$i->id") }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400 {{ $disabledClass }}"
                placeholder="{{ $isPercent ? '0–100' : '0' }}"
                title="{{ $isPercent ? 'Masukkan angka 0–100 (tanpa %). Gunakan titik untuk desimal.' : 'Gunakan titik untuk desimal' }}"
                {{-- normalisasi & kunci minus → 0 --}}
                data-nonneg="1"
                @keydown="keyguard"
                @paste.capture="pasteguard"
                @input="sanitize($event.target)"
                @blur="sanitize($event.target)"
                @if($computedReadonly) readonly disabled @endif>
              @endif
              @error("values.$i->id") <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
            </td>

            {{-- Threshold (view only) --}}
            <td class="px-3 py-2 align-top">
              @php
              $isPercent = trim((string)$i->unit) === '%';
              $val = trim((string)($i->threshold ?? ''));

              // fallback universal → kalau kosong/null/strip, set ke "0"
              if ($val === '' || $val === '-') {
              $val = '0';
              }

              // format tampilan
              $display = $isPercent
              ? rtrim($val, '%') . '%'
              : $val;
              @endphp

              <div class="mt-0.5 font-mono text-sm text-gray-800">{{ $display }}</div>
            </td>

            <td class="px-3 py-2">
              <div class="text-xs text-gray-500">Unit: {{ $i->unit ?? '-' }}</div>
              <input name="notes[{{ $i->id }}]"
                value="{{ old("notes.$i->id") }}"
                class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400 {{ $disabledClass }}"
                placeholder="Catatan (opsional)"
                @if($computedReadonly) readonly disabled @endif>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endforeach

  {{-- Tombol submit --}}
  <div class="{{ $computedReadonly ? 'opacity-60 pointer-events-none' : '' }}">
    <button
      class="mt-6 px-6 py-2.5 rounded-lg bg-maroon-600 hover:bg-maroon-700 text-white shadow disabled:opacity-60 disabled:cursor-not-allowed"
      @if(!$hasAnyAllowedSite || $computedReadonly) disabled @endif>
      Simpan
    </button>
    <a href="{{ route('admin.daily.index') }}"
      class="ml-2 px-6 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700">
      Batal
    </a>
  </div>
</form>
@endsection