@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-xl font-semibold mb-4">HIPO / Nearmiss</h1>

    <div class="bg-white rounded-xl border p-4">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Tanggal</th>
                    <th class="text-left py-2">Jobsite</th>
                    <th class="text-left py-2">Kategori</th>
                    <th class="text-left py-2">Konsekuensi</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-left py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                <tr class="border-b">
                    <td class="py-2">{{ $r->report_time?->format('d M Y') }}</td>
                    <td class="py-2">{{ $r->jobsite }}</td>
                    <td class="py-2">{{ $r->category }}</td>
                    <td class="py-2">{{ $r->potential_consequence }}</td>
                    <td class="py-2">
                        <span class="px-2 py-1 rounded text-xs
                            {{ $r->status === 'Open' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td class="py-2">
                        <a href="{{ route('admin.hipo.show', $r->id) }}"
                           class="text-maroon-700 hover:underline">
                            Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-gray-500">
                        Belum ada laporan HIPO
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
