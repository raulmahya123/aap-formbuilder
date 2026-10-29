@extends('layouts.app')

@section('content')
<x-ui.page class="w-full min-w-0 ">

    {{-- Header --}}
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
            <x-ui.page-title>Input CCM Report</x-ui.page-title>
            <x-ui.page-description>Critical Control Management</x-ui.page-description>
        </div></div>
  <x-ui.page-actions><x-ui.back-link href="{{ route('ccm-reports.index') }}">Kembali ke daftar</x-ui.back-link>
<x-ui.button form="ccmForm" variant="primary" type="submit" >
                <x-ui.lucide name="save" /> Simpan Laporan CCM
            </x-ui.button>
</x-ui.page-actions>
</x-ui.page-header>

    {{-- Form Start --}}
    <form method="POST"
          action="{{ route('ccm-reports.store') }}"
          enctype="multipart/form-data"

          id="ccmForm">
        @csrf

        {{-- ================= SECTION 1 : UMUM ================= --}}
        <x-ui.panel >
            <x-ui.section-title >1. Informasi Umum</x-ui.section-title>
            <div class="grid md:grid-cols-3 gap-5">
                <div>
                    <label class="block">Waktu Pelaporan</label>
                    <input type="date" name="waktu_pelaporan"
                           class="w-full ui-input"
                           value="{{ old('waktu_pelaporan', date('Y-m-d')) }}" required>
                    @error('waktu_pelaporan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="ccm-jobsite" class="block">Jobsite</label>
                    <select id="ccm-jobsite" name="jobsite" class="w-full ui-input" required>
                        <option value="">Pilih site</option>
                        @foreach(($jobsites ?? []) as $jobsite)
                            <option value="{{ $jobsite['value'] }}" {{ old('jobsite') == $jobsite['value'] ? 'selected' : '' }}>
                                {{ $jobsite['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('jobsite') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-ui.form-label for="ccm-reporter" required>Nama Pelapor</x-ui.form-label>
                    <x-ui.form-select id="ccm-reporter" name="nama_pelapor" required>
                        <option value="">Pilih nama pelapor</option>
                        @foreach($reporters as $reporter)
                            <option value="{{ $reporter->name }}" @selected(old('nama_pelapor', auth()->user()->name) === $reporter->name)>{{ $reporter->name }}{{ $reporter->nik ? ' — '.$reporter->nik : '' }}</option>
                        @endforeach
                    </x-ui.form-select>
                    @error('nama_pelapor') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
            @endphp

            <x-ui.panel class="overflow-hidden section-card" data-section="{{ $key }}">

                <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                    <x-ui.card-title >{{ $title }}</x-ui.card-title>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Ada Kegiatan?</span>
                        <select name="{{ $flagName }}"
                                data-section-key="{{ $key }}"
                                class="w-full activity-toggle ui-input"
                                onchange="toggleSection('{{ $key }}', this.value)">
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ old($flagName) == '1' ? 'selected' : '' }}>ADA</option>
                            <option value="0" {{ old($flagName) === '0' ? 'selected' : '' }}>TIDAK ADA</option>
                        </select>
                    </div>
                </div>

                <div class="p-6">
                    {{-- CONTAINER: TIDAK ADA (ALASAN) --}}
                    <div id="{{ $key }}_no_content" class="hidden space-y-3">
                        <label class="block">Alasan Tidak Ada Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="{{ $key }}_tidak_ada_alasan"
                               class="w-full ui-input"
                               placeholder="Contoh: Tidak ada jadwal hari ini"
                               value="{{ old($key.'_tidak_ada_alasan') }}">
                    </div>

                    {{-- CONTAINER: ADA (FORM LENGKAP) --}}
                    <div id="{{ $key }}_yes_content" class="hidden space-y-5">
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="md:col-span-3">
                                <label class="block">Deskripsi Pekerjaan Kritis</label>
                                <input type="text" name="{{ $fieldPekerjaan }}"
                                       class="w-full ui-input"
                                       value="{{ old($fieldPekerjaan) }}">
                                @error($fieldPekerjaan) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Dropdown Prosedur (Sync Enum) --}}
                            <div>
                                <label class="block">Prosedur Terkait</label>
                                <select name="{{ $key }}_prosedur" class="w-full ui-input">
                                    <option value="">-- Pilih --</option>
                                    @php $optPro = ($key === 'kritis_baru') ? ['Ada','Tidak Ada'] : ['Sudah','Belum']; @endphp
                                    @foreach($optPro as $o)
                                        <option value="{{ $o }}" {{ old($key.'_prosedur') == $o ? 'selected' : '' }}>{{ $o }}</option>
                                    @endforeach
                                </select>
                                @error($key.'_prosedur') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Dropdown Pelanggaran (Sync Enum) --}}
                            <div>
                                <label class="block">Potensi/Pelanggaran</label>
                                <select name="{{ $key }}_pelanggaran" class="w-full ui-input">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Ada','Tidak Ada'] as $o)
                                        <option value="{{ $o }}" {{ old($key.'_pelanggaran') == $o ? 'selected' : '' }}>{{ $o }}</option>
                                    @endforeach
                                </select>
                                @error($key.'_pelanggaran') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Dropdown Dipahami (Hanya Kritis Baru) --}}
                            @if($key === 'kritis_baru')
                            <div>
                                <label class="block">Apakah Tim Paham?</label>
                                <select name="kritis_baru_dipahami" class="w-full ui-input">
                                    <option value="">-- Pilih --</option>
                                    @foreach(['Sudah','Belum'] as $o)
                                        <option value="{{ $o }}" {{ old('kritis_baru_dipahami') == $o ? 'selected' : '' }}>{{ $o }}</option>
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
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="mb-3">
                                    <label class="block normal-case">{{ str_replace('_', ' ', $ctrl) }} - Deskripsi</label>
                                    <textarea name="{{ $key }}_{{ $ctrl }}" rows="2"
                                              class="w-full ui-input">{{ old($key.'_'.$ctrl) }}</textarea>
                                    @error($key.'_'.$ctrl) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block normal-case">Upload Foto</label>
                                    <input type="file" name="{{ $key }}_{{ $ctrl }}_evidence" accept="image/*"
                                           class="w-full">
                                    @error($key.'_'.$ctrl.'_evidence') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
            yesContent.querySelectorAll('input[type="file"]').forEach(el => el.required = true);
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
