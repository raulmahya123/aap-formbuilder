@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold">⚠️ Laporan HIPO</h1>

        <a href="{{ route('user.hipo.create') }}"
           class="px-4 py-2 rounded-lg bg-maroon-700 text-white text-sm hover:bg-maroon-800">
            + Buat Laporan
        </a>
    </div>

    <div class="bg-white border rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-ivory-100 border-b">
                <tr>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Jobsite</th>
                    <th class="px-4 py-2 text-left">Kategori</th>
                    <th class="px-4 py-2 text-left">Konsekuensi</th>
                    <th class="px-4 py-2 text-left">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                <tr class="border-b hover:bg-ivory-50">
                    <td class="px-4 py-2">{{ $r->report_time?->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ $r->jobsite }}</td>
                    <td class="px-4 py-2">{{ $r->category }}</td>
                    <td class="px-4 py-2">{{ $r->potential_consequence }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $r->status === 'Open'
                                ? 'bg-red-100 text-red-700'
                                : 'bg-green-100 text-green-700' }}">
                            {{ $r->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-coal-500">
                        Belum ada laporan HIPO
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
