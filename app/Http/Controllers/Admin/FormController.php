<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Form, Department, Company, Site};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage, Log, Schema};
use Illuminate\Validation\Rule;
use Throwable;

class FormController extends Controller
{
    /** Allowed values */
    private const DOC_TYPES  = ['SOP', 'IK', 'FORM'];
    private const FORM_TYPES = ['builder', 'pdf'];

    // =======================
    // INDEX / LIST
    // =======================
    public function index(Request $r)
    {
        $q = Form::with(['department:id,name', 'creator:id,name', 'company:id,code,name', 'site:id,name,company_id'])
            ->latest();

        if ($r->filled('department_id')) {
            $q->where('department_id', (int) $r->department_id);
        }
        if ($r->filled('company_id') && Schema::hasColumn('forms', 'company_id')) {
            $q->where('company_id', (int) $r->company_id);
        }
        if ($r->filled('site_id') && Schema::hasColumn('forms', 'site_id')) {
            $q->where('site_id', (int) $r->site_id);
        }
        if ($r->filled('doc_type') && Schema::hasColumn('forms', 'doc_type')) {
            $docType = strtoupper((string) $r->doc_type);
            if (in_array($docType, self::DOC_TYPES, true)) {
                $q->where('doc_type', $docType);
            }
        }

        $allowed = [10, 20, 50, 100];
        $perPage = (int) $r->input('per_page', 10);
        if (!in_array($perPage, $allowed, true)) $perPage = 10;

        $forms       = $q->paginate($perPage)->appends($r->only('department_id','company_id','site_id','doc_type','per_page'));
        $departments = Department::orderBy('name')->get(['id','name']);
        $companies   = Company::orderBy('code')->get(['id','code','name']);
        $sites       = Site::orderBy('name')->get(['id','name','company_id']);

        return view('admin.forms.index', compact('forms','departments','companies','sites'));
    }

    // =======================
    // CREATE
    // =======================
    public function create()
    {
        $departments = Department::orderBy('name')->get(['id','name']);
        $companies   = Company::orderBy('code')->get(['id','code','name']);
        $sites       = Site::orderBy('name')->get(['id','name','company_id']); // dipakai buat SITES_BY_COMPANY di Blade

        return view('admin.forms.create', compact('departments','companies','sites'));
    }

