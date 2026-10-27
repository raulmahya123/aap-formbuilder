<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Hazard {{ $hazard->hazard_no }} - Penilaian Risiko</h2>
            <div class="flex gap-2">
                <a href="{{ route('hse.sap.hazard.show', $hazard->id) }}" class="px-3 py-1.5 text-xs rounded bg-slate-200">Lihat</a>
                <a href="{{ route('hse.sap.hazard.index') }}" class="px-3 py-1.5 text-xs rounded bg-slate-200">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white border rounded-lg p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><div class="text-xs text-slate-500">Pelapor</div><div class="font-medium">{{ $hazard->reporter?->name }}</div></div>
                <div><div class="text-xs text-slate-500">Lokasi</div><div class="font-medium">{{ $hazard->location }}</div></div>
                <div><div class="text-xs text-slate-500">Kategori</div><div class="font-medium">{{ $hazard->hazard_type }}</div></div>
                <div><div class="text-xs text-slate-500">Status</div><div class="font-medium">{{ $hazard->status }}</div></div>
                <div class="md:col-span-4"><div class="text-xs text-slate-500">Deskripsi Bahaya</div><div class="font-medium">{{ $hazard->hazard_description }}</div></div>
            </div>

            <div class="bg-white border rounded-lg shadow-sm">
                <div class="px-4 py-2 border-b bg-slate-50 font-semibold text-sm">Penilaian Risiko (Probability x Severity)</div>
                <form method="POST" action="{{ route('hse.sap.hazard.assess', $hazard->id) }}" class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    @csrf
                    <div>
                        <label class="text-xs text-slate-600">Probability (1-5)</label>
                        <select name="probability" required class="w-full mt-1 border rounded px-2 py-1.5">
                            @for($i=1;$i<=5;$i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-600">Severity (1-5)</label>
                        <select name="severity" required class="w-full mt-1 border rounded px-2 py-1.5">
                            @for($i=1;$i<=5;$i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 rounded bg-[#0B2345] text-white text-sm">Hitung & Simpan Penilaian</button>
                    </div>
                    <div class="md:col-span-3">
                        <label class="text-xs text-slate-600">Catatan Penilaian</label>
                        <textarea name="risk_notes" class="w-full mt-1 border rounded px-2 py-1.5" rows="2"></textarea>
                    </div>
                </form>
                @if($hazard->risk_score)
                <div class="px-4 pb-4 text-sm">
                    <div class="inline-flex items-center gap-3 px-3 py-2 rounded bg-slate-50 border">
                        <div>Risk Score: <span class="font-bold">{{ $hazard->risk_score }}</span></div>
                        <div>Risk Level: <span class="font-bold">{{ $hazard->risk_level }}</span></div>
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-white border rounded-lg shadow-sm">
                <div class="px-4 py-2 border-b bg-slate-50 font-semibold text-sm">Tindakan Sementara yang Dilakukan</div>
                <form method="POST" action="{{ route('hse.sap.hazard.temp_action', $hazard->id) }}" class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="text-xs text-slate-600">Deskripsi Tindakan Sementara</label>
                        <input type="text" name="description" required class="w-full mt-1 border rounded px-2 py-1.5">
                    </div>
                    <div>
                        <label class="text-xs text-slate-600">Tanggal</label>
                        <input type="date" name="action_date" value="{{ date('Y-m-d') }}" class="w-full mt-1 border rounded px-2 py-1.5">
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 rounded bg-slate-700 text-white text-sm">Tambah</button>
                    </div>
                </form>
                @if($hazard->temporaryActions->count())
                <div class="px-4 pb-4">
                    <ul class="text-sm space-y-1">
                        @foreach($hazard->temporaryActions as $ta)
                        <li class="border rounded px-2 py-1">{{ $ta->action_date?->format('d/m/Y') }} - {{ $ta->description }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <div class="flex justify-end">
                <form method="POST" action="{{ route('hse.sap.hazard.submit', $hazard->id) }}">
                    @csrf
                    <button class="px-4 py-2 rounded bg-green-700 text-white text-sm">Ajukan ke SHE Review</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
