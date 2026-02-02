<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CcmReport;
use Illuminate\Http\Request;

class CcmReportController extends Controller
{
    /**
     * ===============================
     * GET /ccm-reports
     * ===============================
     */
    public function index()
    {
        $reports = CcmReport::orderBy('waktu_pelaporan', 'desc')
            ->paginate(10);

        return view('admin.ccm.index', compact('reports'));
    }

    /**
     * ===============================
     * GET /ccm-reports/create
     * ===============================
     */
    public function create()
    {
        return view('admin.ccm.create');
    }

    /**
     * ===============================
     * POST /ccm-reports
     * ===============================
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        CcmReport::create($validated);

        return redirect()
            ->route('ccm-reports.index')
            ->with('success', 'CCM Report berhasil disimpan');
    }

    /**
     * ===============================
     * GET /ccm-reports/{id}
     * ===============================
     */
    public function show($id)
    {
        $report = CcmReport::findOrFail($id);

        return view('admin.ccm.show', compact('report'));
    }

    /**
     * ===============================
     * GET /ccm-reports/{id}/edit
     * ===============================
     */
    public function edit($id)
    {
        $report = CcmReport::findOrFail($id);

        return view('admin.ccm.edit', compact('report'));
    }

    /**
     * ===============================
     * PUT /ccm-reports/{id}
     * ===============================
     */
    public function update(Request $request, $id)
    {
        $report = CcmReport::findOrFail($id);

        $validated = $request->validate($this->rules(false));

        $report->update($validated);

        return redirect()
            ->route('ccm-reports.index')
            ->with('success', 'CCM Report berhasil diperbarui');
    }

    /**
     * ===============================
     * DELETE /ccm-reports/{id}
     * ===============================
     */
    public function destroy($id)
    {
        CcmReport::findOrFail($id)->delete();

        return redirect()
            ->route('ccm-reports.index')
            ->with('success', 'CCM Report berhasil dihapus');
    }

