{{-- resources/views/admin/ccm/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6">

  {{-- HEADER --}}
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">
      🧯 Detail Critical Control Management
    </h1>

    <a href="{{ route('ccm-reports.index') }}"
       class="px-4 py-2 rounded-lg border">
      ← Kembali
    </a>
  </div>

  {{-- INFO UMUM --}}
  <div class="bg-white rounded-xl p-6 shadow">
    <h2 class="font-semibold mb-4">Informasi Umum</h2>

    <div class="grid md:grid-cols-3 gap-4 text-sm">
      <div>
        <div class="text-coal-500">Waktu Pelaporan</div>
        <div class="font-medium">{{ $report->waktu_pelaporan }}</div>
      </div>

      <div>
        <div class="text-coal-500">Jobsite</div>
        <div class="font-medium">{{ $report->jobsite }}</div>
      </div>

      <div>
        <div class="text-coal-500">Nama Pelapor</div>
        <div class="font-medium">{{ $report->nama_pelapor }}</div>
      </div>
    </div>
  </div>

  {{-- ================= HELPER TAMPILAN ================= --}}
  @php
    function row($label, $value) {
      if ($value === null || $value === '') return '';
      return "
        <div class='grid grid-cols-3 gap-4 py-2 border-b text-sm'>
          <div class='text-coal-500'>{$label}</div>
          <div class='col-span-2 font-medium'>{$value}</div>
        </div>
      ";
    }
  @endphp

  {{-- ================= KENDARAAN ================= --}}
  <div class="bg-white rounded-xl p-6 shadow">
    <h2 class="font-semibold mb-4">🚚 Kendaraan & Alat Berat</h2>
    {!! row('Ada Kegiatan', $report->kendaraan_ada_kegiatan ? 'Ada' : 'Tidak Ada') !!}
    {!! row('Pekerjaan Kritis', $report->kendaraan_pekerjaan_kritis) !!}
    {!! row('Prosedur', $report->kendaraan_prosedur) !!}
    {!! row('Pelanggaran', $report->kendaraan_pelanggaran) !!}
    {!! row('Engineering', $report->kendaraan_engineering) !!}
    {!! row('Administratif', $report->kendaraan_administratif) !!}
    {!! row('Praktek Kerja', $report->kendaraan_praktek_kerja) !!}
    {!! row('APD', $report->kendaraan_apd) !!}
  </div>

  {{-- ================= IZIN KERJA ================= --}}
  <div class="bg-white rounded-xl p-6 shadow">
    <h2 class="font-semibold mb-4">📄 Izin Kerja</h2>
    {!! row('Ada Kegiatan', $report->izin_kerja_ada ? 'Ada' : 'Tidak Ada') !!}
    {!! row('Pekerjaan Kritis', $report->izin_kerja_pekerjaan_kritis) !!}
    {!! row('Prosedur', $report->izin_kerja_prosedur) !!}
    {!! row('Pelanggaran', $report->izin_kerja_pelanggaran) !!}
    {!! row('Engineering', $report->izin_engineering) !!}
    {!! row('Administratif', $report->izin_administratif) !!}
    {!! row('Praktek Kerja', $report->izin_praktek_kerja) !!}
    {!! row('APD', $report->izin_apd) !!}
  </div>

  {{-- ================= KRITIS BARU ================= --}}
  <div class="bg-white rounded-xl p-6 shadow">
    <h2 class="font-semibold mb-4">🆕 Pekerjaan Kritis Baru</h2>
    {!! row('Ada Pekerjaan Baru', $report->kritis_baru_ada ? 'Ada' : 'Tidak Ada') !!}
    {!! row('Nama Pekerjaan', $report->kritis_baru_pekerjaan) !!}
    {!! row('Prosedur', $report->kritis_baru_prosedur) !!}
    {!! row('Dipahami', $report->kritis_baru_dipahami) !!}
    {!! row('Pelanggaran', $report->kritis_baru_pelanggaran) !!}
    {!! row('Engineering', $report->kritis_baru_engineering) !!}
    {!! row('Administratif', $report->kritis_baru_administratif) !!}
    {!! row('Praktek Kerja', $report->kritis_baru_praktek_kerja) !!}
    {!! row('APD', $report->kritis_baru_apd) !!}
  </div>

</div>
@endsection
