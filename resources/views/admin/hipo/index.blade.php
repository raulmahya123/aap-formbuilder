@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-xl font-semibold mb-4">⚠️ HIPO / Nearmiss</h1>

    <div class="bg-white rounded-xl border p-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="text-left py-2 px-2">Tanggal</th>
                    <th class="text-left py-2 px-2">Jobsite</th>
                    <th class="text-left py-2 px-2">Kategori</th>
                    <th class="text-left py-2 px-2">Risk</th>
                    <th class="text-left py-2 px-2">Konsekuensi</th>
                    <th class="text-left py-2 px-2">PIC</th>
                    <th class="text-center py-2 px-2">Stop Work</th>
                    <th class="text-center py-2 px-2">Status</th>
                    <th class="text-center py-2 px-2">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($reports as $r)
                <tr class="border-b hover:bg-gray-50">

                    {{-- Tanggal --}}
                    <td class="py-2 px-2">
                        {{ $r->report_time?->format('d M Y') }}
                    </td>

                    {{-- Jobsite --}}
                    <td class="py-2 px-2">
                        {{ $r->jobsite }}
                    </td>

                    {{-- Kategori --}}
                    <td class="py-2 px-2">
                        {{ $r->category }}
                    </td>

                    {{-- Risk Level --}}
                    <td class="py-2 px-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            @class([
                                'bg-green-100 text-green-700' => $r->risk_level === 'Low',
                                'bg-yellow-100 text-yellow-700' => $r->risk_level === 'Medium',
                                'bg-orange-100 text-orange-700' => $r->risk_level === 'High',
                                'bg-red-100 text-red-700' => $r->risk_level === 'Extreme',
                            ])">
                            {{ $r->risk_level }}
                        </span>
                    </td>

                    {{-- Konsekuensi --}}
                    <td class="py-2 px-2">
                        {{ $r->potential_consequence }}
                    </td>

                    {{-- PIC --}}
                    <td class="py-2 px-2">
                        {{ $r->pic ?? '-' }}
                    </td>

                    {{-- Stop Work --}}
                    <td class="py-2 px-2 text-center">
                        @if($r->stop_work)
                            <span class="px-2 py-1 rounded text-xs bg-red-100 text-red-700">
                                YA
                            </span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-600">
                                TIDAK
                            </span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="py-2 px-2 text-center">
                        <span class="px-2 py-1 rounded text-xs font-semibold
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
                    <td class="py-2 px-2 text-center">
                        <a href="{{ route('admin.hipo.show', $r->id) }}"
                           class="text-maroon-700 hover:underline font-medium">
                            Detail
                        </a>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-6 text-center text-gray-500">
                        Belum ada laporan HIPO
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
