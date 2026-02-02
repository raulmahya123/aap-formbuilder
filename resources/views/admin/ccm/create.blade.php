{{-- resources/views/admin/ccm/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 space-y-6">

  {{-- ================= JUDUL ================= --}}
  <div>
    <h1 class="text-2xl font-bold">🧯 Critical Control Management (CCM)</h1>
    <p class="text-sm text-gray-500">Form Input Pengendalian Risiko Kritis</p>
  </div>

  <form method="POST" action="{{ route('ccm-reports.store') }}" class="space-y-6">
    @csrf

    {{-- ================= SECTION 1 : UMUM ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">1. Informasi Umum</h2>

      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm">Waktu Pelaporan</label>
          <input type="date" name="waktu_pelaporan"
                 class="w-full border rounded px-3 py-2"
                 value="{{ old('waktu_pelaporan') }}">
        </div>

        <div>
          <label class="text-sm">Jobsite</label>
          <input type="text" name="jobsite"
                 class="w-full border rounded px-3 py-2"
                 value="{{ old('jobsite') }}">
        </div>

        <div>
          <label class="text-sm">Nama Pelapor</label>
          <input type="text" name="nama_pelapor"
                 class="w-full border rounded px-3 py-2"
                 value="{{ old('nama_pelapor') }}">
        </div>
      </div>
    </div>

    {{-- ================= SECTION 2 : KENDARAAN ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">2. Pengoperasian Kendaraan & Alat Berat</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada kegiatan?</label>
          <select name="kendaraan_ada_kegiatan" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="kendaraan_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="kendaraan_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="kendaraan_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="kendaraan_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="kendaraan_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Praktek Kerja Aman</label>
          <input type="text" name="kendaraan_praktek_kerja"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="kendaraan_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    {{-- ================= SECTION 3 : IZIN KERJA ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">3. Izin Kerja</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada izin kerja?</label>
          <select name="izin_kerja_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="izin_kerja_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="izin_kerja_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="izin_kerja_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="izin_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="izin_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Praktek Kerja Aman</label>
          <input type="text" name="izin_praktek_kerja"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="izin_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

        {{-- ================= SECTION 4 : TEBING / DISPOSAL ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">4. Tebing / Disposal</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada kegiatan?</label>
          <select name="tebing_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="tebing_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="tebing_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="tebing_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="tebing_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="tebing_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Praktek Kerja Aman</label>
          <input type="text" name="tebing_praktek_kerja"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="tebing_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    {{-- ================= SECTION 5 : AIR & LUMPUR ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">5. Air & Lumpur</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada kegiatan?</label>
          <select name="air_lumpur_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="air_lumpur_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="air_lumpur_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="air_lumpur_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="air_lumpur_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="air_lumpur_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="air_lumpur_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    {{-- ================= SECTION 6 : CHAINSAW ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">6. Chainsaw</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada kegiatan?</label>
          <select name="chainsaw_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="chainsaw_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="chainsaw_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="chainsaw_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="chainsaw_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="chainsaw_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Praktek Kerja Aman</label>
          <input type="text" name="chainsaw_praktek_kerja"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="chainsaw_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    {{-- ================= SECTION 7 : LOTO ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">7. LOTO</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada kegiatan?</label>
          <select name="loto_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="loto_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="loto_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="loto_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="loto_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="loto_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Praktek Kerja Aman</label>
          <input type="text" name="loto_praktek_kerja"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="loto_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>
    {{-- ================= SECTION 8 : LIFTING ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">8. Lifting</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada kegiatan?</label>
          <select name="lifting_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="lifting_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="lifting_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="lifting_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="lifting_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="lifting_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="lifting_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    {{-- ================= SECTION 9 : BLASTING ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">9. Blasting</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada kegiatan?</label>
          <select name="blasting_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pekerjaan Kritis</label>
          <input type="text" name="blasting_pekerjaan_kritis"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur</label>
          <select name="blasting_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="blasting_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="blasting_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="blasting_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Praktek Kerja Aman</label>
          <input type="text" name="blasting_praktek_kerja"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="blasting_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    {{-- ================= SECTION 10 : PEKERJAAN KRITIS BARU ================= --}}
    <div class="bg-white p-6 rounded-xl shadow">
      <h2 class="font-semibold mb-4">10. Pekerjaan Kritis Baru</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm">Ada pekerjaan kritis baru?</label>
          <select name="kritis_baru_ada" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="1">Ada</option>
            <option value="0">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Jenis Pekerjaan</label>
          <input type="text" name="kritis_baru_pekerjaan"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Prosedur Ada?</label>
          <select name="kritis_baru_prosedur" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Dipahami?</label>
          <select name="kritis_baru_dipahami" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Sudah">Sudah</option>
            <option value="Belum">Belum</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Pelanggaran</label>
          <select name="kritis_baru_pelanggaran" class="w-full border rounded px-3 py-2">
            <option value="">-- pilih --</option>
            <option value="Ada">Ada</option>
            <option value="Tidak Ada">Tidak Ada</option>
          </select>
        </div>

        <div>
          <label class="text-sm">Engineering Control</label>
          <input type="text" name="kritis_baru_engineering"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Administratif Control</label>
          <input type="text" name="kritis_baru_administratif"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">Praktek Kerja Aman</label>
          <input type="text" name="kritis_baru_praktek_kerja"
                 class="w-full border rounded px-3 py-2">
        </div>

        <div>
          <label class="text-sm">APD</label>
          <input type="text" name="kritis_baru_apd"
                 class="w-full border rounded px-3 py-2">
        </div>
      </div>
    </div>

    {{-- ================= ACTION ================= --}}
    <div class="flex gap-3">
      <button type="submit"
              class="px-6 py-2 rounded-lg bg-maroon-700 text-white">
        💾 Simpan CCM
      </button>

      <a href="{{ route('ccm-reports.index') }}"
         class="px-6 py-2 rounded-lg border">
        Batal
      </a>
    </div>

  </form>
</div>
@endsection
