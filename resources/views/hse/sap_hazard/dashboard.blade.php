<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Hazard Report (SHE-FM-007)</h2>
            <div class="flex gap-2">
                <a href="{{ route('hse.sap.hazard.index') }}" class="px-3 py-1.5 text-xs rounded bg-slate-800 text-white">Daftar Hazard</a>
                <a href="{{ route('hse.sap.hazard.create') }}" class="px-3 py-1.5 text-xs rounded bg-[#0B2345] text-white">Tambah Laporan</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach($kpis as $k)
                    <div class="bg-white border rounded-lg p-3 shadow-sm">
                        <div class="text-[10px] uppercase text-slate-500">{{ $k['label'] }}</div>
                        <div class="text-xl font-bold text-slate-900">{{ $k['value'] }} <span class="text-xs font-normal text-slate-500">{{ $k['unit'] }}</span></div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-2 border-b bg-slate-50 font-semibold text-sm">Hazard Report Terbaru</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left">No Hazard</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Pelapor</th>
                                <th class="px-4 py-2 text-left">Lokasi</th>
                                <th class="px-4 py-2 text-left">Deskripsi</th>
                                <th class="px-4 py-2 text-left">Risk</th>
                                <th class="px-4 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $r)
                            <tr class="border-t hover:bg-slate-50">
                                <td class="px-4 py-2"><a class="text-[#1646A0] font-medium" href="{{ route('hse.sap.hazard.show', $r->id) }}">{{ $r->hazard_no }}</a></td>
                                <td class="px-4 py-2">{{ $r->report_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $r->reporter?->name }}</td>
                                <td class="px-4 py-2">{{ $r->location }}</td>
                                <td class="px-4 py-2">{{ Str::limit($r->hazard_description, 50) }}</td>
                                <td class="px-4 py-2">{{ $r->risk_level }} ({{ $r->risk_score }})</td>
                                <td class="px-4 py-2">{{ $r->status }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-4 text-center text-slate-500">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
