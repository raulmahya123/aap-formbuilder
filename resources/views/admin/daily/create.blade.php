@extends('layouts.app')

@section('title','Input Harian')

@section('content')
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
<x-ui.page-header>
  <div class="ui-page-header__copy">
    <x-ui.page-title>Input Harian Per Site</x-ui.page-title>
    <x-ui.page-description>Lengkapi data indikator harian sesuai shift dan site operasional.</x-ui.page-description>
  </div>
  <x-ui.page-actions>
    <x-ui.button form="page-primary-form" variant="primary"

      :disabled="!$hasAnyAllowedSite || $computedReadonly">
      <x-ui.lucide name="save" /> Simpan Data
    </x-ui.button>
  </x-ui.page-actions>
</x-ui.page-header>



@if($computedReadonly)
<div class="w-full min-w-0 mb-4 p-3 rounded-lg border border-blue-200 bg-blue-50 text-blue-900 text-sm">
  Mode tampilan saja. Kamu tidak dapat mengubah data pada halaman ini.
</div>
@endif

@if(!$hasAnyAllowedSite)
<div class="w-full min-w-0 mb-6 p-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-900">
  Kamu belum memiliki akses ke site mana pun untuk input harian.
  Hubungi admin untuk diberikan akses site.
</div>
@endif

<form id="page-primary-form" action="{{ route('admin.daily.store') }}" method="post"
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
  class="w-full min-w-0 ui-card relative">
  @csrf

  {{-- Jika readonly total, blok pointer supaya benar2 tidak bisa edit --}}
  @if($computedReadonly)
  <div class="absolute inset-0 rounded-lg" style="pointer-events: none;"></div>
  @endif

  {{-- Header input: site, tanggal, & shift info --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
    <div>
      <x-ui.form-label for="daily-site-id" required>Site Operasional</x-ui.form-label>
      <select id="daily-site-id" name="site_id"
        class="w-full ui-input"
        @if($computedReadonly) disabled @endif required>
        @php $printed = false; @endphp
        @foreach($sites as $s)
        @can('daily.manage', $s->id)
        @php $printed = true; @endphp
        <option value="{{ $s->id }}" @selected($selectedSite == $s->id)>
          {{ $s->code }} — {{ $s->name }}
        </option>
        @endcan
        @endforeach

        @unless($printed)
        <option value="">— Tidak ada site yang diizinkan —</option>
        @endunless
      </select>
      @error('site_id') <x-ui.form-error>{{ $message }}</x-ui.form-error> @enderror
    </div>

    <div>
      <x-ui.form-label for="daily-date" required>Tanggal Input</x-ui.form-label>
      <input type="date" id="daily-date" name="date" value="{{ old('date', $date) }}"
        class="w-full ui-input"
        @if($computedReadonly) readonly disabled @endif required>
      @error('date') <x-ui.form-error>{{ $message }}</x-ui.form-error> @enderror
    </div>

    <div>
      <x-ui.form-label>Shift & Status Input</x-ui.form-label>
      <div class="p-2.5 rounded-lg border {{ !empty($showLateWatermark) ? 'bg-amber-50/70 border-amber-200' : 'bg-emerald-50/70 border-emerald-200' }} flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-md flex items-center justify-center {{ !empty($showLateWatermark) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
            <x-ui.lucide name="{{ !empty($showLateWatermark) ? 'clock' : 'circle-check' }}" class="w-4 h-4" />
          </div>
          <div>
            <div class="text-xs font-bold {{ !empty($showLateWatermark) ? 'text-amber-950' : 'text-emerald-950' }}">
              Shift {{ $currentShift ?? '1' }}
              @if(isset($shiftRanges) && isset($shiftRanges[$currentShift ?? 1]))
                <span class="text-[11px] font-normal text-gray-500 font-mono">({{ $shiftRanges[$currentShift ?? 1][0]->format('H:i') }} - {{ $shiftRanges[$currentShift ?? 1][1]->format('H:i') }})</span>
              @endif
            </div>
            <div class="text-[10px] text-gray-500">
              Otomatis terdeteksi sistem
            </div>
          </div>
        </div>
        <div>
          @if(!empty($showLateWatermark))
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-200/90 text-amber-900 border border-amber-300">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
              Late Input
            </span>
          @else
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-200/90 text-emerald-900 border border-emerald-300">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
              On Time
            </span>
          @endif
        </div>
      </div>
    </div>
  </div>

  @if(!empty($showLateWatermark))
  <div class="flex items-start gap-3 p-3 rounded-lg border border-amber-200 bg-amber-50/80 text-amber-900 text-xs">
    <x-ui.lucide name="alert-triangle" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" />
    <div class="leading-relaxed">
      <span class="font-bold">Input Tercatat Terlambat (Late Input):</span>
      Waktu pengisian berada di luar batas normal jendela shift. Data tetap berhasil disimpan dan otomatis ditandai keterlambatan untuk transparansi pelaporan.
    </div>
  </div>
  @endif

  {{-- Per group indikator --}}
  @foreach($groups as $g)
  <div class="mt-8">
    <div class="px-4 py-2 font-semibold bg-maroon-700 text-white rounded-t">
      {{ $g->name }}
    </div>
    <x-ui.panel :padded="false" class="overflow-hidden">
      <x-ui.table class="min-w-full text-sm">
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
                <span class="ml-2 px-2 py-1 text-[10px] rounded-full bg-gray-100 text-gray-600 border">Derived</span>
                @endif
              </div>
              <div class="text-xs text-gray-500 font-mono">CODE: {{ $i->code }}</div>
            </td>

            <td class="px-3 py-2">
              @if($i->is_derived)
              <input disabled placeholder="Derived"
                class="w-full ui-input">
              @else
              <input
                name="values[{{ $i->id }}]"
                type="number"
                step="{{ $step }}"
                @if($min !==null) min="{{ $min }}" @endif
                @if($max !==null) max="{{ $max }}" @endif
                inputmode="{{ $inputMode }}"
                value="{{ old("values.$i->id") }}"
                class="w-full {{ $disabledClass }} ui-input"
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

              <div class="mt-1 font-mono text-sm text-gray-800">{{ $display }}</div>
            </td>

            <td class="px-3 py-2">
              <div class="text-xs text-gray-500">Unit: {{ $i->unit ?? '-' }}</div>
              <input name="notes[{{ $i->id }}]"
                value="{{ old("notes.$i->id") }}"
                class="w-full {{ $disabledClass }} ui-input"
                placeholder="Catatan (opsional)"
                @if($computedReadonly) readonly disabled @endif>
            </td>
          </tr>
          @endforeach
        </tbody>
      </x-ui.table>
    </x-ui.panel>
  </div>
  @endforeach

  {{-- Tombol submit --}}
  <div class="{{ $computedReadonly ? 'opacity-60 pointer-events-none' : '' }}">

    <x-ui.link variant="secondary" href="{{ route('admin.daily.index') }}"
      >
      Batal
    </x-ui.link>
  </div>
</form>
@endsection
