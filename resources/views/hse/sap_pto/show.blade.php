<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="ui-page-header__copy"><x-ui.page-title>PTO {{ $pto->pto_no }}</x-ui.page-title><x-ui.page-description>Detail observasi dan status checklist: {{ $pto->status }}.</x-ui.page-description></div>
            <div class="flex flex-wrap items-center gap-2"><x-ui.table-actions><x-ui.sap-record-actions module="pto" :record="$pto" /></x-ui.table-actions>
                @if(in_array($pto->status, ['Draft','Returned','Revised']))
                <x-ui.link variant="primary" icon="pencil" href="{{ route('hse.dashboard.sap.pto.edit', $pto->id) }}">Edit</x-ui.link>
                @endif
                <x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.pto.index') }}">Kembali ke daftar</x-ui.link>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white border rounded-lg p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><div class="text-xs text-slate-500">Nomor PTO</div><div class="font-medium">{{ $pto->pto_no }}</div></div>
                <div><div class="text-xs text-slate-500">Dokumen</div><div class="font-medium">{{ $pto->document_code }}</div></div>
                <div><div class="text-xs text-slate-500">Tanggal</div><div class="font-medium">{{ $pto->observation_date?->format('d/m/Y') }} {{ $pto->observation_time }}</div></div>
                <div><div class="text-xs text-slate-500">Status</div><div class="font-medium">{{ $pto->status }}</div></div>
                <div><div class="text-xs text-slate-500">Pengamat</div><div class="font-medium">{{ $pto->observer?->name }}</div></div>
                <div><div class="text-xs text-slate-500">Yang Diamati</div><div class="font-medium">{{ $pto->observedEmployee?->name }}</div></div>
                <div><div class="text-xs text-slate-500">Site/Dept</div><div class="font-medium">{{ $pto->site?->name }} / {{ $pto->department?->name }}</div></div>
                <div><div class="text-xs text-slate-500">Pekerjaan</div><div class="font-medium">{{ $pto->work_activity }}</div></div>
            </div>

            <div class="bg-white border rounded-lg p-4 grid grid-cols-2 md:grid-cols-6 gap-3 text-sm">
                <div><div class="text-xs text-slate-500">Total Checklist</div><div class="text-lg font-bold">{{ $pto->total_checklist }}</div></div>
                <div><div class="text-xs text-slate-500">Applicable</div><div class="text-lg font-bold">{{ $pto->applicable_checklist }}</div></div>
                <div><div class="text-xs text-slate-500">Safe</div><div class="text-lg font-bold text-green-700">{{ $pto->safe_observations }}</div></div>
                <div><div class="text-xs text-slate-500">Unsafe</div><div class="text-lg font-bold text-red-700">{{ $pto->unsafe_observations }}</div></div>
                <div><div class="text-xs text-slate-500">Perilaku Berisiko</div><div class="text-lg font-bold text-amber-700">{{ $pto->risky_behaviors_count }}</div></div>
                <div><div class="text-xs text-slate-500">Safe Rate</div><div class="text-lg font-bold">{{ $pto->safe_rate ? $pto->safe_rate.'%' : '-' }}</div></div>
            </div>

            @if($pto->findings->count())
            <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                <div class="px-4 py-2 border-b bg-slate-50 font-semibold text-sm">Temuan</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr><th class="px-4 py-2 text-left">No Temuan</th><th class="px-4 py-2 text-left">Judul</th><th class="px-4 py-2 text-left">Risk</th><th class="px-4 py-2 text-left">PIC</th><th class="px-4 py-2 text-left">Status</th></tr>
                    </thead>
                    <tbody>
                        @foreach($pto->findings as $f)
                        <tr class="border-t"><td class="px-4 py-2">{{ $f->finding_no }}</td><td class="px-4 py-2">{{ $f->title }}</td><td class="px-4 py-2">{{ $f->risk_class }}</td><td class="px-4 py-2">{{ $f->pic?->name }}</td><td class="px-4 py-2">{{ $f->status }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if(in_array($pto->status, ['Submitted','Supervisor Review','Resubmitted']))
            <div class="bg-white border rounded-lg p-4">
                <div class="font-semibold text-sm mb-2">Review Supervisor</div>
                <form method="POST" action="{{ route('hse.dashboard.sap.pto.review', $pto->id) }}" class="space-y-3 text-sm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-600">Tindakan</label>
                            <select name="action" class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="approve">Setujui (Approve)</option>
                                <option value="return">Kembalikan (Return)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">Rekomendasi</label>
                            <input type="text" name="recommendation" class="w-full mt-1 border rounded px-2 py-1.5">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-slate-600">Catatan Review</label>
                        <textarea name="review_notes" class="w-full mt-1 border rounded px-2 py-1.5" rows="2"></textarea>
                    </div>
                    <button class="px-4 py-2 rounded bg-[#0B2345] text-white text-sm">Proses Review</button>
                </form>
            </div>
            @endif

            <div class="bg-white border rounded-lg p-4">
                <div class="font-semibold text-sm mb-2">Riwayat Persetujuan</div>
                <div class="space-y-2 text-sm">
                    @forelse($pto->approvals as $a)
                    <div class="border rounded p-2 flex justify-between"><div><span class="font-medium">{{ $a->action }}</span> oleh {{ $a->actor?->name }} - {{ $a->comments }}</div><div class="text-xs text-slate-500">{{ $a->created_at }}</div></div>
                    @empty
                    <div class="text-slate-500 text-xs">Belum ada riwayat</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
