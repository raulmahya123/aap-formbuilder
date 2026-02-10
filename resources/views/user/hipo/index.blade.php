@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">⚠️ Laporan HIPO / Nearmiss</h1>

        <a href="{{ route('user.hipo.create') }}"
           class="px-4 py-2 rounded-lg bg-maroon-700 text-white text-sm hover:bg-maroon-800">
            + Buat Laporan
        </a>
    </div>

    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-ivory-100 border-b">
                <tr>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Jobsite</th>
                    <th class="px-4 py-2 text-left">Jenis</th>
                    <th class="px-4 py-2 text-left">Kategori</th>
                    <th class="px-4 py-2 text-left">Risk</th>
                    <th class="px-4 py-2 text-left">KTA</th>
                    <th class="px-4 py-2 text-left">TTA</th>
                    <th class="px-4 py-2 text-left">Konsekuensi</th>
                    <th class="px-4 py-2 text-left">PIC</th>
                    <th class="px-4 py-2 text-center">Stop Work</th>
                    <th class="px-4 py-2 text-center">Status</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($reports as $r)
                <tr class="border-b hover:bg-ivory-50 align-top">

                    {{-- Tanggal --}}
                    <td class="px-4 py-2 whitespace-nowrap">
                        {{ $r->report_time?->format('d M Y') }}
                    </td>

                    {{-- Jobsite --}}
                    <td class="px-4 py-2">
                        {{ $r->jobsite }}
                    </td>

                    {{-- Jenis HIPO --}}
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $r->jenis_hipo === 'HIPO'
                                ? 'bg-red-100 text-red-700'
                                : 'bg-blue-100 text-blue-700' }}">
                            {{ $r->jenis_hipo }}
                        </span>
                    </td>

                    {{-- Kategori --}}
                    <td class="px-4 py-2">
                        {{ $r->category }}
                    </td>

                    {{-- Risk Level --}}
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                            @class([
                                'bg-green-100 text-green-700' => $r->risk_level === 'Low',
                                'bg-yellow-100 text-yellow-700' => $r->risk_level === 'Medium',
                                'bg-orange-100 text-orange-700' => $r->risk_level === 'High',
                                'bg-red-100 text-red-700' => $r->risk_level === 'Extreme',
                            ])">
                            {{ $r->risk_level }}
                        </span>
                    </td>

                    {{-- KTA --}}
                    <td class="px-4 py-2 text-xs text-gray-700">
                        {{ \Illuminate\Support\Str::limit($r->kta, 50) }}
                    </td>

                    {{-- TTA --}}
                    <td class="px-4 py-2 text-xs text-gray-700">
                        {{ \Illuminate\Support\Str::limit($r->tta, 50) }}
                    </td>

                    {{-- Konsekuensi --}}
                    <td class="px-4 py-2">
                        {{ $r->potential_consequence }}
                    </td>

                    {{-- PIC --}}
                    <td class="px-4 py-2">
                        {{ $r->pic }}
                    </td>

                    {{-- Stop Work --}}
                    <td class="px-4 py-2 text-center">
                        @if($r->stop_work)
                            <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700">
                                Ya
                            </span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-600">
                                Tidak
                            </span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-1 rounded text-xs
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
                    <td class="px-4 py-2 text-center whitespace-nowrap">
                        <a href="{{ route('admin.hipo.show', $r->id) }}"
                           class="text-blue-600 hover:underline text-sm">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12"
                        class="px-4 py-6 text-center text-coal-500">
                        Belum ada laporan HIPO / Nearmiss
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
