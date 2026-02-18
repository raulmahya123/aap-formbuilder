<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CcmReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// --- IMPORT LIBRARY INTERVENTION IMAGE V3 ---
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; 
// ------------------------------------------

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
        // 1. Validasi gabungan Base + Section Rules
        $rules = array_merge(
            $this->baseRules(),
            $this->getSectionRules($request, true) // true = isCreate (Evidence Wajib)
        );

        $validated = $request->validate($rules);

        // 2. Handle File Upload (Dengan Kompresi Image V3)
        $validated = $this->handleFileUploads($request, $validated);

        // 3. Simpan ke Database
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

        $rules = array_merge(
            $this->baseRules(),
            $this->getSectionRules($request, false) // false = isUpdate (Evidence Opsional)
        );

        $validated = $request->validate($rules);

        // Handle File Upload (Ganti file lama jika ada upload baru)
        $validated = $this->handleFileUploads($request, $validated, $report);

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

        // Hapus semua file fisik dari storage agar tidak memenuhi server
        foreach ($report->getAttributes() as $field => $value) {
            if (str_ends_with($field, '_evidence') && !empty($value)) {
                if (Storage::disk('public')->exists($value)) {
                    Storage::disk('public')->delete($value);
                }
            }
        }

        $report->delete();

        return redirect()
            ->route('ccm-reports.index')
            ->with('success', 'CCM Report berhasil dihapus');
    }

    /* =========================================================
     * HELPER: BASE RULES (Sesuai Kolom DB Asli)
     * ========================================================= */
    private function baseRules(): array
    {
        return [
            'waktu_pelaporan' => 'required|date',
            'jobsite'         => 'required|in:AAP-BGG,AAP-SBS,ABN-DBK,ABC-POS',
            'nama_pelapor'    => 'required|string|max:255',

            // Boolean Flags sesuai migrasi database Anda
            'kendaraan_ada_kegiatan' => 'nullable|boolean',
            'izin_kerja_ada'         => 'nullable|boolean',
            'tebing_ada'             => 'nullable|boolean',
            'air_lumpur_ada'         => 'nullable|boolean',
            'chainsaw_ada'           => 'nullable|boolean',
            'loto_ada'               => 'nullable|boolean',
            'lifting_ada'            => 'nullable|boolean',
            'blasting_ada'           => 'nullable|boolean',
            'kritis_baru_ada'        => 'nullable|boolean',
        ];
    }

    /* =========================================================
     * HELPER: SECTION RULES (Logika Validasi Dinamis & Sinkron Migrasi)
     * ========================================================= */
    private function getSectionRules(Request $request, bool $isCreate): array
    {
        $rules = [];

        // Definisi Kontrol sesuai migrasi Anda
        $sections = [
            'kendaraan'   => ['engineering', 'administratif', 'praktek_kerja', 'apd'],
            'izin_kerja'  => ['engineering', 'administratif', 'praktek_kerja', 'apd'],
            'tebing'      => ['engineering', 'administratif', 'praktek_kerja', 'apd'],
            'air_lumpur'  => ['engineering', 'administratif', 'apd'], 
            'chainsaw'    => ['engineering', 'administratif', 'praktek_kerja', 'apd'],
            'loto'        => ['engineering', 'administratif', 'praktek_kerja', 'apd'],
            'lifting'     => ['engineering', 'administratif', 'apd'],
            'blasting'    => ['engineering', 'administratif', 'praktek_kerja', 'apd'],
            'kritis_baru' => ['engineering', 'administratif', 'praktek_kerja', 'apd'],
        ];

        foreach ($sections as $prefix => $controls) {
            
            // LOGIC NAMA KOLOM FLAG (Kendaraan beda sendiri di DB)
            $flag = ($prefix === 'kendaraan') ? 'kendaraan_ada_kegiatan' : $prefix . '_ada';

            if ($request->boolean($flag)) {
                
                // 1. Validasi Kolom Deskripsi Teks (Menyesuaikan ENUM di Migrasi)
                if ($prefix === 'kritis_baru') {
                    $rules[$prefix . '_pekerjaan']   = 'required|string';
                    $rules[$prefix . '_prosedur']    = 'required|in:Ada,Tidak Ada'; // Sesuai Migrasi: enum('Ada','Tidak Ada')
                    $rules[$prefix . '_dipahami']    = 'required|in:Sudah,Belum';   // Sesuai Migrasi: enum('Sudah','Belum')
                    $rules[$prefix . '_pelanggaran'] = 'required|in:Ada,Tidak Ada'; // Sesuai Migrasi: enum('Ada','Tidak Ada')
                } else {
                    $rules[$prefix . '_pekerjaan_kritis'] = 'required|string';
                    $rules[$prefix . '_prosedur']         = 'required|in:Sudah,Belum';   // Sesuai Migrasi: enum('Sudah','Belum')
                    $rules[$prefix . '_pelanggaran']      = 'required|in:Ada,Tidak Ada'; // Sesuai Migrasi: enum('Ada','Tidak Ada')
                }

                // 2. Validasi Kontrol & Evidence
                foreach ($controls as $ctrl) {
                    $rules["{$prefix}_{$ctrl}"] = 'required|string';
                    
                    $fileRule = $isCreate ? 'required' : 'nullable';
                    $rules["{$prefix}_{$ctrl}_evidence"] = "$fileRule|file|image|max:10240"; // Max 10MB
                }

            } else {
                // Sesuai logic Anda: Jika tidak ada kegiatan, alasan wajib diisi
                // Catatan: Pastikan kolom '..._tidak_ada_alasan' ada di Model fillable jika ingin disimpan
                $rules["{$prefix}_tidak_ada_alasan"] = 'required|string';
            }
        }

        return $rules;
    }

    /* =========================================================
     * HELPER: HANDLE FILE UPLOADS (KOMPRESI V3)
     * ========================================================= */
    private function handleFileUploads(Request $request, array $validatedData, ?CcmReport $existingReport = null): array
    {
        $manager = new ImageManager(new Driver());

        foreach ($request->allFiles() as $field => $file) {
            
            if ($file && $file->isValid()) {
                
                // Hapus file lama jika proses Update
                if ($existingReport && !empty($existingReport->$field)) {
                    if (Storage::disk('public')->exists($existingReport->$field)) {
                        Storage::disk('public')->delete($existingReport->$field);
                    }
                }

                // Generate nama unik
                $filename = 'ccm-evidence/' . Str::random(40) . '.jpg';
                
                try {
                    // Proses Kompresi Image Manager V3
                    $image = $manager->read($file);

                    // Resize ke lebar 1000px, tinggi otomatis (aspect ratio tetap)
                    $image->scale(width: 1000);

                    // Encode ke format JPG dengan kualitas 70 (Kompresi Efektif)
                    $encoded = $image->toJpeg(quality: 70);

                    // Simpan ke storage
                    Storage::disk('public')->put($filename, (string) $encoded);

                    // Masukkan path ke array data yang akan di-insert/update
                    $validatedData[$field] = $filename;

                } catch (\Exception $e) {
                    // Fallback jika library gagal proses: simpan file asli
                    $path = $file->store('ccm-evidence', 'public');
                    $validatedData[$field] = $path;
                }
            }
        }

        return $validatedData;
    }
}