    // =======================
    // STORE
    // =======================
    public function store(Request $r)
    {
        Log::info('forms.store:start', ['input' => $r->except(['pdf'])]);

        // VALIDASI DASAR
        $validated = $r->validate([
            'company_id'    => ['required', 'exists:companies,id'],
            'site_id'       => ['nullable', 'exists:sites,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'doc_type'      => ['required', Rule::in(self::DOC_TYPES)],
            'type'          => ['required', Rule::in(self::FORM_TYPES)],
            'schema'        => ['nullable'], // validasi JSON manual di bawah (boleh array/string)
            'pdf'           => ['required_if:type,pdf', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:30720'],
            'is_active'     => ['nullable', 'boolean'],
        ], [
            'pdf.required_if' => 'Saat memilih tipe File, harap unggah file referensi.',
        ]);

        // Validasi relasi: jika site_id diisi, harus milik company_id yg sama
        if (!empty($validated['site_id'])) {
            $ok = Site::where('id', $validated['site_id'])
                ->where('company_id', $validated['company_id'])
                ->exists();
            if (!$ok) {
                return back()
                    ->withErrors(['site_id' => 'Site tidak sesuai dengan perusahaan yang dipilih.'])
                    ->withInput();
            }
        }

        try {
            return DB::transaction(function () use ($r, $validated) {
                // ===== FILE HANDLING =====
                $filePath = null;
                if ($validated['type'] === 'pdf' && $r->hasFile('pdf')) {
                    $uploaded   = $r->file('pdf');
                    $storedTemp = $uploaded->store('forms/tmp', 'public');

                    $ext    = strtolower($uploaded->getClientOriginalExtension());
                    $outRel = 'forms/files/' . uniqid('form_') . '.' . $ext;
                    $outAbs = Storage::disk('public')->path($outRel);

                    Storage::disk('public')->makeDirectory('forms/files');

                    $ok = false;
                    if ($ext === 'pdf') {
                        $ok = $this->compressPdf(Storage::disk('public')->path($storedTemp), $outAbs);
                    } elseif (in_array($ext, ['docx','xlsx'], true)) {
                        $ok = $this->recompressOfficeZip(Storage::disk('public')->path($storedTemp), $outAbs);
                    }

                    if (!$ok) {
                        // fallback: simpan apa adanya
                        $outRel = $uploaded->store('forms/files', 'public');
                    }

                    Storage::disk('public')->delete($storedTemp);
                    $filePath = $outRel;
                }

                // ===== SCHEMA HANDLING =====
                $schemaArr = null;
                if (($validated['type'] ?? null) === 'builder') {
                    $raw = $r->input('schema');
                    if (is_array($raw)) {
                        $schemaArr = $raw;
                    } elseif (is_string($raw) && $raw !== '') {
                        $decoded   = json_decode($raw, true);
                        $schemaArr = is_array($decoded) ? $decoded : ['fields' => []];
                    } else {
                        $schemaArr = ['fields' => []];
                    }
                    if (!isset($schemaArr['fields']) || !is_array($schemaArr['fields'])) {
                        $schemaArr = ['fields' => []];
                    }
                }

                // ===== PAYLOAD =====
                $payload = [
                    'company_id'    => (int) $validated['company_id'],
                    'site_id'       => !empty($validated['site_id']) ? (int) $validated['site_id'] : null,
                    'department_id' => (int) $validated['department_id'],
                    'created_by'    => optional($r->user())->id,
                    'title'         => $validated['title'],
                    'doc_type'      => strtoupper($validated['doc_type']),
                    'type'          => $validated['type'],
                    'schema'        => $schemaArr,
                    'pdf_path'      => $filePath,
                    'is_active'     => $r->boolean('is_active', true),
                ];

                Log::info('forms.store:payload_before_create', $payload);

                $form = Form::create($payload);

                Log::info('forms.store:created', ['id' => $form->id]);

                if (!$form || !$form->id) {
                    Log::error('forms.store:failed_no_id');
                    return back()
                        ->withErrors(['general' => 'Form gagal dibuat (ID kosong). Coba lagi.'])
                        ->withInput();
                }

                return redirect()
                    ->route('admin.forms.edit', $form)
                    ->with('ok', 'Form dibuat');
            });
        } catch (Throwable $e) {
            Log::error('forms.store:error', [
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 2000),
            ]);

            return back()
                ->withErrors(['general' => 'Terjadi kesalahan saat menyimpan: '.$e->getMessage()])
                ->withInput();
        }
    }

    // =======================
    // EDIT
    // =======================
    public function edit(Form $form)
    {
        $departments = Department::orderBy('name')->get(['id','name']);
        $companies   = Company::orderBy('code')->get(['id','code','name']);
        $sites       = Site::orderBy('name')->get(['id','name','company_id']);

        return view('admin.forms.edit', compact('form','departments','companies','sites'));
    }

    // =======================
    // UPDATE
    // =======================
    public function update(Request $r, Form $form)
    {
        // Quick update hanya doc_type (AJAX builder)
        $onlyDocType = $r->has('doc_type')
            && !$r->hasAny(['company_id','site_id','department_id','title','type','schema','pdf','is_active']);

        if ($onlyDocType) {
            $r->validate(['doc_type' => ['required', Rule::in(self::DOC_TYPES)]]);
            $form->update(['doc_type' => strtoupper($r->doc_type)]);
            return response()->json(['ok' => true, 'doc_type' => $form->doc_type]);
        }

        $validated = $r->validate([
            'company_id'    => ['required', 'exists:companies,id'],
            'site_id'       => ['nullable', 'exists:sites,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'doc_type'      => ['required', Rule::in(self::DOC_TYPES)],
            'type'          => ['required', Rule::in(self::FORM_TYPES)],
            'schema'        => ['nullable'],
            'pdf'           => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:30720'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        // Validasi relasi site-company saat update
        if (!empty($validated['site_id'])) {
            $ok = Site::where('id', $validated['site_id'])
                ->where('company_id', $validated['company_id'])
                ->exists();
            if (!$ok) {
                return back()
                    ->withErrors(['site_id' => 'Site tidak sesuai dengan perusahaan yang dipilih.'])
                    ->withInput();
            }
        }

        try {
            return DB::transaction(function () use ($r, $validated, $form) {
                Log::info('forms.update:start', ['id' => $form->id, 'input' => $validated]);

                $filePath = $form->pdf_path;

                // ganti file jika type=pdf dan ada file baru
                if ($validated['type'] === 'pdf' && $r->hasFile('pdf')) {
                    if ($filePath) Storage::disk('public')->delete($filePath);

                    $uploaded   = $r->file('pdf');
                    $storedTemp = $uploaded->store('forms/tmp', 'public');

                    $ext    = strtolower($uploaded->getClientOriginalExtension());
                    $outRel = 'forms/files/' . uniqid('form_') . '.' . $ext;
                    $outAbs = Storage::disk('public')->path($outRel);

                    Storage::disk('public')->makeDirectory('forms/files');

                    $ok = false;
                    if ($ext === 'pdf') {
                        $ok = $this->compressPdf(Storage::disk('public')->path($storedTemp), $outAbs);
                    } elseif (in_array($ext, ['docx','xlsx'], true)) {
                        $ok = $this->recompressOfficeZip(Storage::disk('public')->path($storedTemp), $outAbs);
                    }

                    if (!$ok) {
                        $outRel = $uploaded->store('forms/files', 'public');
                    }

                    Storage::disk('public')->delete($storedTemp);
                    $filePath = $outRel;
                }

                // jika pindah dari pdf -> builder, hapus file lama
                if ($validated['type'] === 'builder' && $form->type === 'pdf' && $form->pdf_path) {
                    Storage::disk('public')->delete($form->pdf_path);
                    $filePath = null;
                }

                // schema (string/array)
                $schemaArr = null;
                if (($validated['type'] ?? null) === 'builder') {
                    $raw = $r->input('schema');
                    if (is_array($raw)) {
                        $schemaArr = $raw;
                    } elseif (is_string($raw) && $raw !== '') {
                        $decoded   = json_decode($raw, true);
                        $schemaArr = is_array($decoded) ? $decoded : ['fields' => []];
                    } else {
                        $schemaArr = ['fields' => []];
                    }
                    if (!isset($schemaArr['fields']) || !is_array($schemaArr['fields'])) {
                        $schemaArr = ['fields' => []];
                    }
                }

                $form->update([
                    'company_id'    => (int) $validated['company_id'],
                    'site_id'       => !empty($validated['site_id']) ? (int) $validated['site_id'] : null,
                    'department_id' => (int) $validated['department_id'],
                    'title'         => $validated['title'],
                    'doc_type'      => strtoupper($validated['doc_type']),
                    'type'          => $validated['type'],
                    'schema'        => $schemaArr,
                    'pdf_path'      => $filePath,
                    'is_active'     => $r->boolean('is_active', true),
                ]);

                Log::info('forms.update:done', ['id' => $form->id]);

                return back()->with('ok', 'Form diperbarui');
            });
        } catch (Throwable $e) {
            Log::error('forms.update:error', [
                'id'    => $form->id,
                'msg'   => $e->getMessage(),
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ]);
            return back()
                ->withErrors(['general' => 'Gagal update: '.$e->getMessage()])
                ->withInput();
        }
    }

    // =======================
    // DESTROY
    // =======================
    public function destroy(Form $form)
    {
        try {
            if ($form->pdf_path) {
                Storage::disk('public')->delete($form->pdf_path);
            }
            $form->delete();
            return redirect()->route('admin.forms.index')->with('ok', 'Form dihapus');
        } catch (Throwable $e) {
            Log::error('forms.destroy:error', ['id' => $form->id, 'msg' => $e->getMessage()]);
            return back()->withErrors(['general' => 'Gagal hapus: '.$e->getMessage()]);
        }
    }

    // =======================
    // BUILDER VIEW + SAVE
    // =======================
    public function builder(Form $form)
    {
        abort_if($form->type !== 'builder', 404, 'Hanya untuk form tipe builder');
        $schema = $form->schema ?? ['fields' => []];
        return view('admin.forms.builder', compact('form','schema'));
    }

    public function saveSchema(Request $r, Form $form)
    {
        abort_if($form->type !== 'builder', 404);

        $r->validate(['schema' => ['required']]);

        $decoded = is_array($r->schema) ? $r->schema : json_decode((string) $r->schema, true);
        if (!is_array($decoded) || !isset($decoded['fields']) || !is_array($decoded['fields'])) {
            return back()
                ->withErrors(['schema' => 'Schema tidak valid: butuh objek dengan key "fields" berupa array.'])
                ->withInput();
        }

        $form->update(['schema' => $decoded]);
        return redirect()->route('admin.forms.edit', $form)->with('ok', 'Schema tersimpan');
    }

    // =======================
    // HELPERS
    // =======================
    private function compressPdf(string $inPath, string $outPath): bool
    {
        if (!function_exists('shell_exec')) {
            Log::warning('PDF compress skipped: shell_exec() is disabled.');
            return false;
        }

        $isWin = (PHP_OS_FAMILY ?? php_uname('s')) === 'Windows';
        $candidates = [];

        if ($isWin) {
            $where = @shell_exec('where gswin64c 2>NUL') ?: @shell_exec('where gswin32c 2>NUL');
            if ($where) $candidates[] = trim($where);
            $candidates[] = 'C:\\Program Files\\gs\\gs10.00.0\\bin\\gswin64c.exe';
            $candidates[] = 'C:\\Program Files\\gs\\gs9.56.1\\bin\\gswin64c.exe';
            $candidates[] = 'C:\\Program Files\\gs\\gs9.55.0\\bin\\gswin64c.exe';
        } else {
            $which = @shell_exec('command -v gs 2>/dev/null') ?: @shell_exec('which gs 2>/dev/null');
            if ($which) $candidates[] = trim($which);
            $candidates[] = '/usr/bin/gs';
            $candidates[] = '/usr/local/bin/gs';
        }

        $gs = null;
        foreach ($candidates as $bin) {
            if ($bin && is_file($bin) && is_executable($bin)) { $gs = $bin; break; }
        }
        if (!$gs) { Log::warning('PDF compress skipped: Ghostscript not found.'); return false; }

        $preset = 'screen';
        $cmd = escapeshellcmd($gs)
             .' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4'
             .' -dPDFSETTINGS=/'.$preset
             .' -dNOPAUSE -dQUIET -dBATCH'
             .' -sOutputFile='.escapeshellarg($outPath)
             .' '.escapeshellarg($inPath)
             .' 2>&1';

        @shell_exec($cmd);

        return is_file($outPath) && filesize($outPath) > 0;
    }

    private function recompressOfficeZip(string $inPath, string $outPath): bool
    {
        if (!class_exists(\ZipArchive::class)) return false;

        $zipIn = new \ZipArchive();
        if ($zipIn->open($inPath) !== true) return false;

        $zipOut = new \ZipArchive();
        if ($zipOut->open($outPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $zipIn->close();
            return false;
        }

        for ($i = 0; $i < $zipIn->numFiles; $i++) {
            $stat   = $zipIn->statIndex($i);
            $name   = $stat['name'];
            $stream = $zipIn->getStream($name);
            if (!$stream) continue;

            $data = stream_get_contents($stream);
            fclose($stream);

            $zipOut->addFromString($name, $data);
            if (defined('\ZipArchive::CM_DEFLATE')) {
                $zipOut->setCompressionName($name, \ZipArchive::CM_DEFLATE, 9);
            }
        }

        $zipOut->close();
        $zipIn->close();

        return is_file($outPath) && filesize($outPath) > 0;
    }
}
