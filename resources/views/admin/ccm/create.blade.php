@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🧯 Input CCM Report</h1>
            <p class="text-sm text-gray-500">Critical Control Management</p>
        </div>
        <a href="{{ route('ccm-reports.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
            &larr; Kembali ke List
        </a>
    </div>

    {{-- Form Start --}}
    <form method="POST" 
          action="{{ route('ccm-reports.store') }}" 
          enctype="multipart/form-data" 
          class="space-y-8"
          id="ccmForm">
        @csrf

        {{-- ================= SECTION 1 : UMUM ================= --}}
        <div class="bg-white p-6 rounded-xl border shadow-sm">
            <h2 class="font-bold text-lg mb-4 text-gray-700 border-b pb-2">1. Informasi Umum</h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Pelaporan</label>
                    <input type="date" name="waktu_pelaporan" 
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('waktu_pelaporan', date('Y-m-d')) }}" required>
                    @error('waktu_pelaporan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jobsite</label>
                    <select name="jobsite" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">-- Pilih Jobsite --</option>
                        @foreach(['AAP-BGG', 'AAP-SBS', 'ABN-DBK', 'ABC-POS'] as $site)
                            <option value="{{ $site }}" {{ old('jobsite') == $site ? 'selected' : '' }}>{{ $site }}</option>
                        @endforeach
                    </select>
                    @error('jobsite') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelapor</label>
                    <input type="text" name="nama_pelapor" 
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('nama_pelapor') }}" placeholder="Nama Lengkap" required>
                    @error('nama_pelapor') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

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

            <div class="bg-white rounded-xl border shadow-sm overflow-hidden section-card" data-section="{{ $key }}">
                
                <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">{{ $title }}</h3>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">Ada Kegiatan?</span>
                        <select name="{{ $flagName }}" 
                                data-section-key="{{ $key }}"
                                class="activity-toggle text-sm border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 font-bold"
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
                        <label class="block text-sm font-medium text-gray-700">Alasan Tidak Ada Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="{{ $key }}_tidak_ada_alasan" 
                               class="w-full border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: Tidak ada jadwal hari ini"
                               value="{{ old($key.'_tidak_ada_alasan') }}">
                    </div>

                    {{-- CONTAINER: ADA (FORM LENGKAP) --}}
                    <div id="{{ $key }}_yes_content" class="hidden space-y-6">
                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700">Deskripsi Pekerjaan Kritis</label>
                                <input type="text" name="{{ $fieldPekerjaan }}" 
                                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       value="{{ old($fieldPekerjaan) }}">
                                @error($fieldPekerjaan) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            
                            {{-- Dropdown Prosedur (Sync Enum) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prosedur Terkait</label>
                                <select name="{{ $key }}_prosedur" class="w-full border-gray-300 rounded-lg shadow-sm">
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
                                <label class="block text-sm font-medium text-gray-700">Potensi/Pelanggaran</label>
                                <select name="{{ $key }}_pelanggaran" class="w-full border-gray-300 rounded-lg shadow-sm">
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
                                <label class="block text-sm font-medium text-gray-700">Apakah Tim Paham?</label>
                                <select name="kritis_baru_dipahami" class="w-full border-gray-300 rounded-lg shadow-sm">
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
                        <h4 class="text-sm font-bold text-gray-600 uppercase tracking-wide">Evidence & Kontrol</h4>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            @foreach($controls as $ctrl)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="mb-3">
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ str_replace('_', ' ', $ctrl) }} - Deskripsi</label>
                                    <textarea name="{{ $key }}_{{ $ctrl }}" rows="2" 
                                              class="w-full border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500">{{ old($key.'_'.$ctrl) }}</textarea>
                                    @error($key.'_'.$ctrl) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Upload Foto</label>
                                    <input type="file" name="{{ $key }}_{{ $ctrl }}_evidence" accept="image/*"
                                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                    @error($key.'_'.$ctrl.'_evidence') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="flex items-center gap-4 pt-4 pb-10">
            <button type="submit" class="bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold shadow hover:bg-blue-800 transition transform active:scale-95">
                💾 Simpan Laporan CCM
            </button>
            <a href="{{ route('ccm-reports.index') }}" class="text-gray-600 hover:underline">Batal</a>
        </div>
    </form>
</div>

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