    /**
     * ===============================
     * VALIDATION RULES (FULL)
     * ===============================
     */
    private function rules(bool $isCreate = true): array
    {
        $required = $isCreate ? 'required' : 'sometimes';

        return [

            /* =====================
             * SECTION 1 - UMUM
             * ===================== */
            'waktu_pelaporan' => "$required|date",
            'jobsite'         => "$required|string",
            'nama_pelapor'    => "$required|string",

            /* =====================
             * Kendaraan & Alat Berat
             * ===================== */
            'kendaraan_ada_kegiatan'               => 'nullable|boolean',
            'kendaraan_pekerjaan_kritis'           => 'nullable|string',
            'kendaraan_prosedur'                   => 'nullable|in:Sudah,Belum',
            'kendaraan_pelanggaran'                => 'nullable|in:Ada,Tidak Ada',
            'kendaraan_engineering'                => 'nullable|string',
            'kendaraan_engineering_evidence'       => 'nullable|string',
            'kendaraan_administratif'              => 'nullable|string',
            'kendaraan_administratif_evidence'     => 'nullable|string',
            'kendaraan_praktek_kerja'              => 'nullable|string',
            'kendaraan_praktek_kerja_evidence'     => 'nullable|string',
            'kendaraan_apd'                        => 'nullable|string',
            'kendaraan_apd_evidence'               => 'nullable|string',

            /* =====================
             * Izin Kerja
             * ===================== */
            'izin_kerja_ada'                       => 'nullable|boolean',
            'izin_kerja_pekerjaan_kritis'          => 'nullable|string',
            'izin_kerja_prosedur'                  => 'nullable|in:Sudah,Belum',
            'izin_kerja_pelanggaran'               => 'nullable|in:Ada,Tidak Ada',
            'izin_engineering'                     => 'nullable|string',
            'izin_engineering_evidence'            => 'nullable|string',
            'izin_administratif'                   => 'nullable|string',
            'izin_administratif_evidence'          => 'nullable|string',
            'izin_praktek_kerja'                   => 'nullable|string',
            'izin_praktek_kerja_evidence'          => 'nullable|string',
            'izin_apd'                             => 'nullable|string',
            'izin_apd_evidence'                    => 'nullable|string',

            /* =====================
             * Tebing / Disposal
             * ===================== */
            'tebing_ada'                           => 'nullable|boolean',
            'tebing_pekerjaan_kritis'              => 'nullable|string',
            'tebing_prosedur'                      => 'nullable|in:Sudah,Belum',
            'tebing_pelanggaran'                   => 'nullable|in:Ada,Tidak Ada',
            'tebing_engineering'                   => 'nullable|string',
            'tebing_engineering_evidence'          => 'nullable|string',
            'tebing_administratif'                 => 'nullable|string',
            'tebing_administratif_evidence'        => 'nullable|string',
            'tebing_praktek_kerja'                 => 'nullable|string',
            'tebing_praktek_kerja_evidence'        => 'nullable|string',
            'tebing_apd'                           => 'nullable|string',
            'tebing_apd_evidence'                  => 'nullable|string',

            /* =====================
             * Air & Lumpur
             * ===================== */
            'air_lumpur_ada'                       => 'nullable|boolean',
            'air_lumpur_pekerjaan_kritis'          => 'nullable|string',
            'air_lumpur_prosedur'                  => 'nullable|in:Sudah,Belum',
            'air_lumpur_pelanggaran'               => 'nullable|in:Ada,Tidak Ada',
            'air_lumpur_engineering'               => 'nullable|string',
            'air_lumpur_engineering_evidence'      => 'nullable|string',
            'air_lumpur_administratif'             => 'nullable|string',
            'air_lumpur_administratif_evidence'    => 'nullable|string',
            'air_lumpur_apd'                       => 'nullable|string',
            'air_lumpur_apd_evidence'              => 'nullable|string',

            /* =====================
             * Chainsaw
             * ===================== */
            'chainsaw_ada'                         => 'nullable|boolean',
            'chainsaw_pekerjaan_kritis'            => 'nullable|string',
            'chainsaw_prosedur'                    => 'nullable|in:Sudah,Belum',
            'chainsaw_pelanggaran'                 => 'nullable|in:Ada,Tidak Ada',
            'chainsaw_engineering'                 => 'nullable|string',
            'chainsaw_engineering_evidence'        => 'nullable|string',
            'chainsaw_administratif'               => 'nullable|string',
            'chainsaw_administratif_evidence'      => 'nullable|string',
            'chainsaw_praktek_kerja'               => 'nullable|string',
            'chainsaw_praktek_kerja_evidence'      => 'nullable|string',
            'chainsaw_apd'                         => 'nullable|string',
            'chainsaw_apd_evidence'                => 'nullable|string',

            /* =====================
             * LOTO & Penanganan Ban
             * ===================== */
            'loto_ada'                             => 'nullable|boolean',
            'loto_pekerjaan_kritis'                => 'nullable|string',
            'loto_prosedur'                        => 'nullable|in:Sudah,Belum',
            'loto_pelanggaran'                     => 'nullable|in:Ada,Tidak Ada',
            'loto_engineering'                     => 'nullable|string',
            'loto_engineering_evidence'            => 'nullable|string',
            'loto_administratif'                   => 'nullable|string',
            'loto_administratif_evidence'          => 'nullable|string',
            'loto_praktek_kerja'                   => 'nullable|string',
            'loto_praktek_kerja_evidence'          => 'nullable|string',
            'loto_apd'                             => 'nullable|string',
            'loto_apd_evidence'                    => 'nullable|string',

            /* =====================
             * Pengangkatan & Penyangga
             * ===================== */
            'lifting_ada'                          => 'nullable|boolean',
            'lifting_pekerjaan_kritis'             => 'nullable|string',
            'lifting_prosedur'                     => 'nullable|in:Sudah,Belum',
            'lifting_pelanggaran'                  => 'nullable|in:Ada,Tidak Ada',
            'lifting_engineering'                  => 'nullable|string',
            'lifting_engineering_evidence'         => 'nullable|string',
            'lifting_administratif'                => 'nullable|string',
            'lifting_administratif_evidence'       => 'nullable|string',
            'lifting_apd'                          => 'nullable|string',
            'lifting_apd_evidence'                 => 'nullable|string',

            /* =====================
             * Blasting
             * ===================== */
            'blasting_ada'                         => 'nullable|boolean',
            'blasting_pekerjaan_kritis'            => 'nullable|string',
            'blasting_prosedur'                    => 'nullable|in:Sudah,Belum',
            'blasting_pelanggaran'                 => 'nullable|in:Ada,Tidak Ada',
            'blasting_engineering'                 => 'nullable|string',
            'blasting_engineering_evidence'        => 'nullable|string',
            'blasting_administratif'               => 'nullable|string',
            'blasting_administratif_evidence'      => 'nullable|string',
            'blasting_praktek_kerja'               => 'nullable|string',
            'blasting_praktek_kerja_evidence'      => 'nullable|string',
            'blasting_apd'                         => 'nullable|string',
            'blasting_apd_evidence'                => 'nullable|string',

            /* =====================
             * Pekerjaan Kritis Baru
             * ===================== */
            'kritis_baru_ada'                      => 'nullable|boolean',
            'kritis_baru_pekerjaan'                => 'nullable|string',
            'kritis_baru_prosedur'                 => 'nullable|in:Ada,Tidak Ada',
            'kritis_baru_dipahami'                 => 'nullable|in:Sudah,Belum',
            'kritis_baru_pelanggaran'              => 'nullable|in:Ada,Tidak Ada',
            'kritis_baru_engineering'              => 'nullable|string',
            'kritis_baru_engineering_evidence'     => 'nullable|string',
            'kritis_baru_administratif'            => 'nullable|string',
            'kritis_baru_administratif_evidence'   => 'nullable|string',
            'kritis_baru_praktek_kerja'            => 'nullable|string',
            'kritis_baru_praktek_kerja_evidence'   => 'nullable|string',
            'kritis_baru_apd'                      => 'nullable|string',
            'kritis_baru_apd_evidence'             => 'nullable|string',
        ];
    }
}
