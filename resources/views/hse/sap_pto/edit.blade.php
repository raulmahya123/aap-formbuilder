@extends('layouts.app')
@section('title', 'Edit PTO '.$pto->pto_no)
@section('content')
<x-ui.page>
    <x-ui.page-header>
        <div class="ui-page-header__copy"><x-ui.page-title>Edit PTO {{ $pto->pto_no }}</x-ui.page-title><x-ui.page-description>Perbarui checklist SHE-FM-019 untuk observasi ini.</x-ui.page-description></div>
        <x-ui.page-actions><x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.pto.show', $pto->id) }}">Lihat</x-ui.link><x-ui.link variant="secondary" href="{{ route('hse.dashboard.sap.pto.index') }}">Kembali ke daftar</x-ui.link></x-ui.page-actions>
    </x-ui.page-header>

    <div class="w-full max-w-6xl space-y-4">
            <div class="bg-white border rounded-lg p-4 text-sm grid grid-cols-2 md:grid-cols-4 gap-3">
                <div><div class="text-xs text-slate-500">Pekerjaan</div><div class="font-medium">{{ $pto->work_activity }}</div></div>
                <div><div class="text-xs text-slate-500">Tanggal/Waktu</div><div class="font-medium">{{ $pto->observation_date?->format('d/m/Y') }} {{ $pto->observation_time }}</div></div>
                <div><div class="text-xs text-slate-500">Pengamat</div><div class="font-medium">{{ $pto->observer?->name }}</div></div>
                <div><div class="text-xs text-slate-500">Diamati</div><div class="font-medium">{{ $pto->observedEmployee?->name }}</div></div>
            </div>

            <form method="POST" action="{{ route('hse.dashboard.sap.pto.save_checklist', $pto->id) }}" >
                @csrf
                <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                    <div class="px-4 py-2 border-b bg-slate-50 flex justify-between">
                        <div class="font-semibold text-sm">Checklist Observasi (Ya/Tidak/NA)</div>
                        <div class="text-xs text-slate-500">Safe/Unsafe dievaluasi berdasarkan konfigurasi item</div>
                    </div>
                    <div class="divide-y">
                        @php $section=''; @endphp
                        @foreach(($pto->template->items ?? []) as $item)
                            @if($section != $item->section)
                                @php $section = $item->section; @endphp
                                <div class="px-4 py-2 bg-slate-100 font-semibold text-xs uppercase">{{ $section }}</div>
                            @endif
                            <div class="px-4 py-3 grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                                <div class="md:col-span-6">
                                    <div class="text-sm"><span class="text-slate-500">{{ $item->item_no }}.</span> {{ $item->question }}</div>
                                    @if($item->guidance)<div class="text-xs text-slate-500 mt-1">{{ $item->guidance }}</div>@endif
                                    @if($item->has_apd_field)
                                        <div class="mt-2">
                                            <label >APD yang digunakan</label>
                                            <input type="text" name="apd[{{ $item->id }}]" class="w-full ui-input" placeholder="Sebutkan APD">
                                        </div>
                                    @endif
                                    @if($item->has_other_field)
                                        <div class="mt-2">
                                            <label >Keterangan lainnya</label>
                                            <input type="text" name="other_note[{{ $item->id }}]" class="w-full ui-input">
                                        </div>
                                    @endif
                                </div>
                                <div class="md:col-span-3 flex gap-4">
                                    <label class="flex items-center"><input type="radio" name="answers[{{ $item->id }}]" value="YA"> Ya</label>
                                    <label class="flex items-center"><input type="radio" name="answers[{{ $item->id }}]" value="TIDAK"> Tidak</label>
                                    <label class="flex items-center"><input type="radio" name="answers[{{ $item->id }}]" value="NA"> NA</label>
                                </div>
                                <div class="md:col-span-3">
                                    <input type="text" name="remarks[{{ $item->id }}]" class="w-full ui-input" placeholder="Catatan / Temuan">
                                </div>
                            </div>
                        @endforeach
                        @if(!($pto->template && $pto->template->items->count()))
                            <div class="px-4 py-6 text-center text-slate-500 text-sm">Template checklist belum tersedia. Silakan hubungi Admin SHE untuk seed template SHE-FM-019.</div>
                        @endif
                    </div>
                </div>

                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="px-4 py-2 border-b bg-slate-50 font-semibold text-sm">Uraian Hasil Pengamatan</div>
                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="md:col-span-2">
                            <label >Uraian Observasi</label>
                            <textarea name="observation_description" form="detailsForm" class="w-full ui-input" rows="2">{{ $pto->observation_description }}</textarea>
                        </div>
                        <div>
                            <label >Perilaku Aman</label>
                            <textarea name="safe_behavior" form="detailsForm" class="w-full ui-input" rows="2">{{ $pto->safe_behavior }}</textarea>
                        </div>
                        <div>
                            <label >Perilaku Berisiko</label>
                            <textarea name="risky_behavior" form="detailsForm" class="w-full ui-input" rows="2">{{ $pto->risky_behavior }}</textarea>
                        </div>
                        <div>
                            <label >Kondisi Tidak Aman</label>
                            <textarea name="unsafe_condition" form="detailsForm" class="w-full ui-input" rows="2">{{ $pto->unsafe_condition }}</textarea>
                        </div>
                        <div>
                            <label >Potensi Dampak</label>
                            <textarea name="potential_impact" form="detailsForm" class="w-full ui-input" rows="2">{{ $pto->potential_impact }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label >Tindakan Perbaikan Sementara / Awal</label>
                            <textarea name="immediate_action" form="detailsForm" class="w-full ui-input" rows="2">{{ $pto->immediate_action }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <x-ui.button variant="primary" icon="save" type="submit">Simpan Checklist</x-ui.button>
                </div>
            </form>

            <form id="detailsForm" method="POST" action="{{ route('hse.dashboard.sap.pto.update_details', $pto->id) }}">
                @csrf
                <input type="hidden" name="observation_description">
                <input type="hidden" name="safe_behavior">
                <input type="hidden" name="risky_behavior">
                <input type="hidden" name="unsafe_condition">
                <input type="hidden" name="potential_impact">
                <input type="hidden" name="immediate_action">
            </form>

            <div class="flex justify-end gap-2">
                <form method="POST" action="{{ route('hse.dashboard.sap.pto.submit', $pto->id) }}">
                    @csrf
                    <x-ui.button variant="success" icon="circle-check" type="submit">Ajukan ke Supervisor untuk Review</x-ui.button>
                </form>
            </div>
    </div>
</x-ui.page>
@endsection
