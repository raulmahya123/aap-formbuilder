@extends('layouts.app')

@section('content')
<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🧯 Critical Control Management</h1>
            <p class="text-sm text-gray-500">Daftar Laporan Pengendalian Risiko Kritis</p>
        </div>

        <a href="{{ route('ccm-reports.create') }}"
           class="px-5 py-2.5 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition">
          + Input CCM Baru
        </a>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-xs font-bold">
                    <tr>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Jobsite</th>
                        <th class="p-4">Pelapor</th>
                        <th class="p-4 text-center">Kegiatan Kritis</th>
                        <th class="p-4">Ringkasan Pekerjaan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($reports as $r)
                    <tr class="hover:bg-gray-50 transition">
                        
                        {{-- Tanggal --}}
                        <td class="p-4 whitespace-nowrap font-medium text-gray-700">
                            {{ $r->waktu_pelaporan->format('d M Y') }}
                        </td>

                        {{-- Jobsite --}}
                        <td class="p-4">
                            <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 font-bold text-xs border border-gray-200">
                                {{ $r->jobsite }}
                            </span>
                        </td>

                        {{-- Pelapor --}}
                        <td class="p-4 text-gray-600 font-medium">
                            {{ $r->nama_pelapor }}
                        </td>

                        {{-- KEGIATAN KRITIS (ICON) --}}
                        <td class="p-4 text-center text-lg">
                            <div class="flex items-center justify-center space-x-1">
                                @if($r->kendaraan_ada_kegiatan) <span title="Kendaraan">🚚</span> @endif
                                @if($r->izin_kerja_ada) <span title="Izin Kerja">📄</span> @endif
                                @if($r->tebing_ada) <span title="Tebing">⛰️</span> @endif
                                @if($r->air_lumpur_ada) <span title="Air & Lumpur">💧</span> @endif
                                @if($r->chainsaw_ada) <span title="Chainsaw">🪚</span> @endif
                                @if($r->loto_ada) <span title="LOTO">🔒</span> @endif
                                @if($r->lifting_ada) <span title="Lifting">🏗️</span> @endif
                                @if($r->blasting_ada) <span title="Blasting">💥</span> @endif
                                @if($r->kritis_baru_ada) <span title="Kritis Baru">🆕</span> @endif

                                @if(!($r->kendaraan_ada_kegiatan || $r->izin_kerja_ada || $r->tebing_ada || $r->air_lumpur_ada || $r->chainsaw_ada || $r->loto_ada || $r->lifting_ada || $r->blasting_ada || $r->kritis_baru_ada))
                                    <span class="text-[10px] text-gray-400 italic">N/A</span>
                                @endif
                            </div>
                        </td>

                        {{-- RINGKASAN PEKERJAAN --}}
                        <td class="p-4 text-xs text-gray-600 max-w-xs">
                            @php
                                // Karena kolom _ringkasan tidak ada di DB, kita ambil dari pekerjaan_kritis
                                $ringkasan = collect([
                                    $r->kendaraan_pekerjaan_kritis,
                                    $r->izin_kerja_pekerjaan_kritis,
                                    $r->tebing_pekerjaan_kritis,
                                    $r->air_lumpur_pekerjaan_kritis,
                                    $r->chainsaw_pekerjaan_kritis,
                                    $r->loto_pekerjaan_kritis,
                                    $r->lifting_pekerjaan_kritis,
                                    $r->blasting_pekerjaan_kritis,
                                    $r->kritis_baru_pekerjaan,
                                ])->filter()->implode(', ');
                            @endphp
                            {{ Str::limit($ringkasan ?: 'Tidak ada deskripsi pekerjaan', 100) }}
                        </td>

                        {{-- AKSI --}}
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('ccm-reports.show', $r->id) }}" 
                                   class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-tighter" title="Lihat Detail">
                                    Detail
                                </a>
                                
                                <a href="{{ route('ccm-reports.edit', $r->id) }}" 
                                   class="text-amber-600 hover:text-amber-800 font-bold text-xs uppercase tracking-tighter" title="Ubah Data">
                                    Edit
                                </a>

                                <form action="{{ route('ccm-reports.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus laporan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-xs uppercase tracking-tighter" title="Hapus Laporan">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-10 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-2">📁</span>
                                <p>Belum ada data laporan CCM yang tersimpan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $reports->links() }}
    </div>

</div>
@endsection