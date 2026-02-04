<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CcmReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CcmReportController extends Controller
{
    /* =========================================================
     * INDEX
     * ========================================================= */
    public function index()
    {
        $reports = CcmReport::orderBy('waktu_pelaporan', 'desc')
            ->paginate(10);

        return view('admin.ccm.index', compact('reports'));
    }

    /* =========================================================
     * CREATE
     * ========================================================= */
    public function create()
    {
        return view('admin.ccm.create');
    }

    /* =========================================================
     * STORE
     * ========================================================= */
    public function store(Request $request)
    {
        $validated = $request->validate(
            array_merge(
                $this->baseRules(true),
                $this->conditionalRules($request)
            )
        );

        // ===============================
        // HANDLE FILE UPLOAD (CREATE)
        // ===============================
        foreach ($request->allFiles() as $field => $file) {
            if ($file && is_file($file)) {
                $validated[$field] = $file->store('ccm-evidence', 'public');
            }
        }

        CcmReport::create($validated);

        return redirect()
            ->route('ccm-reports.index')
            ->with('success', 'CCM Report berhasil disimpan');
    }

    /* =========================================================
     * SHOW
     * ========================================================= */
    public function show($id)
    {
        $report = CcmReport::findOrFail($id);
        return view('admin.ccm.show', compact('report'));
    }

    /* =========================================================
     * EDIT
     * ========================================================= */
    public function edit($id)
    {
        $report = CcmReport::findOrFail($id);
        return view('admin.ccm.edit', compact('report'));
    }

    /* =========================================================
     * UPDATE
     * ========================================================= */
    public function update(Request $request, $id)
    {
        $report = CcmReport::findOrFail($id);

        $validated = $request->validate(
            array_merge(
                $this->baseRules(false),
                $this->conditionalRules($request)
            )
        );

        // ===============================
        // HANDLE FILE UPLOAD (UPDATE)
        // ===============================
        foreach ($request->allFiles() as $field => $file) {
            if ($file && is_file($file)) {

                // hapus file lama jika ada
                if (!empty($report->$field)) {
                    Storage::disk('public')->delete($report->$field);
                }

                $validated[$field] = $file->store('ccm-evidence', 'public');
            }
        }

        $report->update($validated);

        return redirect()
            ->route('ccm-reports.index')
            ->with('success', 'CCM Report berhasil diperbarui');
    }

    /* =========================================================
     * DELETE
     * ========================================================= */
    public function destroy($id)
    {
        $report = CcmReport::findOrFail($id);

        // hapus semua evidence file
        foreach ($report->getAttributes() as $field => $value) {
            if (str_ends_with($field, '_evidence') && $value) {
                Storage::disk('public')->delete($value);
            }
        }

        $report->delete();

        return redirect()
            ->route('ccm-reports.index')
            ->with('success', 'CCM Report berhasil dihapus');
    }

    /* =========================================================
     * BASE RULES
     * ========================================================= */
    private function baseRules(bool $isCreate = true): array
    {
        $required = $isCreate ? 'required' : 'sometimes';

        return [
            'waktu_pelaporan' => "$required|date",
            'jobsite'         => "$required|in:AAP-BGG,AAP-SBS,ABN-DBK,ABC-POS",
            'nama_pelapor'    => "$required|string",

            // FLAG KEGIATAN (HARUS *_ada_kegiatan)
            'kendaraan_ada_kegiatan' => 'nullable|boolean',
            'izin_kerja_ada_kegiatan' => 'nullable|boolean',
            'tebing_ada_kegiatan' => 'nullable|boolean',
            'air_lumpur_ada_kegiatan' => 'nullable|boolean',
            'chainsaw_ada_kegiatan' => 'nullable|boolean',
            'loto_ada_kegiatan' => 'nullable|boolean',
            'lifting_ada_kegiatan' => 'nullable|boolean',
            'blasting_ada_kegiatan' => 'nullable|boolean',
            'kritis_baru_ada_kegiatan' => 'nullable|boolean',
        ];
    }

    /* =========================================================
     * CONDITIONAL RULES (FINAL & FIXED)
     * ========================================================= */
    private function conditionalRules(Request $request): array
    {
        $rules = [];

        $sections = [
            'kendaraan'   => ['engineering','administratif','praktek_kerja','apd'],
            'izin_kerja'  => ['engineering','administratif','praktek_kerja','apd'],
            'tebing'      => ['engineering','administratif','praktek_kerja','apd'],
            'air_lumpur'  => ['engineering','administratif','apd'],
            'chainsaw'    => ['engineering','administratif','praktek_kerja','apd'],
            'loto'        => ['engineering','administratif','praktek_kerja','apd'],
            'lifting'     => ['engineering','administratif','apd'],
            'blasting'    => ['engineering','administratif','praktek_kerja','apd'],
            'kritis_baru' => ['engineering','administratif','praktek_kerja','apd'],
        ];

        foreach ($sections as $section => $controls) {

            // ⬇️ FIX UTAMA: PAKAI *_ada_kegiatan
            if ($request->boolean($section . '_ada_kegiatan')) {

                foreach ($controls as $ctrl) {
                    $rules[$section.'_'.$ctrl] = 'required|string';
                    $rules[$section.'_'.$ctrl.'_evidence']
                        = 'required|file|image|max:5120';
                }

            } else {
                $rules[$section.'_tidak_ada_alasan'] = 'required|string';
            }
        }

        return $rules;
    }
}
