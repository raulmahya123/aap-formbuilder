@extends('layouts.app')

@section('content')
<div class="p-6 space-y-4">

  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-xl font-semibold">🧯 Critical Control Management</h1>

    <a href="{{ route('ccm-reports.create') }}"
       class="px-4 py-2 rounded-lg bg-maroon-700 text-white text-sm">
      + Input CCM
    </a>
  </div>

  {{-- TABLE --}}
  <div class="bg-white rounded-xl border overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-ivory-100 border-b">
        <tr>
          <th class="p-3 text-left">Tanggal</th>
          <th class="p-3 text-left">Jobsite</th>
          <th class="p-3 text-left">Pelapor</th>
          <th class="p-3 text-left">Kegiatan Kritis</th>
          <th class="p-3 text-left">Ringkasan</th>
          <th class="p-3 text-center">Kontrol</th>
          <th class="p-3 text-center">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($reports as $r)
        <tr class="border-t hover:bg-ivory-50 align-top">

          {{-- Tanggal --}}
          <td class="p-3 whitespace-nowrap">
            {{ \Carbon\Carbon::parse($r->waktu_pelaporan)->format('d M Y') }}
          </td>

          {{-- Jobsite --}}
          <td class="p-3 font-medium">{{ $r->jobsite }}</td>

          {{-- Pelapor --}}
          <td class="p-3">{{ $r->nama_pelapor }}</td>

          {{-- KEGIATAN KRITIS --}}
          <td class="p-3 text-lg space-x-1">
            @if($r->kendaraan_ada) 🚚 @endif
            @if($r->izin_kerja_ada) 📄 @endif
            @if($r->tebing_ada) ⛰️ @endif
            @if($r->air_lumpur_ada) 💧 @endif
            @if($r->chainsaw_ada) 🪚 @endif
            @if($r->loto_ada) 🔒 @endif
            @if($r->lifting_ada) 🏗️ @endif
            @if($r->blasting_ada) 💥 @endif
            @if($r->kritis_baru_ada) 🆕 @endif

            @if(
              !$r->kendaraan_ada &&
              !$r->izin_kerja_ada &&
              !$r->tebing_ada &&
              !$r->air_lumpur_ada &&
              !$r->chainsaw_ada &&
              !$r->loto_ada &&
              !$r->lifting_ada &&
              !$r->blasting_ada &&
              !$r->kritis_baru_ada
            )
              <span class="text-xs text-gray-500 italic">
                Tidak ada kegiatan kritis
              </span>
            @endif
          </td>

          {{-- RINGKASAN KEGIATAN --}}
          <td class="p-3 text-xs text-gray-700">
            @php
              $ringkasan = collect([
                $r->kendaraan_ringkasan,
                $r->izin_kerja_ringkasan,
                $r->tebing_ringkasan,
                $r->air_lumpur_ringkasan,
                $r->chainsaw_ringkasan,
                $r->loto_ringkasan,
                $r->lifting_ringkasan,
                $r->blasting_ringkasan,
                $r->kritis_baru_ringkasan,
              ])->filter()->implode(' | ');
            @endphp

            {{ \Illuminate\Support\Str::limit($ringkasan, 80) ?: '-' }}
          </td>

          {{-- KONTROL --}}
          <td class="p-3 text-center text-xs">
            @if(
              $r->kendaraan_engineering || $r->izin_kerja_engineering ||
              $r->tebing_engineering || $r->air_lumpur_engineering ||
              $r->chainsaw_engineering || $r->loto_engineering ||
              $r->lifting_engineering || $r->blasting_engineering ||
              $r->kritis_baru_engineering
            )
              <span class="px-2 py-1 rounded bg-green-100 text-green-700">
                Lengkap
              </span>
            @else
              <span class="px-2 py-1 rounded bg-red-100 text-red-700">
                Belum
              </span>
            @endif
          </td>

          {{-- AKSI --}}
          <td class="p-3 text-center whitespace-nowrap">
            <a href="{{ route('ccm-reports.show', $r->id) }}"
               class="text-blue-600 hover:underline">
              Detail
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="p-6 text-center text-gray-500">
            Belum ada data CCM
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- PAGINATION --}}
  <div>
    {{ $reports->links() }}
  </div>

</div>
@endsection
