@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl mx-auto">

    <h1 class="text-xl font-semibold mb-4">⚠️ Form Laporan HIPO / Nearmiss</h1>

    <form method="POST"
          action="{{ route('user.hipo.store') }}"
          enctype="multipart/form-data"
          class="space-y-4 bg-white border rounded-xl p-6">

        @csrf

        {{-- Jobsite --}}
        <div>
            <label class="text-sm font-medium">Jobsite</label>
            <select name="jobsite" class="w-full border rounded-lg px-3 py-2">
                <option value="AAP-BGG">AAP-BGG</option>
                <option value="AAP-SBS">AAP-SBS</option>
                <option value="ABN-DBK">ABN-DBK</option>
                <option value="ABC-POS">ABC-POS</option>
            </select>
        </div>

        {{-- Nama Pelapor --}}
        <div>
            <label class="text-sm font-medium">Nama Pelapor</label>
            <input name="reporter_name"
                   value="{{ auth()->user()->name }}"
                   class="w-full border rounded-lg px-3 py-2">
        </div>

        {{-- Waktu --}}
        <div>
            <label class="text-sm font-medium">Waktu Pelaporan</label>
            <input type="datetime-local"
                   name="report_time"
                   class="w-full border rounded-lg px-3 py-2">
        </div>

        {{-- Shift --}}
        <div>
            <label class="text-sm font-medium">Shift</label>
            <select name="shift" class="w-full border rounded-lg px-3 py-2">
                <option>Shift 1</option>
                <option>Shift 2</option>
            </select>
        </div>

        {{-- Sumber --}}
        <div>
            <label class="text-sm font-medium">Sumber Laporan</label>
            <select name="source" class="w-full border rounded-lg px-3 py-2">
                <option>Hazard Report</option>
                <option>Safety Inspection</option>
                <option>PTO</option>
            </select>
        </div>

        {{-- Kategori --}}
        <div>
            <label class="text-sm font-medium">Kategori</label>
            <select name="category" class="w-full border rounded-lg px-3 py-2">
                <option>High Potential Hazard</option>
                <option>Nearmiss</option>
            </select>
        </div>

        {{-- Rincian --}}
        <div>
            <label class="text-sm font-medium">Rincian HIPO / Nearmiss</label>
            <textarea name="description"
                      rows="3"
                      class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        {{-- Konsekuensi --}}
        <div>
            <label class="text-sm font-medium">Potensi Konsekuensi</label>
            <select name="potential_consequence" class="w-full border rounded-lg px-3 py-2">
                <option>Fatality</option>
                <option>LTI</option>
                <option>Injury Non LTI</option>
                <option>Property Damage</option>
                <option>Environment Accident</option>
            </select>
        </div>

        {{-- Stop Work --}}
        <div>
            <label class="text-sm font-medium">Stop Work</label>
            <select name="stop_work" class="w-full border rounded-lg px-3 py-2">
                <option value="1">Ya</option>
                <option value="0">Tidak</option>
            </select>
        </div>

        {{-- Engineering --}}
        <div>
            <label class="text-sm font-medium">Kontrol Engineering</label>
            <textarea name="control_engineering" class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        {{-- Administrative --}}
        <div>
            <label class="text-sm font-medium">Kontrol Administratif</label>
            <textarea name="control_administrative" class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        {{-- Work Practice --}}
        <div>
            <label class="text-sm font-medium">Kontrol Praktek Kerja</label>
            <textarea name="control_work_practice" class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        {{-- APD --}}
        <div>
            <label class="text-sm font-medium">Kontrol APD</label>
            <textarea name="control_ppe" class="w-full border rounded-lg px-3 py-2"></textarea>
        </div>

        {{-- Evidence --}}
        <div>
            <label class="text-sm font-medium">Evidence (Foto / PDF)</label>
            <input type="file" name="evidence_file" class="w-full">
        </div>

        <div class="pt-4 flex justify-end gap-2">
            <a href="{{ route('user.hipo.index') }}"
               class="px-4 py-2 border rounded-lg">
                Batal
            </a>

            <button class="px-4 py-2 bg-maroon-700 text-white rounded-lg">
                Kirim Laporan
            </button>
        </div>

    </form>
</div>
@endsection
