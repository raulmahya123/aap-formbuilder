<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="ui-page-header__copy"><x-ui.page-title>Daftar Hazard Report</x-ui.page-title><x-ui.page-description>Daftar laporan bahaya, tingkat risiko, dan progres tindak lanjut.</x-ui.page-description></div>
            <x-ui.page-actions><x-ui.link variant="primary" icon="plus" href="{{ route('hse.dashboard.sap.hazard.create') }}">Tambah Laporan</x-ui.link></x-ui.page-actions>
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
                                <td class="px-4 py-2 text-right space-x-1"><x-ui.table-actions>
                                    <x-ui.icon-button action="view" label="Lihat" href="{{ route('hse.dashboard.sap.hazard.show', $h->id) }}" />
                                    @if(in_array($h->status, ['Draft','Returned','Revised']))
                                    <x-ui.icon-button action="edit" label="Edit" href="{{ route('hse.dashboard.sap.hazard.edit', $h->id) }}" />
                                    @endif
                                <x-ui.sap-record-actions module="hazard" :record="$h" /></x-ui.table-actions></td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="px-4 py-4 text-center text-slate-500">Belum ada data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="ui-pagination-footer">{{ $hazards->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
