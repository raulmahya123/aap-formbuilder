<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Daftar PTO</h2>
            <a href="{{ route('hse.dashboard.sap.pto.create') }}" class="px-3 py-1.5 text-xs rounded bg-[#0B2345] text-white">Tambah PTO</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-left">No PTO</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                                <th class="px-4 py-2 text-left">Site</th>
                                <th class="px-4 py-2 text-left">Pengamat</th>
                                <th class="px-4 py-2 text-left">Diamati</th>
                                <th class="px-4 py-2 text-left">Pekerjaan</th>
                                <th class="px-4 py-2 text-left">Risky</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ptos as $p)
                            <tr class="border-t hover:bg-slate-50">
                                <td class="px-4 py-2 font-medium">{{ $p->pto_no }}</td>
                                <td class="px-4 py-2">{{ $p->observation_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-2">{{ $p->site?->name }}</td>
                                <td class="px-4 py-2">{{ $p->observer?->name }}</td>
                                <td class="px-4 py-2">{{ $p->observedEmployee?->name }}</td>
                                <td class="px-4 py-2">{{ Str::limit($p->work_activity, 40) }}</td>
                                <td class="px-4 py-2">{{ $p->risky_behaviors_count }}</td>
                                <td class="px-4 py-2">{{ $p->status }}</td>
                                <td class="px-4 py-2 text-right space-x-1">
                                    <a href="{{ route('hse.dashboard.sap.pto.show', $p->id) }}" class="text-xs px-2 py-1 rounded bg-slate-100">Lihat</a>
                                    @if(in_array($p->status, ['Draft','Returned','Revised']))
                                    <a href="{{ route('hse.dashboard.sap.pto.edit', $p->id) }}" class="text-xs px-2 py-1 rounded bg-amber-100">Edit</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="px-4 py-4 text-center text-slate-500">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-2">{{ $ptos->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
