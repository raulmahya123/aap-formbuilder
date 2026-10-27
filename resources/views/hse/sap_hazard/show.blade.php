<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Hazard {{ $hazard->hazard_no }} - {{ $hazard->status }}</h2>
            <div class="flex gap-2">
                @if(in_array($hazard->status, ['Draft','Returned','Revised']))
                <a href="{{ route('hse.sap.hazard.edit', $hazard->id) }}" class="px-3 py-1.5 text-xs rounded bg-amber-100">Edit</a>
                @endif
                <a href="{{ route('hse.sap.hazard.index') }}" class="px-3 py-1.5 text-xs rounded bg-slate-200">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white border rounded-lg p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><div class="text-xs text-slate-500">No Hazard</div><div class="font-medium">{{ $hazard->hazard_no }}</div></div>
                <div><div class="text-xs text-slate-500">Dokumen</div><div class="font-medium">{{ $hazard->document_code }}</div></div>
                <div><div class="text-xs text-slate-500">Pelapor</div><div class="font-medium">{{ $hazard->reporter?->name }}</div></div>
                <div><div class="text-xs text-slate-500">Status</div><div class="font-medium">{{ $hazard->status }}</div></div>
                <div><div class="text-xs text-slate-500">Lokasi</div><div class="font-medium">{{ $hazard->location }}</div></div>
                <div><div class="text-xs text-slate-500">Kategori</div><div class="font-medium">{{ $hazard->hazard_type }}</div></div>
                <div><div class="text-xs text-slate-500">Risk Level</div><div class="font-medium">{{ $hazard->risk_level }}</div></div>
                <div><div class="text-xs text-slate-500">Risk Score</div><div class="font-medium">{{ $hazard->risk_score }}</div></div>
                <div class="md:col-span-4"><div class="text-xs text-slate-500">Deskripsi</div><div class="font-medium">{{ $hazard->hazard_description }}</div></div>
            </div>

            @if(in_array($hazard->status, ['Submitted','SHE Review']))
            <div class="bg-white border rounded-lg p-4">
                <div class="font-semibold text-sm mb-2">Review SHE</div>
                <form method="POST" action="{{ route('hse.sap.hazard.review', $hazard->id) }}" class="space-y-3 text-sm">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-600">Tindakan</label>
                            <select name="action" class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="approve">Lanjut Review / Terima</option>
                                <option value="assign">Tugaskan ke PIC</option>
                                <option value="return">Kembalikan</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-slate-600">PIC (jika ditugaskan)</label>
                            <select name="assigned_pic_id" class="w-full mt-1 border rounded px-2 py-1.5">
                                <option value="">- Pilih PIC -</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-slate-600">Catatan Review</label>
                        <textarea name="review_notes" class="w-full mt-1 border rounded px-2 py-1.5" rows="2"></textarea>
                    </div>
                    <button class="px-4 py-2 rounded bg-[#0B2345] text-white text-sm">Proses</button>
                </form>
            </div>
            @endif

            @if(in_array($hazard->status, ['Assigned','In Progress','Reopened']))
            <div class="bg-white border rounded-lg p-4">
                <div class="font-semibold text-sm mb-2">Tindakan Perbaikan (Corrective Action)</div>
                <form method="POST" action="{{ route('hse.sap.hazard.ca.store', $hazard->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="text-xs text-slate-600">Deskripsi Tindakan Perbaikan</label>
                        <input type="text" name="corrective_action" required class="w-full mt-1 border rounded px-2 py-1.5">
                    </div>
                    <div>
                        <label class="text-xs text-slate-600">PIC</label>
                        <select name="pic_id" required class="w-full mt-1 border rounded px-2 py-1.5">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-600">Target Selesai</label>
                        <input type="date" name="target_completion_date" class="w-full mt-1 border rounded px-2 py-1.5">
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <button class="px-4 py-2 rounded bg-slate-700 text-white text-sm">Tambah Tindakan</button>
                    </div>
                </form>

                @if($hazard->correctiveActions->count())
                <div class="mt-4 divide-y border rounded">
                    @foreach($hazard->correctiveActions as $ca)
                    <div class="p-3 text-sm">
                        <div class="flex justify-between">
                            <div>
                                <div class="font-medium">{{ $ca->corrective_action }}</div>
                                <div class="text-xs text-slate-500">PIC: {{ $ca->pic?->name }} | Target: {{ $ca->target_completion_date?->format('d/m/Y') }} | Status: {{ $ca->status }}</div>
                            </div>
                            <div class="flex gap-2">
                                @if($ca->status === 'Assigned' || $ca->status === 'In Progress' || $ca->status === 'Rejected')
                                <form method="POST" action="{{ route('hse.sap.hazard.ca.submit', $ca->id) }}">
                                    @csrf
                                    <button class="text-xs px-2 py-1 rounded bg-amber-100">Ajukan Verifikasi</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            @if($hazard->status === 'Pending Verification')
            <div class="bg-white border rounded-lg p-4">
                <div class="font-semibold text-sm mb-2">Verifikasi Tindakan Perbaikan (SHE)</div>
                @foreach($hazard->correctiveActions as $ca)
                @if($ca->status === 'Pending Verification')
                <form method="POST" action="{{ route('hse.sap.hazard.ca.verify', $ca->id) }}" class="border rounded p-3 mb-2 space-y-2 text-sm">
                    @csrf
                    <div><span class="font-medium">{{ $ca->corrective_action }}</span> - PIC: {{ $ca->pic?->name }}</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <select name="action" class="border rounded px-2 py-1.5">
                            <option value="verify">Verifikasi Saja</option>
                            <option value="close">Verifikasi & Tutup Hazard</option>
                            <option value="reject">Tolak & Kembalikan</option>
                        </select>
                        <input type="text" name="verification_notes" placeholder="Catatan Verifikasi" class="md:col-span-2 border rounded px-2 py-1.5">
                    </div>
                    <button class="px-3 py-1.5 rounded bg-green-700 text-white text-xs">Proses Verifikasi</button>
                </form>
                @endif
                @endforeach
            </div>
            @endif

            <div class="bg-white border rounded-lg p-4">
                <div class="font-semibold text-sm mb-2">Riwayat Approval</div>
                <div class="space-y-2 text-sm">
                    @forelse($hazard->approvals as $a)
                    <div class="border rounded p-2 flex justify-between"><div><span class="font-medium">{{ $a->action }}</span> oleh {{ $a->actor?->name }} - {{ $a->comments }}</div><div class="text-xs text-slate-500">{{ $a->created_at }}</div></div>
                    @empty
                    <div class="text-slate-500 text-xs">Belum ada riwayat</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
