<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="ui-page-header__copy"><x-ui.page-title>Daftar PTO</x-ui.page-title><x-ui.page-description>Daftar observasi pekerjaan dan checklist Planned Task Observation.</x-ui.page-description></div>
            <x-ui.page-actions><x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.pto.create') }}">Tambah PTO</x-ui.link></x-ui.page-actions>
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
                                <td class="px-4 py-2 text-right space-x-1"><x-ui.table-actions>
                                    <x-ui.icon-button action="view" label="Lihat" href="{{ route('hse.dashboard.sap.pto.show', $p->id) }}" />
                                    @if(in_array($p->status, ['Draft','Returned','Revised']))
                                    <x-ui.icon-button action="edit" label="Edit" href="{{ route('hse.dashboard.sap.pto.edit', $p->id) }}" />
                                    @endif
                                <x-ui.sap-record-actions module="pto" :record="$p" /></x-ui.table-actions></td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="px-4 py-4 text-center text-slate-500">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="ui-pagination-footer">{{ $ptos->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
