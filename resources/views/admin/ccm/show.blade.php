@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🧯 Detail CCM Report</h1>
            <p class="text-sm text-gray-500 font-medium">ID Laporan: #{{ $report->id }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('ccm-reports.edit', $report->id) }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm text-sm">📝 Edit Data</a>
            <a href="{{ route('ccm-reports.index') }}" class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">← Kembali</a>
        </div>
    </div>

    {{-- INFO UMUM --}}
    <div class="bg-white rounded-xl p-6 border shadow-sm">
        <h2 class="font-bold text-gray-700 mb-4 border-b pb-2">1. Informasi Umum</h2>
        <div class="grid md:grid-cols-3 gap-6 text-sm">
            <div>
                <div class="text-gray-400 uppercase text-xs font-bold tracking-wider">Waktu Pelaporan</div>
                <div class="font-semibold text-gray-800 text-base">{{ $report->waktu_pelaporan->format('d M Y') }}</div>
            </div>
            <div>
                <div class="text-gray-400 uppercase text-xs font-bold tracking-wider">Jobsite</div>
                <div class="font-semibold text-gray-800 text-base">{{ $report->jobsite }}</div>
            </div>
            <div>
                <div class="text-gray-400 uppercase text-xs font-bold tracking-wider">Nama Pelapor</div>
                <div class="font-semibold text-gray-800 text-base">{{ $report->nama_pelapor }}</div>
            </div>
        </div>
    </div>

    {{-- ================= HELPER FUNCTIONS ================= --}}
    @php
    function row($label, $value) {
        $val = ($value === null || $value === '') ? '-' : e($value);
        return "
        <div class='grid grid-cols-3 gap-4 py-3 border-b border-gray-100 text-sm'>
            <div class='text-gray-500 font-medium'>{$label}</div>
            <div class='col-span-2 text-gray-800 whitespace-pre-line'>{$val}</div>
        </div>";
    }

    function evidence($path, $label) {
        if (!$path) {
            return "";
        }
        $url = asset('storage/'.$path);
        return "
        <div class='py-3 border-b border-gray-100 grid grid-cols-3 gap-4'>
            <div class='text-gray-500 text-sm font-medium'>Evidence {$label}</div>
            <div class='col-span-2'>
                <a href='{$url}' target='_blank' class='group block'>
                    <img src='{$url}' class='h-24 w-40 object-cover rounded-lg border hover:opacity-80 transition'>
                    <span class='text-blue-600 text-xs mt-1 block group-hover:underline uppercase font-bold'>Klik untuk memperbesar</span>
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
            // Logic Flag Name sesuai database
            $flagName = ($key === 'kendaraan') ? 'kendaraan_ada_kegiatan' : $key . '_ada';
            $isAda = $report->$flagName;

            // Logic Field Pekerjaan
            $fieldPekerjaan = ($key === 'kritis_baru') ? $key.'_pekerjaan' : $key.'_pekerjaan_kritis';
            
            // Logic Controls (Air Lumpur & Lifting cuma 3 item)
            $controls = ['engineering', 'administratif', 'praktek_kerja', 'apd'];
            if(in_array($key, ['air_lumpur', 'lifting'])) {
                $controls = ['engineering', 'administratif', 'apd'];
            }
        @endphp

        <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
            <div class="{{ $isAda ? 'bg-blue-50 border-blue-100' : 'bg-gray-50 border-gray-100' }} px-6 py-4 border-b">
                <div class="flex justify-between items-center">
                    <h2 class="font-bold text-lg text-gray-800">{{ $title }}</h2>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $isAda ? 'bg-blue-600 text-white' : 'bg-gray-400 text-white' }}">
                        {{ $isAda ? 'Ada Kegiatan' : 'Tidak Ada' }}
                    </span>
                </div>
            </div>

            <div class="p-6">
                @if($isAda)
                    {{-- JIKA ADA KEGIATAN --}}
                    <div class="space-y-1">
                        {!! row('Pekerjaan Kritis', $report->$fieldPekerjaan) !!}
                        {!! row('Prosedur Terkait', $report->{$key.'_prosedur'}) !!}
                        
                        @if($key === 'kritis_baru')
                            {!! row('Apakah Tim Paham?', $report->kritis_baru_dipahami) !!}
                        @endif

                        {!! row('Potensi Pelanggaran', $report->{$key.'_pelanggaran'}) !!}

                        <div class="mt-6 mb-2 text-xs font-bold text-blue-600 uppercase tracking-widest">Pengendalian & Kontrol</div>
                        
                        @foreach($controls as $ctrl)
                            <div class="bg-gray-50 rounded-lg px-4 mb-2">
                                {!! row(ucfirst($ctrl), $report->{$key.'_'.$ctrl}) !!}
                                {!! evidence($report->{$key.'_'.$ctrl.'_evidence'}, ucfirst($ctrl)) !!}
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- JIKA TIDAK ADA KEGIATAN --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300">
                        <div class="text-sm text-gray-500 font-medium">Alasan Tidak Ada Kegiatan:</div>
                        <div class="text-gray-800 font-semibold mt-1 italic">"{{ $report->{$key.'_tidak_ada_alasan'} ?? 'Tidak ada keterangan' }}"</div>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

</div>
@endsection