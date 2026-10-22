@extends('layouts.app')

@section('content')
<x-ui.page class="w-full min-w-0 ">

    {{-- Header --}}
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
            <x-ui.page-title>Edit CCM Report #{{ $report->id }}</x-ui.page-title>
            <x-ui.page-description>Critical Control Management</x-ui.page-description>
        </div></div>
  <x-ui.page-actions><x-ui.back-link href="{{ route('ccm-reports.index') }}">Kembali ke daftar</x-ui.back-link>
<x-ui.button form="ccmForm" variant="primary" type="submit" class="px-8 py-3 rounded-lg font-semibold shadow-none transition transform active:scale-95">
                <x-ui.lucide name="save" /> Update Laporan CCM
            </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

    {{-- Form Start --}}
    <form method="POST"
          action="{{ route('ccm-reports.update', $report->id) }}"
          enctype="multipart/form-data"
          class="space-y-5"
          id="ccmForm">
        @csrf
        @method('PUT')

        {{-- ================= SECTION 1 : UMUM ================= --}}
        <x-ui.panel class="bg-white rounded-lg border shadow-none">
            <x-ui.section-title class="font-bold text-lg mb-4 text-gray-700 border-b pb-2">1. Informasi Umum</x-ui.section-title>
            <div class="grid md:grid-cols-3 gap-5">
                <div>
                    <x-ui.form-label for="ccm-waktu" required>Waktu Pelaporan</x-ui.form-label>
                    <x-ui.form-input type="date" id="ccm-waktu" name="waktu_pelaporan"
                           value="{{ old('waktu_pelaporan', $report->waktu_pelaporan ? ($report->waktu_pelaporan instanceof \Carbon\CarbonInterface ? $report->waktu_pelaporan->format('Y-m-d') : \Carbon\Carbon::parse($report->waktu_pelaporan)->format('Y-m-d')) : '') }}" required />
                    @error('waktu_pelaporan') <x-ui.form-error>{{ $message }}</x-ui.form-error> @enderror
                </div>

                <div>
                    <x-ui.form-label for="ccm-jobsite" required>Jobsite</x-ui.form-label>
                    <x-ui.form-select id="ccm-jobsite" name="jobsite" required>
                        <option value="">Pilih site</option>
                        @if($report->jobsite && !collect($jobsites ?? [])->contains('value', $report->jobsite))
                            <option value="{{ $report->jobsite }}" selected>{{ $report->jobsite }}</option>
                        @endif
                        @foreach(($jobsites ?? []) as $jobsite)
                            <option value="{{ $jobsite['value'] }}" {{ old('jobsite', $report->jobsite) == $jobsite['value'] ? 'selected' : '' }}>
                                {{ $jobsite['label'] }}
                            </option>
                        @endforeach
                    </x-ui.form-select>
                    @error('jobsite') <x-ui.form-error>{{ $message }}</x-ui.form-error> @enderror
                </div>

                <div>
                    <x-ui.form-label for="ccm-reporter" required>Nama Pelapor</x-ui.form-label>
                    <x-ui.form-select id="ccm-reporter" name="nama_pelapor" required>
                        <option value="">Pilih nama pelapor</option>
                        @if($report->nama_pelapor && !collect($reporters ?? [])->contains('name', $report->nama_pelapor))
                            <option value="{{ $report->nama_pelapor }}" selected>{{ $report->nama_pelapor }}</option>
                        @endif
                        @foreach(($reporters ?? []) as $reporter)
                            <option value="{{ $reporter->name }}" @selected(old('nama_pelapor', $report->nama_pelapor) === $reporter->name)>{{ $reporter->name }}{{ $reporter->nik ? ' — '.$reporter->nik : '' }}</option>
                        @endforeach
                    </x-ui.form-select>
                    @error('nama_pelapor') <x-ui.form-error>{{ $message }}</x-ui.form-error> @enderror
                </div>
            </div>
        </x-ui.panel>

        {{-- ================= SECTION LOOP (DINAMIS) ================= --}}
        @php
            $sections = [
                'kendaraan'   => 'Pengoperasian Kendaraan & Alat Berat',
                'izin_kerja'  => 'Izin Kerja',
                'tebing'      => 'Tebing / Disposal',
                'air_lumpur'  => 'Air & Lumpur',
                'chainsaw'    => 'Chainsaw',
                'loto'        => 'LOTO',
                'lifting'     => 'Lifting',
                'blasting'    => 'Blasting',
                'kritis_baru' => 'Pekerjaan Kritis Baru',
            ];
        @endphp

        @foreach($sections as $key => $title)
            @php
                $controls = ['engineering', 'administratif', 'praktek_kerja', 'apd'];
                if(in_array($key, ['air_lumpur', 'lifting'])) {
                    $controls = ['engineering', 'administratif', 'apd'];
                }

                $fieldPekerjaan = ($key === 'kritis_baru') ? $key.'_pekerjaan' : $key.'_pekerjaan_kritis';
                $flagName = ($key === 'kendaraan') ? 'kendaraan_ada_kegiatan' : $key . '_ada';
                $currentFlag = old($flagName, $report->$flagName);
            @endphp

            <x-ui.panel class="bg-white rounded-lg border shadow-none overflow-hidden section-card" data-section="{{ $key }}">

                <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                    <x-ui.card-title class="font-bold text-gray-800">{{ $title }}</x-ui.card-title>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Ada Kegiatan?</span>
                        <select name="{{ $flagName }}"
                                data-section-key="{{ $key }}"
                                class="activity-toggle text-sm border-gray-300 rounded-lg shadow-none focus:ring-blue-500 focus:border-blue-500 font-bold"
                                onchange="toggleSection('{{ $key }}', this.value)">
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ $currentFlag == '1' || $currentFlag === true ? 'selected' : '' }}>ADA</option>
                            <option value="0" {{ $currentFlag === '0' || $currentFlag === false && $currentFlag !== null && $currentFlag !== '' ? 'selected' : '' }}>TIDAK ADA</option>
                        </select>
                    </div>
                </div>

                <div class="p-6">
                    {{-- CONTAINER: TIDAK ADA (ALASAN) --}}
                    <div id="{{ $key }}_no_content" class="hidden space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Alasan Tidak Ada Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="{{ $key }}_tidak_ada_alasan"
                               class="w-full border-gray-300 rounded-lg shadow-none bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: Tidak ada jadwal hari ini"
                               value="{{ old($key.'_tidak_ada_alasan', $report->{$key.'_tidak_ada_alasan'} ?? '') }}">
                    </div>

                    {{-- CONTAINER: ADA (FORM LENGKAP) --}}
                    <div id="{{ $key }}_yes_content" class="hidden space-y-5">
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Deskripsi Pekerjaan Kritis</label>
                                <input type="text" name="{{ $fieldPekerjaan }}"
                                       class="w-full border-gray-300 rounded-lg shadow-none focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ old($fieldPekerjaan, $report->$fieldPekerjaan ?? '') }}">
                                @error($fieldPekerjaan) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Dropdown Prosedur (Sync Enum) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prosedur Terkait</label>
                                <select name="{{ $key }}_prosedur" class="w-full border-gray-300 rounded-lg shadow-none">
                                    <option value="">-- Pilih --</option>
                                    @php $optPro = ($key === 'kritis_baru') ? ['Ada','Tidak Ada'] : ['Sudah','Belum']; @endphp
                                    @foreach($optPro as $o)
                                        <option value="{{ $o }}" {{ old($key.'_prosedur', $report->{$key.'_prosedur'} ?? '') == $o ? 'selected' : '' }}>{{ $o }}</option>
                                    @endforeach
                                </select>
                                @error($key.'_prosedur') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Dropdown Pelanggaran (Sync Enum) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Potensi/Pelanggaran</label>
                                <select name="{{ $key }}_pelanggaran" class="w-full border-gray-300 rounded-lg shadow-none">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Ada','Tidak Ada'] as $o)
                                        <option value="{{ $o }}" {{ old($key.'_pelanggaran', $report->{$key.'_pelanggaran'} ?? '') == $o ? 'selected' : '' }}>{{ $o }}</option>
                                    @endforeach
                                </select>
                                @error($key.'_pelanggaran') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Dropdown Dipahami (Hanya Kritis Baru) --}}
                            @if($key === 'kritis_baru')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Apakah Tim Paham?</label>
                                <select name="kritis_baru_dipahami" class="w-full border-gray-300 rounded-lg shadow-none">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Sudah','Belum'] as $o)
                                        <option value="{{ $o }}" {{ old('kritis_baru_dipahami', $report->kritis_baru_dipahami ?? '') == $o ? 'selected' : '' }}>{{ $o }}</option>
                                    @endforeach
                                </select>
                                @error('kritis_baru_dipahami') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            @endif
                        </div>

                        <hr class="border-gray-200">
                        <h4 class="text-sm font-bold text-gray-600 normal-case tracking-normal">Evidence & Kontrol</h4>

                        <div class="grid md:grid-cols-2 gap-5">
                            @foreach($controls as $ctrl)
                            @php
                                $evidenceField = $key.'_'.$ctrl.'_evidence';
                                $existingEvidence = $report->$evidenceField ?? null;
                            @endphp
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-500 normal-case mb-1">{{ str_replace('_', ' ', $ctrl) }} - Deskripsi</label>
                                    <textarea name="{{ $key }}_{{ $ctrl }}" rows="2"
                                              class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">{{ old($key.'_'.$ctrl, $report->{$key.'_'.$ctrl} ?? '') }}</textarea>
                                    @error($key.'_'.$ctrl) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 normal-case mb-1">Upload Foto</label>

                                    {{-- Show existing evidence thumbnail if available --}}
                                    @if($existingEvidence)
                                        <div class="mb-2 flex items-center gap-3 p-2 bg-white rounded-lg border">
                                            @php $evidenceUrl = route('pubfile.stream', ['path' => $existingEvidence]); @endphp
                                            <a href="{{ $evidenceUrl }}" target="_blank">
                                                <img src="{{ $evidenceUrl }}" class="w-16 h-12 object-cover rounded border" alt="Evidence">
                                            </a>
                                            <div class="text-xs text-gray-500">
                                                <span class="text-green-600 font-semibold">✓ Sudah ada foto</span><br>
                                                Upload baru untuk mengganti
                                            </div>
                                        </div>
                                    @endif

                                    <input type="file" name="{{ $evidenceField }}" accept="image/*"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                    @error($evidenceField) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-ui.panel>
        @endforeach

        <div class="flex items-center gap-4 pt-4 pb-8">
            <a href="{{ route('ccm-reports.index') }}" class="text-gray-600 hover:underline">Batal</a>
        </div>
    </form>
</x-ui.page>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.activity-toggle').forEach(select => {
            toggleSection(select.getAttribute('data-section-key'), select.value);
        });
    });

    function toggleSection(key, value) {
        const noContent = document.getElementById(key + '_no_content');
        const yesContent = document.getElementById(key + '_yes_content');
        if (!noContent || !yesContent) return;

        const inputsNo = noContent.querySelectorAll('input, textarea, select');
        const inputsYes = yesContent.querySelectorAll('input, textarea, select');

        if (value === '1') {
            noContent.classList.add('hidden');
            yesContent.classList.remove('hidden');
            inputsYes.forEach(el => el.required = true);
            inputsNo.forEach(el => { el.required = false; el.value = ''; });
            // In edit mode, file inputs are NOT required (existing evidence can remain)
            yesContent.querySelectorAll('input[type="file"]').forEach(el => el.required = false);
        } else if (value === '0') {
            noContent.classList.remove('hidden');
            yesContent.classList.add('hidden');
            inputsNo.forEach(el => el.required = true);
            inputsYes.forEach(el => { el.required = false; });
        } else {
            noContent.classList.add('hidden');
            yesContent.classList.add('hidden');
        }
    }
</script>
@endsection
