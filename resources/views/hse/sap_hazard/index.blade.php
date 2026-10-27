<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar Hazard Report</h2>
            <a href="{{ route('hse.sap.hazard.create') }}" class="px-3 py-1.5 text-xs rounded bg-[#0B2345] text-white">Tambah Laporan</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left">No Hazard</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Pelapor</th>
                                <th class="px-4 py-2 text-left">Lokasi</th>
                                <th class="px-4 py-2 text-left">Tipe</th>
                                <th class="px-4 py-2 text-left">Risk Level</th>
                                <th class="px-4 py-2 text-left">Score</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hazards as $h)
                            <tr class="border-t hover:bg-slate-50">
                                <td class="px-4 py-2 font-medium">{{ $h->hazard_no }}</td>
                                <td class="px-4 py-2">{{ $h->report_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $h->reporter?->name }}</td>
                                <td class="px-4 py-2">{{ $h->location }}</td>
                                <td class="px-4 py-2">{{ $h->hazard_type }}</td>
                                <td class="px-4 py-2">{{ $h->risk_level }}</td>
                                <td class="px-4 py-2">{{ $h->risk_score }}</td>
                                <td class="px-4 py-2">{{ $h->status }}</td>
                                <td class="px-4 py-2 text-right space-x-1">
                                    <a href="{{ route('hse.sap.hazard.show', $h->id) }}" class="text-xs px-2 py-1 rounded bg-slate-100">Lihat</a>
                                    @if(in_array($h->status, ['Draft','Returned','Revised']))
                                    <a href="{{ route('hse.sap.hazard.edit', $h->id) }}" class="text-xs px-2 py-1 rounded bg-amber-100">Edit</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="px-4 py-4 text-center text-slate-500">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-2">{{ $hazards->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
