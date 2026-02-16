@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">➕ Tambah HIPO / Nearmiss</h1>

        <a href="{{ route('admin.hipo.index') }}"
            class="px-4 py-2 border rounded-lg hover:bg-gray-100">
            ← Kembali
        </a>
    </div>

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

    <form method="POST"
        action="{{ route('admin.hipo.store') }}"
        enctype="multipart/form-data"
        class="bg-white border rounded-xl p-6 space-y-6 shadow-sm">

        @csrf

        {{-- ================= DATA UMUM ================= --}}
        <div>
            <h3 class="font-semibold text-sm mb-3">📋 Data Umum</h3>

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
                    <input type="text"
                        name="jobsite"
                        value="{{ old('jobsite') }}"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- JENIS HIPO --}}
                <div>
                    <label class="text-xs">Jenis HIPO</label>
                    <select name="jenis_hipo"
                        required
                        class="w-full border rounded-lg px-3 py-2">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="HIPO" {{ old('jenis_hipo') == 'HIPO' ? 'selected' : '' }}>HIPO</option>
                        <option value="Nearmiss" {{ old('jenis_hipo') == 'Nearmiss' ? 'selected' : '' }}>Nearmiss</option>
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
            <h3 class="font-semibold text-sm mb-3">📝 Rincian Kejadian</h3>

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
            <h3 class="font-semibold text-sm mb-3">🛡️ Kontrol Risiko</h3>

            <div class="grid md:grid-cols-2 gap-6">

                @foreach([
                    'engineering' => 'Rekayasa Engineering',
                    'administrative' => 'Administratif',
                    'work_practice' => 'Praktek Kerja',
                    'ppe' => 'APD'
                ] as $key => $label)

                <div class="border rounded-xl p-4 space-y-3 bg-gray-50">

                    <h4 class="font-semibold text-sm">{{ $label }}</h4>

                    <textarea name="control_{{ $key }}"
                        rows="2"
                        class="w-full border rounded-lg px-3 py-2"
                        placeholder="Tindakan kontrol...">{{ old('control_'.$key) }}</textarea>

                    <input type="text"
                        name="pic_{{ $key }}"
                        value="{{ old('pic_'.$key) }}"
                        placeholder="PIC {{ $label }}"
                        class="w-full border rounded-lg px-3 py-2">

                    <input type="file"
                        name="evidence_{{ $key }}"
                        class="w-full border rounded-lg px-3 py-2">

                </div>

                @endforeach

            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end">
            <button class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                💾 Simpan Laporan
            </button>
        </div>

    </form>

</div>
@endsection
