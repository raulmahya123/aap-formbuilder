@extends('layouts.app')
@section('title', 'Edit Hazard '.$hazard->hazard_no)
@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy"><x-ui.page-title>Edit Hazard {{ $hazard->hazard_no }}</x-ui.page-title><x-ui.page-description>Perbarui penilaian risiko dan informasi laporan bahaya.</x-ui.page-description></div>
        <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.hazard.show', $hazard->id) }}">Lihat</x-ui.link><x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.hazard.index') }}">Kembali ke daftar</x-ui.link></x-ui.page-actions>
    </x-ui.page-header>

    <div class="w-full max-w-4xl space-y-4">
            <div class="bg-white border rounded-lg p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><div class="text-xs text-slate-500">Pelapor</div><div class="font-medium">{{ $hazard->reporter?->name }}</div></div>
                <div><div class="text-xs text-slate-500">Lokasi</div><div class="font-medium">{{ $hazard->location }}</div></div>
                <div><div class="text-xs text-slate-500">Kategori</div><div class="font-medium">{{ $hazard->hazard_type }}</div></div>
                <div><div class="text-xs text-slate-500">Status</div><div class="font-medium">{{ $hazard->status }}</div></div>
                <div class="md:col-span-4"><div class="text-xs text-slate-500">Deskripsi Bahaya</div><div class="font-medium">{{ $hazard->hazard_description }}</div></div>
            </div>

            <div class="bg-white border rounded-lg shadow-sm">
                <div class="px-4 py-2 border-b bg-slate-50 font-semibold text-sm">Penilaian Risiko (Probability x Severity)</div>
                <form method="POST" action="{{ route('hse.dashboard.sap.hazard.assess', $hazard->id) }}" class="grid grid-cols-1 md:grid-cols-3">
                    @csrf
                    <div>
                        <label >Probability (1-5)</label>
                        <select name="probability" required class="w-full ui-input">
                            @for($i=1;$i<=5;$i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                        </select>
                    </div>
                    <div>
                        <label >Severity (1-5)</label>
                        <select name="severity" required class="w-full ui-input">
                            @for($i=1;$i<=5;$i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="px-4 py-2 rounded bg-[#0B2345] text-white text-sm">Hitung & Simpan Penilaian</button>
                    </div>
                    <div class="md:col-span-3">
                        <label >Catatan Penilaian</label>
                        <textarea name="risk_notes" class="w-full ui-input" rows="2"></textarea>
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
                <form method="POST" action="{{ route('hse.dashboard.sap.hazard.temp_action', $hazard->id) }}" class="grid grid-cols-1 md:grid-cols-4">
                    @csrf
                    <div class="md:col-span-2">
                        <label >Deskripsi Tindakan Sementara</label>
                        <input type="text" name="description" required class="w-full ui-input">
                    </div>
                    <div>
                        <label >Tanggal</label>
                        <input type="date" name="action_date" value="{{ date('Y-m-d') }}" class="w-full ui-input">
                    </div>
                    <div class="flex items-end">
                        <x-ui.button variant="primary" icon="plus" type="submit">Tambah</x-ui.button>
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
                <form method="POST" action="{{ route('hse.dashboard.sap.hazard.submit', $hazard->id) }}">
                    @csrf
                    <x-ui.button variant="success" icon="circle-check" type="submit">Ajukan ke SHE Review</x-ui.button>
                </form>
            </div>
    </div>
</x-ui.page>
@endsection
