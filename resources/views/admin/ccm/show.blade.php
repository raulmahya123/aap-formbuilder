@extends('layouts.app')

@section('content')
<div class="max-w-6xl p-6 mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🧯 Detail CCM Report</h1>
            <p class="text-sm font-medium text-gray-500">ID Laporan: #{{ $report->id }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('ccm-reports.edit', $report->id) }}" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">📝 Edit Data</a>
            <a href="{{ route('ccm-reports.index') }}" class="px-4 py-2 text-sm bg-white border rounded-lg hover:bg-gray-50">← Kembali</a>
        </div>
    </div>

    {{-- INFO UMUM --}}
    <div class="p-6 bg-white border shadow-sm rounded-xl">
        <h2 class="pb-2 mb-4 font-bold text-gray-700 border-b">1. Informasi Umum</h2>
        <div class="grid gap-6 text-sm md:grid-cols-3">
            <div>
                <div class="text-xs font-bold tracking-wider text-gray-400 uppercase">Waktu Pelaporan</div>
                <div class="text-base font-semibold text-gray-800">{{ $report->waktu_pelaporan->format('d M Y') }}</div>
            </div>
            <div>
                <div class="text-xs font-bold tracking-wider text-gray-400 uppercase">Jobsite</div>
                <div class="text-base font-semibold text-gray-800">{{ $report->jobsite }}</div>
            </div>
            <div>
                <div class="text-xs font-bold tracking-wider text-gray-400 uppercase">Nama Pelapor</div>
                <div class="text-base font-semibold text-gray-800">{{ $report->nama_pelapor }}</div>
            </div>
        </div>
    </div>

    {{-- ================= HELPER FUNCTIONS ================= --}}
    @php
    function row($label, $value) {
        $val = ($value === null || $value === '') ? '-' : e($value);
        return "
        <div class='grid grid-cols-3 gap-4 py-3 text-sm border-b border-gray-100'>
            <div class='font-medium text-gray-500'>{$label}</div>
            <div class='col-span-2 text-gray-800 whitespace-pre-line'>{$val}</div>
        </div>";
    }

    function evidence($path, $label) {
        if (!$path) return "";

        // 🔥 FIX UTAMA (ANTI ERROR NIAGAHOSTER)
        $url = url('storage/'.$path);

        return "
        <div class='grid grid-cols-3 gap-4 py-3 border-b border-gray-100'>
            <div class='text-sm font-medium text-gray-500'>Evidence {$label}</div>
            <div class='col-span-2'>
                <a href='{$url}' target='_blank' class='block group'>
                    <img src='{$url}' class='object-cover w-40 h-24 transition border rounded-lg hover:opacity-80'>
                    <span class='block mt-1 text-xs font-bold text-blue-600 uppercase group-hover:underline'>
                        Klik untuk memperbesar
                    </span>
                </a>
            </div>
        </div>";
    }
    @endphp

    {{-- ================= SECTION CCM ================= --}}
    @php
    $sections = [
        'kendaraan' => '🚚 Kendaraan & Alat Berat',
        'izin_kerja' => '📄 Izin Kerja',
        'tebing' => '⛰️ Tebing / Disposal',
        'air_lumpur' => '💧 Air & Lumpur',
        'chainsaw' => '🪚 Chainsaw',
        'loto' => '🔒 LOTO & Penanganan Ban',
        'lifting' => '🏗️ Lifting',
        'blasting' => '💥 Blasting',
        'kritis_baru' => '🆕 Pekerjaan Kritis Baru',
    ];
    @endphp

    @foreach($sections as $key => $title)
        @php
            $flagName = ($key === 'kendaraan') ? 'kendaraan_ada_kegiatan' : $key . '_ada';
            $isAda = $report->$flagName;

            $fieldPekerjaan = ($key === 'kritis_baru') ? $key.'_pekerjaan' : $key.'_pekerjaan_kritis';

            $controls = ['engineering', 'administratif', 'praktek_kerja', 'apd'];
            if(in_array($key, ['air_lumpur', 'lifting'])) {
                $controls = ['engineering', 'administratif', 'apd'];
            }
        @endphp

        <div class="overflow-hidden bg-white border shadow-sm rounded-xl">
            <div class="{{ $isAda ? 'bg-blue-50 border-blue-100' : 'bg-gray-50 border-gray-100' }} px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800">{{ $title }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $isAda ? 'bg-blue-600 text-white' : 'bg-gray-400 text-white' }}">
                        {{ $isAda ? 'Ada Kegiatan' : 'Tidak Ada' }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                @if($isAda)

                    <div class="space-y-1">
                        {!! row('Pekerjaan Kritis', $report->$fieldPekerjaan) !!}
                        {!! row('Prosedur Terkait', $report->{$key.'_prosedur'}) !!}

                        @if($key === 'kritis_baru')
                            {!! row('Apakah Tim Paham?', $report->kritis_baru_dipahami) !!}
                        @endif

                        {!! row('Potensi Pelanggaran', $report->{$key.'_pelanggaran'}) !!}

                        <div class="mt-6 mb-2 text-xs font-bold tracking-widest text-blue-600 uppercase">
                            Pengendalian & Kontrol
                        </div>

                        @foreach($controls as $ctrl)
                            <div class="px-4 mb-2 rounded-lg bg-gray-50">
                                {!! row(ucfirst($ctrl), $report->{$key.'_'.$ctrl}) !!}
                                {!! evidence($report->{$key.'_'.$ctrl.'_evidence'}, ucfirst($ctrl)) !!}
                            </div>
                        @endforeach
                    </div>

                @else

                    <div class="p-4 border border-gray-300 border-dashed rounded-lg bg-gray-50">
                        <div class="text-sm font-medium text-gray-500">Alasan Tidak Ada Kegiatan:</div>
                        <div class="mt-1 italic font-semibold text-gray-800">
                            "{{ $report->{$key.'_tidak_ada_alasan'} ?? 'Tidak ada keterangan' }}"
                        </div>
                    </div>

                @endif
            </div>
        </div>
    @endforeach

</div>
@endsection