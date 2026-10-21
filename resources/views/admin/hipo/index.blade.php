@extends('layouts.app')

@section('content')
<x-ui.page class="">

    {{-- HEADER --}}
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
            <x-ui.page-title>HIPO / Nearmiss</x-ui.page-title>
            <x-ui.page-description>Daftar laporan HIPO & Nearmiss</x-ui.page-description>
        </div></div>
  <x-ui.page-actions><div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">

            {{-- Search --}}


            {{-- CREATE BUTTON --}}
            <x-ui.link icon="plus" variant="primary" href="{{ route('admin.hipo.create') }}"
               class="inline-flex justify-center px-4 py-2 rounded-lg text-sm font-semibold shadow-none transition duration-200">Tambah HIPO
            </x-ui.link>
        </div></x-ui.page-actions>
</x-ui.page-header>
<div class="ui-page-filters"><form method="GET" action="" class="relative w-full sm:w-auto">
                <input type="text"
                       name="search"
                       placeholder="Cari jobsite / PIC..."
                       class="w-full pl-4 pr-8 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none sm:w-64">
                <svg class="w-4 h-4 absolute right-3 top-3 text-gray-400"
                     fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-4.35-4.35m1.85-5.65a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
                </svg>
            </form></div>

    {{-- TABLE CARD --}}
    <x-ui.panel :padded="false" class="bg-white rounded-lg shadow-none border overflow-hidden">

        <div class="overflow-x-auto">
            <x-ui.table class="w-full min-w-[760px] text-sm">
                <thead class="bg-gray-100 text-gray-700 normal-case text-xs">
                    <tr>
                        <th class="text-left py-3 px-3">Tanggal</th>
                        <th class="text-left py-3 px-3">Jobsite</th>
                        <th class="text-left py-3 px-3">Jenis</th>
                        <th class="text-left py-3 px-3">Kategori</th>
                        <th class="text-left py-3 px-3">Risk</th>
                        <th class="text-left py-3 px-3">PIC</th>
                        <th class="text-center py-3 px-3">Stop Work</th>
                        <th class="text-center py-3 px-3">Status</th>
                        <th class="text-center py-3 px-3">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reports as $r)
                    <tr class="border-t hover:bg-gray-50 transition">

                        {{-- Tanggal --}}
                        <td class="py-3 px-3 whitespace-nowrap">
                            {{ $r->report_time?->format('d M Y') }}
                        </td>

                        {{-- Jobsite --}}
                        <td class="py-3 px-3 font-medium text-gray-700">
                            {{ $r->jobsite }}
                        </td>

                        {{-- Jenis --}}
                        <td class="py-3 px-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $r->jenis_hipo === 'HIPO'
                                    ? 'ui-status ui-status--danger'
                                    : 'ui-status ui-status--info' }}">
                                {{ $r->jenis_hipo }}
                            </span>
                        </td>

                        {{-- Kategori --}}
                        <td class="py-3 px-3 text-gray-600">
                            {{ $r->category }}
                        </td>

                        {{-- Risk --}}
                        <td class="py-3 px-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold @class([ 'ui-status ui-status--success' => $r->risk_level === 'Low', 'ui-status ui-status--warning' => $r->risk_level === 'Medium', 'ui-status ui-status--warning' => $r->risk_level === 'High', 'ui-status ui-status--danger' => $r->risk_level === 'Extreme', ])">
                                {{ $r->risk_level }}
                            </span>
                        </td>

                        {{-- PIC --}}
                        <td class="py-3 px-3 text-gray-600">
                            {{ $r->pic ?? '-' }}
                        </td>

                        {{-- Stop Work --}}
                        <td class="py-3 px-3 text-center">
                            @if($r->stop_work)
                                <x-ui.badge variant="danger" class="px-2 py-1 rounded-full text-xs">
                                    YA
                                </x-ui.badge>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                                    TIDAK
                                </span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold @class([ 'ui-status ui-status--danger' => $r->status === 'Open', 'ui-status ui-status--warning' => $r->status === 'On Progress', 'ui-status ui-status--success' => $r->status === 'Closed', 'bg-gray-200 text-gray-700' => $r->status === 'Rejected', ])">
                                {{ $r->status }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="ui-action-cell py-3 px-3 text-center whitespace-nowrap"><x-ui.table-actions>
                            <div class="flex justify-center gap-3">
                                <x-ui.icon-button action="view" label="Lihat detail" href="{{ route('admin.hipo.show', $r->id) }}" />

                                <form action="{{ route('admin.hipo.destroy', $r->id) }}"
                                      method="POST"
                                      data-confirm="Yakin ingin hapus data ini?">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.icon-button action="delete" label="Hapus" type="submit" />
                                </form>
                            </div>
                        </x-ui.table-actions></td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-gray-400">
                            Belum ada laporan HIPO / Nearmiss
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        @if($reports->hasPages())
          <div class="border-t border-gray-100 px-4 py-4">{{ $reports->withQueryString()->links() }}</div>
        @endif

    </x-ui.panel>
</x-ui.page>
@endsection
