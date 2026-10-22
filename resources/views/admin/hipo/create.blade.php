@extends('layouts.app')

@section('content')
<x-ui.page class="w-full min-w-0 ">

    <x-ui.page-header>
  <div class="ui-page-header__copy"><x-ui.page-title>Tambah HIPO / Nearmiss</x-ui.page-title>
<x-ui.page-description>Lengkapi informasi untuk menambahkan laporan HIPO dan Nearmiss.</x-ui.page-description></div>
  <x-ui.page-actions><x-ui.back-link href="{{ route('admin.hipo.index') }}">Kembali</x-ui.back-link>
<x-ui.button form="page-primary-form" variant="primary" class="px-6 py-2 rounded-lg transition">
                <x-ui.lucide name="save" /> Simpan Laporan
            </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

    {{-- ERROR ALERT --}}
    @if ($errors->any())
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-lg">
            <ul class="text-sm list-disc ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="page-primary-form" method="POST"
        action="{{ route('admin.hipo.store') }}"
        enctype="multipart/form-data"
        class="ui-card bg-white border rounded-lg p-6 space-y-5 shadow-none">
  @csrf

        {{-- ================= DATA UMUM ================= --}}
        <div>
            <x-ui.card-title class="font-semibold text-sm mb-3"><x-ui.lucide name="clipboard-list" /> Data Umum</x-ui.card-title>

            <div class="grid md:grid-cols-3 gap-4">

                {{-- REPORT TIME --}}
                <div>
                    <label class="text-xs">Tanggal & Waktu</label>
                    <input type="datetime-local"
                        name="report_time"
                        value="{{ old('report_time') }}"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- REPORTER --}}
                <div>
                    <label class="text-xs">Reporter</label>
                    <input type="text"
                        value="{{ auth()->user()->name }}"
                        class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                        readonly>
                </div>

                {{-- JOBSITE --}}
                <div>
                    <label class="text-xs">Jobsite</label>
                    <select
                        name="site_id"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Jobsite --</option>
                        @foreach(($sites ?? []) as $site)
                            <option value="{{ $site['id'] }}" {{ old('site_id') == $site['id'] ? 'selected' : '' }}>
                                {{ $site['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- SHIFT --}}
                <div>
                    <label class="text-xs">Shift</label>
                    <select name="shift"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Shift --</option>
                        <option value="Shift 1" {{ old('shift') == 'Shift 1' ? 'selected' : '' }}>Shift 1</option>
                        <option value="Shift 2" {{ old('shift') == 'Shift 2' ? 'selected' : '' }}>Shift 2</option>
                    </select>
                </div>

                {{-- SUMBER --}}
                <div>
                    <label class="text-xs">Sumber</label>
                    <select name="source"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Sumber --</option>
                        <option value="Hazard Report" {{ old('source') == 'Hazard Report' ? 'selected' : '' }}>Hazard Report</option>
                        <option value="Safety Inspection" {{ old('source') == 'Safety Inspection' ? 'selected' : '' }}>Safety Inspection</option>
                        <option value="PTO" {{ old('source') == 'PTO' ? 'selected' : '' }}>PTO</option>
                    </select>
                </div>

                {{-- CATEGORY --}}
                <div>
                    <label class="text-xs">Kategori</label>
                    <select name="category"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="High Potential Hazard" {{ old('category') == 'High Potential Hazard' ? 'selected' : '' }}>
                            High Potential Hazard
                        </option>
                        <option value="Nearmiss" {{ old('category') == 'Nearmiss' ? 'selected' : '' }}>
                            Nearmiss
                        </option>
                    </select>
                </div>

                {{-- RISK LEVEL --}}
                <div>
                    <label class="text-xs">Risk Level</label>
                    <select name="risk_level"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                        @foreach(['Low','Medium','High','Extreme'] as $risk)
                            <option value="{{ $risk }}"
                                {{ old('risk_level') == $risk ? 'selected' : '' }}>
                                {{ $risk }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- STOP WORK --}}
                <div class="flex items-center gap-2 mt-6">
                    <input type="checkbox"
                        name="stop_work"
                        value="1"
                        {{ old('stop_work') ? 'checked' : '' }}>
                    <label class="text-sm">Stop Work</label>
                </div>

            </div>
        </div>

        {{-- ================= RINCIAN ================= --}}
        <div>
            <x-ui.card-title class="font-semibold text-sm mb-3"><x-ui.lucide name="pencil" /> Rincian Kejadian</x-ui.card-title>

            <div class="space-y-4">

                <div>
                    <label class="text-xs font-semibold">KTA</label>
                    <textarea name="kta"
                        rows="3"
                        required
                        class="w-full border rounded-lg px-3 py-2">{{ old('kta') }}</textarea>
                </div>

                <div>
                    <label class="text-xs font-semibold">TTA</label>
                    <textarea name="tta"
                        rows="3"
                        required
                        class="w-full border rounded-lg px-3 py-2">{{ old('tta') }}</textarea>
                </div>

                <div>
                    <label class="text-xs font-semibold">Deskripsi Kejadian</label>
                    <textarea name="description"
                        rows="3"
                        required
                        class="w-full border rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="text-xs font-semibold">Konsekuensi Potensial</label>
                    <select name="potential_consequence"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Konsekuensi --</option>
                        @foreach([
                            'Fatality',
                            'LTI',
                            'Injury Non LTI',
                            'Property Damage',
                            'Environment Accident'
                        ] as $consequence)
                            <option value="{{ $consequence }}"
                                {{ old('potential_consequence') == $consequence ? 'selected' : '' }}>
                                {{ $consequence }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- ================= KONTROL RISIKO ================= --}}
        <div>
            <x-ui.card-title class="font-semibold text-sm mb-3"><x-ui.lucide name="shield" /> Kontrol Risiko</x-ui.card-title>

            <div class="grid md:grid-cols-2 gap-5">

                @foreach([
                    'engineering' => 'Rekayasa Engineering',
                    'administrative' => 'Administratif',
                    'work_practice' => 'Praktek Kerja',
                    'ppe' => 'APD'
                ] as $key => $label)

                <div class="border rounded-lg p-4 space-y-3 bg-gray-50">

                    <h4 class="font-semibold text-sm">{{ $label }}</h4>

                    <textarea name="control_{{ $key }}"
                        rows="2"
                        class="w-full border rounded-lg px-3 py-2"
                        placeholder="Tindakan kontrol...">{{ old('control_'.$key) }}</textarea>

                    <x-ui.user-name-select name="pic_{{ $key }}" :users="$picUsers" :value="old('pic_'.$key)" aria-label="PIC {{ $label }}" />

                    <input type="file"
                        name="evidence_{{ $key }}"
                        class="w-full border rounded-lg px-3 py-2">

                </div>

                @endforeach

            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end">

        </div>

    </form>

</x-ui.page>
@endsection
