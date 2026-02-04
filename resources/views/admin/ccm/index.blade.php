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
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-ivory-100">
        <tr>
          <th class="p-3 text-left">Tanggal</th>
          <th class="p-3 text-left">Jobsite</th>
          <th class="p-3 text-left">Pelapor</th>
          <th class="p-3 text-left">Ringkasan Kegiatan</th>
          <th class="p-3 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($reports as $r)
          <tr class="border-t hover:bg-ivory-50">
            <td class="p-3">
              {{ \Carbon\Carbon::parse($r->waktu_pelaporan)->format('d M Y') }}
            </td>

            <td class="p-3 font-medium">{{ $r->jobsite }}</td>

            <td class="p-3">{{ $r->nama_pelapor }}</td>

            {{-- RINGKASAN --}}
            <td class="p-3 space-x-1 text-lg">
              @if($r->kendaraan_ada_kegiatan) 🚚 @endif
              @if($r->izin_kerja_ada) 📄 @endif
              @if($r->tebing_ada) ⛰️ @endif
              @if($r->air_lumpur_ada) 💧 @endif
              @if($r->chainsaw_ada) 🪚 @endif
              @if($r->loto_ada) 🔒 @endif
              @if($r->lifting_ada) 🏗️ @endif
              @if($r->blasting_ada) 💥 @endif
              @if($r->kritis_baru_ada) 🆕 @endif

              @if(
                !$r->kendaraan_ada_kegiatan &&
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

            {{-- AKSI --}}
            <td class="p-3">
              <a href="{{ route('ccm-reports.show', $r->id) }}"
                 class="text-blue-600 hover:underline">
                Detail
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="p-6 text-center text-gray-500">
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
