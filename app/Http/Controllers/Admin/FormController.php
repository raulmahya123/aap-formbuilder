<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Form, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, Log};
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    public function index(Request $r)
    {
        $q = Form::with(['department', 'creator'])->latest();
        if ($r->filled('department_id')) $q->where('department_id', $r->department_id);

        $forms = $q->paginate(20);
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
        $r->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'type'          => ['required', Rule::in(['builder','pdf'])], // tetap 'pdf' agar kompatibel
            'schema'        => ['nullable', 'json'],
            // saat create dan type=pdf, wajib ada file
            'pdf'           => ['required_if:type,pdf', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:30720'],
        ], [
            'pdf.required_if' => 'Harap unggah file referensi saat memilih tipe File.',
        ]);

        $this->authorize('create', [Form::class, (int)$r->department_id]);

        Log::info('forms.store payload', [
            'type'        => $r->input('type'),
            'hasFile_pdf' => $r->hasFile('pdf'),
        ]);

        $filePath = null;

        if ($r->type === 'pdf' && $r->hasFile('pdf')) {
            $uploaded   = $r->file('pdf');
            $storedTemp = $uploaded->store('forms/tmp', 'public');

            // Tentukan tujuan akhir
            $ext    = strtolower($uploaded->getClientOriginalExtension());
            $outRel = 'forms/files/' . uniqid('form_') . '.' . $ext;
            $outAbs = Storage::disk('public')->path($outRel);

            // Pastikan folder ada
            Storage::disk('public')->makeDirectory('forms/files');

            // Kompres sesuai tipe
            $ok = false;
            if ($ext === 'pdf') {
                $ok = $this->compressPdf(Storage::disk('public')->path($storedTemp), $outAbs);
            } elseif (in_array($ext, ['docx','xlsx'])) {
                $ok = $this->recompressOfficeZip(Storage::disk('public')->path($storedTemp), $outAbs);
            } else {
                // doc/xls lama: tidak bisa di-zip ulang; biarkan apa adanya (ok tetap false agar fallback)
            }

            if (!$ok) {
                // fallback: simpan file asli ke folder final
                $outRel = $uploaded->store('forms/files', 'public');
            }

            // bersihkan temp
            Storage::disk('public')->delete($storedTemp);

            $filePath = $outRel;
        }

        $form = Form::create([
            'department_id' => (int)$r->department_id,
            'created_by'    => $r->user()->id,
            'title'         => $r->title,
            'type'          => $r->type,
            'schema'        => $r->type === 'builder' ? json_decode($r->schema, true) : null,
            // gunakan kolom lama 'pdf_path' untuk semua jenis file agar tanpa migrasi
            'pdf_path'      => $filePath,
            'is_active'     => (bool)$r->boolean('is_active', true),
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

        $r->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'type'          => ['required', Rule::in(['builder','pdf'])],
            'schema'        => ['nullable', 'json'],
            // di update, file opsional
            'pdf'           => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx', 'max:30720'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        Log::info('forms.update payload', [
            'id'          => $form->id,
            'type'        => $r->input('type'),
            'hasFile_pdf' => $r->hasFile('pdf'),
        ]);

        $filePath = $form->pdf_path;

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
            } elseif (in_array($ext, ['docx','xlsx'])) {
                $ok = $this->recompressOfficeZip(Storage::disk('public')->path($storedTemp), $outAbs);
            }

            if (!$ok) {
                $outRel = $uploaded->store('forms/files', 'public');
            }

            Storage::disk('public')->delete($storedTemp);
            $filePath = $outRel;
        }

        $form->update([
            'department_id' => (int)$r->department_id,
            'title'         => $r->title,
            'type'          => $r->type,
            'schema'        => $r->type === 'builder' ? json_decode($r->schema, true) : null,
            'pdf_path'      => $filePath, // kolom lama dipakai generik
            'is_active'     => (bool)$r->boolean('is_active', true),
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
    private function compressPdf(string $inPath, string $outPath): bool
    {
        // Preset kualitas: /screen (kecil), /ebook (sedang), /printer (bagus)
        $preset = 'screen';

        // Cek ketersediaan gs
        $gs = trim((string)@shell_exec('which gs'));
        if ($gs === '') return false;

        // Perintah Ghostscript
        $cmd = escapeshellcmd($gs)
            .' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4'
            .' -dPDFSETTINGS=/' . $preset
            .' -dNOPAUSE -dQUIET -dBATCH'
            .' -sOutputFile=' . escapeshellarg($outPath)
            .' ' . escapeshellarg($inPath);

        @shell_exec($cmd);

        return file_exists($outPath) && filesize($outPath) > 0;
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
            $stat  = $zipIn->statIndex($i);
            $name  = $stat['name'];
            $stream = $zipIn->getStream($name);
            if (!$stream) continue;

            $data = stream_get_contents($stream);
            fclose($stream);

            $zipOut->addFromString($name, $data);

            // Set level kompresi maksimum (kalau didukung)
            if (defined('\ZipArchive::CM_DEFLATE')) {
                $zipOut->setCompressionName($name, \ZipArchive::CM_DEFLATE, 9);
            }
        }

        $zipOut->close();
        $zipIn->close();

        return file_exists($outPath) && filesize($outPath) > 0;
    }
}
