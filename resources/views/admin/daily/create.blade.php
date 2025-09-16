@extends('layouts.app')

@section('title','Input Harian')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-maroon-700">Input Harian Per Site</h1>

@php
  // dari controller: $sites (sudah terfilter), $date, $groups
  $selectedSite = old('site_id', request('site_id'));
  $hasAnyAllowedSite = $sites->filter(fn($s) => auth()->user()?->can('daily.manage', $s->id))->count() > 0;
@endphp

@if(!$hasAnyAllowedSite)
  <div class="max-w-6xl mb-6 p-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-900">
    Kamu belum memiliki akses ke site mana pun untuk input harian.
    Hubungi admin untuk diberikan akses site.
  </div>
@endif

<form action="{{ route('admin.daily.store') }}" method="post"
      class="space-y-6 max-w-6xl bg-white rounded-xl border border-gray-200 shadow-sm p-6">
  @csrf

  {{-- Header input: site & tanggal --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Site</label>
      <select name="site_id"
              class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
              required>
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
      @error('site_id') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
      <input type="date" name="date" value="{{ old('date', $date) }}"
             class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
             required>
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
              <th class="px-3 py-2 text-left w-40">Threshold</th> {{-- <- pindah di sini, sebelah value --}}
              <th class="px-3 py-2 text-left">Unit/Note</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($g->indicators as $i)
              @php
                // Tentukan step sesuai tipe data
                $step = match($i->data_type) {
                  'int'      => '1',
                  'currency' => '0.01',
                  'rate'     => '0.0001',
                  default    => '0.0001', // decimal
                };
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
                      value="{{ old("values.$i->id") }}"
                      class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
                      placeholder="0">
                  @endif
                  @error("values.$i->id") <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
                </td>

                {{-- Kolom Threshold (view only) --}}
                <td class="px-3 py-2 align-top">
                  <div class="mt-0.5 font-mono text-sm text-gray-800">
                    {{ $i->threshold !== null && $i->threshold !== '' ? $i->threshold : '—' }}
                  </div>
                </td>

                <td class="px-3 py-2">
                  <div class="text-xs text-gray-500">Unit: {{ $i->unit ?? '-' }}</div>
                  <input name="notes[{{ $i->id }}]"
                         value="{{ old("notes.$i->id") }}"
                         class="mt-1 w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-maroon-400 focus:border-maroon-400"
                         placeholder="Catatan (opsional)">
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach

  {{-- Tombol submit --}}
  <div>
    <button
      class="mt-6 px-6 py-2.5 rounded-lg bg-maroon-600 hover:bg-maroon-700 text-white shadow disabled:opacity-60 disabled:cursor-not-allowed"
      @if(!$hasAnyAllowedSite) disabled @endif>
      Simpan
    </button>
    <a href="{{ route('admin.daily.index') }}"
       class="ml-2 px-6 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700">
      Batal
    </a>
  </div>
</form>
@endsection
