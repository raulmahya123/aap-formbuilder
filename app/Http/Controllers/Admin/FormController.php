<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Form, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, Log, Schema};
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FormController extends Controller
{
    /** Daftar nilai yang diizinkan */
    private const DOC_TYPES = ['SOP', 'IK', 'FORM'];
    private const FORM_TYPES = ['builder', 'pdf'];

    public function index(Request $r)
    {
        $q = Form::with(['department', 'creator'])->latest();

        if ($r->filled('department_id')) {
            $q->where('department_id', $r->department_id);
        }

        // filter doc_type (opsional): ?doc_type=SOP|IK|FORM
        if ($r->filled('doc_type') && Schema::hasColumn('forms', 'doc_type')) {
            $docType = strtoupper((string)$r->doc_type);
            if (in_array($docType, self::DOC_TYPES, true)) {
                $q->where('doc_type', $docType);
            }
        }

        $forms = $q->paginate(20)->appends($r->only('department_id', 'doc_type'));
        $departments = Department::orderBy('name')->get();

        return view('admin.forms.index', compact('forms', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.forms.create', compact('departments'));
    }

    public function store(Request $r)
    {
        // Validasi: tanpa default DB → wajib pilih doc_type di form
        $r->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'doc_type'      => ['required', Rule::in(self::DOC_TYPES)],
            'type'          => ['required', Rule::in(self::FORM_TYPES)],
            'schema'        => ['nullable', 'json'],
            'pdf'           => ['required_if:type,pdf', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:30720'],
            'is_active'     => ['nullable', 'boolean'],
        ], [
            'pdf.required_if' => 'Saat memilih tipe File, harap unggah file referensi.',
        ]);

        $this->authorize('create', [Form::class, (int)$r->department_id]);

        Log::info('forms.store payload', [
            'type'        => $r->input('type'),
            'hasFile_pdf' => $r->hasFile('pdf'),
            'doc_type'    => $r->input('doc_type'),
        ]);

        // Upload/kompres file bila type=pdf
        $filePath = null;
        if ($r->type === 'pdf' && $r->hasFile('pdf')) {
            $uploaded   = $r->file('pdf');
            $storedTemp = $uploaded->store('forms/tmp', 'public');

            $ext    = strtolower($uploaded->getClientOriginalExtension());
            $outRel = 'forms/files/' . uniqid('form_') . '.' . $ext;
            $outAbs = Storage::disk('public')->path($outRel);

            Storage::disk('public')->makeDirectory('forms/files');

            $ok = false;
            if ($ext === 'pdf') {
                $ok = $this->compressPdf(Storage::disk('public')->path($storedTemp), $outAbs);
            } elseif (in_array($ext, ['docx', 'xlsx'], true)) {
                $ok = $this->recompressOfficeZip(Storage::disk('public')->path($storedTemp), $outAbs);
            }

            if (!$ok) {
                // fallback: simpan apa adanya
                $outRel = $uploaded->store('forms/files', 'public');
            }

            Storage::disk('public')->delete($storedTemp);
            $filePath = $outRel;
        }

        $form = Form::create([
            'department_id' => (int)$r->department_id,
            'created_by'    => $r->user()->id,
            'title'         => $r->title,
            'doc_type'      => strtoupper($r->doc_type),                   // SOP/IK/FORM
            'type'          => $r->type,                                   // builder/pdf
            'schema'        => $r->type === 'builder' ? json_decode($r->schema, true) : null,
            'pdf_path'      => $filePath,
            'is_active'     => $r->boolean('is_active', true),
        ]);

        return redirect()->route('admin.forms.edit', $form)->with('ok', 'Form dibuat');
    }

    public function edit(Form $form)
    {
        $this->authorize('update', $form);
        $departments = Department::orderBy('name')->get();
        return view('admin.forms.edit', compact('form', 'departments'));
    }

    public function update(Request $r, Form $form)
    {
        $this->authorize('update', $form);

        // QUICK UPDATE dari Builder: hanya doc_type
        $onlyDocType = $r->has('doc_type')
            && !$r->hasAny(['department_id', 'title', 'type', 'schema', 'pdf', 'is_active']);

        if ($onlyDocType) {
            $r->validate([
                'doc_type' => ['required', Rule::in(self::DOC_TYPES)],
            ]);
            $form->update(['doc_type' => strtoupper($r->doc_type)]);
            return response()->json(['ok' => true, 'doc_type' => $form->doc_type]);
        }

        // Validasi jalur update penuh (file opsional)
        $r->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'doc_type'      => ['required', Rule::in(self::DOC_TYPES)],
            'type'          => ['required', Rule::in(self::FORM_TYPES)],
            'schema'        => ['nullable', 'json'],
            'pdf'           => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:30720'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        Log::info('forms.update payload', [
            'id'          => $form->id,
            'type'        => $r->input('type'),
            'hasFile_pdf' => $r->hasFile('pdf'),
            'doc_type'    => $r->input('doc_type'),
        ]);

        $filePath = $form->pdf_path;

        // Jika type=pdf dan ada file baru → proses
        if ($r->type === 'pdf' && $r->hasFile('pdf')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            $uploaded   = $r->file('pdf');
            $storedTemp = $uploaded->store('forms/tmp', 'public');

            $ext    = strtolower($uploaded->getClientOriginalExtension());
            $outRel = 'forms/files/' . uniqid('form_') . '.' . $ext;
            $outAbs = Storage::disk('public')->path($outRel);

            Storage::disk('public')->makeDirectory('forms/files');

            $ok = false;
            if ($ext === 'pdf') {
                $ok = $this->compressPdf(Storage::disk('public')->path($storedTemp), $outAbs);
            } elseif (in_array($ext, ['docx', 'xlsx'], true)) {
                $ok = $this->recompressOfficeZip(Storage::disk('public')->path($storedTemp), $outAbs);
            }

            if (!$ok) {
                $outRel = $uploaded->store('forms/files', 'public');
            }

            Storage::disk('public')->delete($storedTemp);
            $filePath = $outRel;
        }

        // Opsi: jika user ganti dari pdf -> builder dan tidak upload file baru, bersihkan file lama
        if ($r->type === 'builder' && $form->type === 'pdf' && $form->pdf_path) {
            Storage::disk('public')->delete($form->pdf_path);
            $filePath = null;
        }

        $form->update([
            'department_id' => (int)$r->department_id,
            'title'         => $r->title,
            'doc_type'      => strtoupper($r->doc_type),
            'type'          => $r->type,
            'schema'        => $r->type === 'builder' ? json_decode($r->schema, true) : null,
            'pdf_path'      => $filePath,
            'is_active'     => $r->boolean('is_active', true),
        ]);

        return back()->with('ok', 'Form diperbarui');
    }

    public function destroy(Form $form)
    {
        $this->authorize('delete', $form);

        if ($form->pdf_path) {
            Storage::disk('public')->delete($form->pdf_path);
        }

        $form->delete();

        return redirect()->route('admin.forms.index')->with('ok', 'Form dihapus');
    }

    public function builder(Form $form)
    {
        $this->authorize('update', $form);
        abort_if($form->type !== 'builder', 404, 'Hanya untuk form tipe builder');

        $schema = $form->schema ?? ['fields' => []];
        return view('admin.forms.builder', compact('form', 'schema'));
    }

    public function saveSchema(Request $r, Form $form)
    {
        $this->authorize('update', $form);
        abort_if($form->type !== 'builder', 404);

        $r->validate([
            'schema' => ['required', 'json'],
        ]);

        $decoded = json_decode($r->schema, true);
        if (!is_array($decoded) || !isset($decoded['fields']) || !is_array($decoded['fields'])) {
            return back()
                ->withErrors(['schema' => 'Schema tidak valid: butuh objek dengan key "fields" berupa array.'])
                ->withInput();
        }

        $form->update(['schema' => $decoded]);

        return redirect()->route('admin.forms.edit', $form)->with('ok', 'Schema tersimpan');
    }

    /**
     * Kompres PDF menggunakan Ghostscript. Return true jika sukses.
     */
    // GANTI method compressPdf() Anda menjadi:
