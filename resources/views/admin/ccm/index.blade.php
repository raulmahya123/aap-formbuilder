@extends('layouts.app')

@section('content')
<x-ui.page class="">

    {{-- HEADER --}}
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
            <x-ui.page-title>Critical Control Management</x-ui.page-title>
            <x-ui.page-description>Daftar Laporan Pengendalian Risiko Kritis</x-ui.page-description>
        </div></div>
  <x-ui.page-actions><x-ui.link icon="plus" variant="primary" href="{{ route('ccm-reports.create') }}"
           class="px-5 py-3 rounded-lg text-sm font-semibold shadow-none transition">Input CCM Baru
        </x-ui.link></x-ui.page-actions>
</x-ui.page-header>

    {{-- TABLE --}}
    <x-ui.panel :padded="false" class="bg-white rounded-lg border border-gray-200 shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <x-ui.table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 normal-case text-xs font-bold">
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
                            <span class="px-2 py-1 rounded-lg bg-gray-100 text-gray-700 font-bold text-xs border border-gray-200">
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
                                @if($r->izin_kerja_ada) <span title="Izin Kerja"><x-ui-icon name="file" class="h-5 w-5" /></span> @endif
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
                        <td class="ui-action-cell p-4 text-center"><x-ui.table-actions>
                            <div class="flex items-center justify-center gap-3">
                                <x-ui.icon-button ability="view" :subject="$r" action="view" label="Lihat detail" href="{{ route('ccm-reports.show', $r->id) }}" />

                                <x-ui.icon-button ability="update" :subject="$r" action="edit" label="Ubah data" href="{{ route('ccm-reports.edit', $r->id) }}" />

                                <form action="{{ route('ccm-reports.destroy', $r->id) }}" method="POST" data-confirm="Hapus laporan ini?">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.icon-button ability="delete" :subject="$r" action="delete" label="Hapus laporan" type="submit" />
                                </form>
                            </div>
                        </x-ui.table-actions></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <x-ui-icon name="file" class="mb-2 h-10 w-10 text-[#777777]" />
                                <p>Belum ada data laporan CCM yang tersimpan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>
    </x-ui.panel>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $reports->links() }}
    </div>

</x-ui.page>
@endsection
