<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CcmReport;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

// --- IMPORT LIBRARY INTERVENTION IMAGE V3 ---
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; 
// ------------------------------------------

class CcmReportController extends Controller
{
    /* =========================================================
     * INDEX
     * ========================================================= */
    public function index(Request $request)
    {
        $request->validate(['date_from' => ['nullable','date'], 'date_to' => ['nullable','date']]);
        $jobsiteLabels = $this->jobsiteOptions()->pluck('label', 'value');
        $allowedJobsites = $jobsiteLabels->keys()->all();
        $siteId = \App\Support\SiteSelection::id($request);
        $site = $siteId ? Site::with('company:id,code,name')->findOrFail($siteId) : null;
        $jobsite = $site ? ($site->company ? (($site->company->code ?: $site->company->name).'-'.($site->code ?: $site->name)) : ($site->code ?: $site->name)) : null;
        $reports = CcmReport::when(!auth()->user()->isSuperAdmin(), fn ($q) => $q->whereIn('jobsite', $allowedJobsites ?: ['__none__']))
            ->when($jobsite, fn ($q) => $q->where('jobsite', $jobsite))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('jobsite', 'like', '%'.$request->string('q').'%')->orWhere('nama_pelapor', 'like', '%'.$request->string('q').'%')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('waktu_pelaporan', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('waktu_pelaporan', '<=', $request->date('date_to')))
            ->orderBy('waktu_pelaporan', 'desc')
            ->paginate(\App\Support\Listing::perPage())->withQueryString();

        return view('admin.ccm.index', compact('reports', 'jobsiteLabels'));
    }

    /* =========================================================
     * CREATE
     * ========================================================= */
    public function create()
    {
        $jobsites = $this->jobsiteOptions();
        $allowedSiteIds = auth()->user()->allowedSiteIds();
        $reporters = User::query()->select('id', 'name', 'nik')
            ->when(is_array($allowedSiteIds), fn ($query) => $query->where(fn ($scope) =>
                $scope->whereKey(auth()->id())->orWhereHas('sites', fn ($sites) => $sites->whereIn('sites.id', $allowedSiteIds))
            ))
            ->orderBy('name')->get();

        return view('admin.ccm.create', compact('jobsites', 'reporters'));
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
        $this->authorizeJobsite($validated['jobsite']);

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
        $this->authorizeJobsite($report->jobsite);
        $jobsiteLabel = $this->jobsiteOptions()->firstWhere('value', $report->jobsite)['label'] ?? $report->jobsite;
        return view('admin.ccm.show', compact('report', 'jobsiteLabel'));
    }

    /* =========================================================
     * EDIT
     * ========================================================= */
    public function edit($id)
    {
        $report = CcmReport::findOrFail($id);
        $this->authorizeJobsite($report->jobsite);

        $jobsites = $this->jobsiteOptions();
        $allowedSiteIds = auth()->user()->allowedSiteIds();
        $reporters = User::query()->select('id', 'name', 'nik')
            ->when(is_array($allowedSiteIds), fn ($query) => $query->where(fn ($scope) =>
                $scope->whereKey(auth()->id())->orWhereHas('sites', fn ($sites) => $sites->whereIn('sites.id', $allowedSiteIds))
            ))
            ->orderBy('name')->get();

        return view('admin.ccm.edit', compact('report', 'jobsites', 'reporters'));
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
        $this->authorizeJobsite($report->jobsite);
        $this->authorizeJobsite($validated['jobsite']);

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
        $this->authorizeJobsite($report->jobsite);

        // Hapus semua file fisik dari storage agar tidak memenuhi server
        foreach ($report->getAttributes() as $field => $value) {
            if (str_ends_with($field, '_evidence') && !empty($value)) {
                if (Storage::disk('mandala_uploads')->exists($value)) {
                    Storage::disk('mandala_uploads')->delete($value);
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
            'jobsite'         => ['required', 'string', 'max:255', Rule::in($this->jobsiteOptions()->pluck('value')->all())],
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
                    if (Storage::disk('mandala_uploads')->exists($existingReport->$field)) {
                        Storage::disk('mandala_uploads')->delete($existingReport->$field);
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
                    Storage::disk('mandala_uploads')->put($filename, (string) $encoded);

                    // Masukkan path ke array data yang akan di-insert/update
                    $validatedData[$field] = $filename;

                } catch (\Exception $e) {
                    // Fallback jika library gagal proses: simpan file asli
                    $path = $file->store('ccm-evidence', 'mandala_uploads');
                    $validatedData[$field] = $path;
                }
            }
        }

        return $validatedData;
    }

    private function jobsiteOptions()
    {
        $query = Site::with('company:id,code,name')
            ->orderBy('name')
            ->when(is_array(auth()->user()->allowedSiteIds()), function ($q) {
                $q->whereIn('id', auth()->user()->allowedSiteIds() ?: [-1]);
            });

        return $query->get(['id', 'code', 'name', 'company_id'])
            ->map(function (Site $site) {
                $value = $this->jobsiteLabel($site);

                return [
                    'value' => $value,
                    'label' => $site->displayLabel(),
                ];
            })
            ->values();
    }

    private function authorizeJobsite(?string $jobsite): void
    {
        if (auth()->user()->isSuperAdmin()) return;
        if (empty($jobsite)) abort(403);
        abort_unless($this->jobsiteOptions()->pluck('value')->contains($jobsite), 403);
    }

    private function jobsiteLabel(Site $site): string
    {
        $company = $site->company?->code ?: $site->company?->name;
        $siteName = $site->code ?: $site->name;

        return $company ? "{$company}-{$siteName}" : $siteName;
    }
}
