@extends('layouts.app')

@section('content')
<x-ui.page class="w-full min-w-0 ">

    {{-- HEADER --}}
    <x-ui.page-header>
  <div class="ui-page-header__copy"><div>
        <x-ui.page-title>Detail CCM Report</x-ui.page-title>
            <x-ui.page-description>ID Laporan: #{{ $report->id }}</x-ui.page-description>
        </div></div>
  <x-ui.page-actions><div class="flex gap-2">
            <x-ui.link variant="primary" href="{{ route('ccm-reports.edit', $report->id) }}" class="px-4 py-2 text-sm rounded-lg shadow-none">📝 Edit Data</x-ui.link>
            <x-ui.link variant="secondary" href="{{ route('ccm-reports.index') }}" class="px-4 py-2 text-sm border rounded-lg">← Kembali</x-ui.link>
        </div></x-ui.page-actions>
</x-ui.page-header>

    {{-- INFO UMUM --}}
    <x-ui.panel class="bg-white border shadow-none rounded-lg">
        <x-ui.section-title class="pb-2 mb-4 font-bold text-gray-700 border-b">1. Informasi Umum</x-ui.section-title>
        <div class="grid gap-5 text-sm md:grid-cols-3">
            <div>
                <div class="text-xs font-bold tracking-normal text-gray-400 normal-case">Waktu Pelaporan</div>
                <div class="text-base font-semibold text-gray-800">{{ $report->waktu_pelaporan->format('d M Y') }}</div>
            </div>
            <div>
                <div class="text-xs font-bold tracking-normal text-gray-400 normal-case">Jobsite</div>
                <div class="text-base font-semibold text-gray-800">{{ $jobsiteLabel }}</div>
            </div>
            <div>
                <div class="text-xs font-bold tracking-normal text-gray-400 normal-case">Nama Pelapor</div>
                <div class="text-base font-semibold text-gray-800">{{ $report->nama_pelapor }}</div>
            </div>
        </div>
    </x-ui.panel>

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
        $url = route('pubfile.stream', ['path' => $path]);

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
        'kendaraan' => 'Kendaraan & Alat Berat',
        'izin_kerja' => 'Izin Kerja',
        'tebing' => 'Tebing / Disposal',
        'air_lumpur' => 'Air & Lumpur',
        'chainsaw' => 'Chainsaw',
        'loto' => 'LOTO & Penanganan Ban',
        'lifting' => 'Lifting',
        'blasting' => 'Blasting',
        'kritis_baru' => 'Pekerjaan Kritis Baru',
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

        <x-ui.panel class="overflow-hidden bg-white border shadow-none rounded-lg">
            <div class="{{ $isAda ? 'bg-blue-50 border-blue-100' : 'bg-gray-50 border-gray-100' }} px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <x-ui.section-title class="text-lg font-bold text-gray-800">{{ $title }}</x-ui.section-title>
                    <span class="px-3 py-1 rounded-full text-xs font-bold normal-case {{ $isAda ? 'bg-blue-600 text-white' : 'bg-gray-400 text-white' }}">
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

                        <div class="mt-6 mb-2 text-xs font-bold tracking-normal text-blue-600 normal-case">
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
        </x-ui.panel>
    @endforeach

</x-ui.page>
@endsection