private function compressPdf(string $inPath, string $outPath): bool
{
    // Jika fungsi shell_exec tidak tersedia/disabled → skip kompres
    if (!function_exists('shell_exec')) {
        Log::warning('PDF compress skipped: shell_exec() is disabled.');
        return false;
    }

    // Deteksi OS & cari binary Ghostscript
    $isWin = (\PHP_OS_FAMILY ?? php_uname('s')) === 'Windows';

    $candidates = [];
    if ($isWin) {
        // Coba where, lalu beberapa path umum
        $where = @shell_exec('where gswin64c 2>NUL') ?: @shell_exec('where gswin32c 2>NUL');
        if ($where) $candidates[] = trim($where);
        $candidates[] = 'C:\\Program Files\\gs\\gs10.00.0\\bin\\gswin64c.exe';
        $candidates[] = 'C:\\Program Files\\gs\\gs9.56.1\\bin\\gswin64c.exe';
        $candidates[] = 'C:\\Program Files\\gs\\gs9.55.0\\bin\\gswin64c.exe';
    } else {
        // Linux/macOS
        $which = @shell_exec('command -v gs 2>/dev/null') ?: @shell_exec('which gs 2>/dev/null');
        if ($which) $candidates[] = trim($which);
        $candidates[] = '/usr/bin/gs';
        $candidates[] = '/usr/local/bin/gs';
    }

    $gs = null;
    foreach ($candidates as $bin) {
        if ($bin && is_file($bin) && is_executable($bin)) {
            $gs = $bin;
            break;
        }
    }
    if (!$gs) {
        Log::warning('PDF compress skipped: Ghostscript not found.');
        return false;
    }

    $preset = 'screen'; // atau ebook/printer sesuai kebutuhan
    $cmd = escapeshellcmd($gs)
        .' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4'
        .' -dPDFSETTINGS=/'.$preset
        .' -dNOPAUSE -dQUIET -dBATCH'
        .' -sOutputFile='.escapeshellarg($outPath)
        .' '.escapeshellarg($inPath)
        .' 2>&1';

    // Jalankan; error diarahkan ke stdout (agar tidak memicu warning)
    @shell_exec($cmd);

    return is_file($outPath) && filesize($outPath) > 0;
}

    /**
     * Re-compress DOCX/XLSX (ZIP container) dengan level maksimal.
     * Perlu ext-zip aktif. Return true jika sukses.
     */
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

        return file_exists($outPath) && filesize($outPath) > 0;
    }
    public function file(Form $form)
    {
        $this->authorize('update', $form); // atau 'view' sesuai policy kamu
        abort_unless($form->pdf_path, 404, 'File belum diunggah.');

        $disk = Storage::disk('public');
        abort_unless($disk->exists($form->pdf_path), 404, 'File tidak ditemukan di storage.');

        $abs  = $disk->path($form->pdf_path);
        $ext  = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        $mime = $this->guessMime($abs, $ext);

        // Nama file yang rapi
        $filename = $this->downloadName($form, $ext);

        // Tampilkan inline (browser handle sendiri: pdf/word/excel bisa diunduh bila tidak didukung)
        return response()->file($abs, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ======== FORCE DOWNLOAD ========
    public function download(Form $form)
    {
        $this->authorize('update', $form); // atau 'view'
        abort_unless($form->pdf_path, 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($form->pdf_path), 404);

        $abs  = $disk->path($form->pdf_path);
        $ext  = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        $mime = $this->guessMime($abs, $ext);
        $filename = $this->downloadName($form, $ext);

        return response()->download($abs, $filename, [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    // ======== Helpers ========
    private function guessMime(string $absPath, ?string $ext = null): string
    {
        // Coba fileinfo
        if (function_exists('mime_content_type')) {
            $m = @mime_content_type($absPath);
            if ($m) return $m;
        }

        // Fallback by extension
        $ext = strtolower($ext ?? pathinfo($absPath, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }

    private function downloadName(Form $form, string $ext): string
    {
        $base = trim(($form->title ?: 'form'), " \t\n\r\0\x0B.");
        $base = Str::slug($base, '-');
        if ($base === '') $base = "form-{$form->id}";
        return $base . '.' . $ext;
    }
}
