@extends('layouts.app')

@section('content')
<div class="p-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">⚠️ HIPO / Nearmiss</h1>
            <p class="text-sm text-gray-500">Daftar laporan HIPO & Nearmiss</p>
        </div>

        <div class="flex items-center gap-3">

            {{-- Search --}}
            <form method="GET" action="" class="relative">
                <input type="text"
                       name="search"
                       placeholder="Cari jobsite / PIC..."
                       class="pl-4 pr-10 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none w-64">
                <svg class="w-4 h-4 absolute right-3 top-3 text-gray-400"
                     fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 21l-4.35-4.35m1.85-5.65a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z"/>
                </svg>
            </form>

            {{-- CREATE BUTTON --}}
            <a href="{{ route('admin.hipo.create') }}"
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow transition duration-200">
                + Tambah HIPO
            </a>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-2xl shadow-md border overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
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
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-blue-100 text-blue-700' }}">
                                {{ $r->jenis_hipo }}
                            </span>
                        </td>

                        {{-- Kategori --}}
                        <td class="py-3 px-3 text-gray-600">
                            {{ $r->category }}
                        </td>

                        {{-- Risk --}}
                        <td class="py-3 px-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @class([
                                    'bg-green-100 text-green-700' => $r->risk_level === 'Low',
                                    'bg-yellow-100 text-yellow-700' => $r->risk_level === 'Medium',
                                    'bg-orange-100 text-orange-700' => $r->risk_level === 'High',
                                    'bg-red-100 text-red-700' => $r->risk_level === 'Extreme',
                                ])">
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
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">
                                    YA
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">
                                    TIDAK
                                </span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @class([
                                    'bg-red-100 text-red-700' => $r->status === 'Open',
                                    'bg-yellow-100 text-yellow-700' => $r->status === 'On Progress',
                                    'bg-green-100 text-green-700' => $r->status === 'Closed',
                                    'bg-gray-200 text-gray-700' => $r->status === 'Rejected',
                                ])">
                                {{ $r->status }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="py-3 px-3 text-center whitespace-nowrap">
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('admin.hipo.show', $r->id) }}"
                                   class="text-blue-600 hover:underline text-sm font-medium">
                                    Detail
                                </a>

                                <form action="{{ route('admin.hipo.destroy', $r->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:underline text-sm font-medium">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-gray-400">
                            Belum ada laporan HIPO / Nearmiss
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
