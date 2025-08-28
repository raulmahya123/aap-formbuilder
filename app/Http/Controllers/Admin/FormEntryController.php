<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormEntry;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;      // <- tambah ini
use ZipArchive;         // <- dan ini
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class FormEntryController extends Controller
{
    /**
     * Daftar entries
     */
    public function index(Request $r)
    {
        // Pastikan FormEntryPolicy terpasang: viewAny
        $this->authorize('viewAny', FormEntry::class);

        $q = FormEntry::with(['form:id,title,department_id', 'user:id,name,email'])
            ->latest();

        // Filter by form_id
        if ($r->filled('form_id')) {
            $q->where('form_id', (int) $r->input('form_id'));
        }

        // Filter by user (nama/email). Hindari $r->user (bentrok dengan Request::user())
        if ($r->filled('user')) {
            $u = trim($r->input('user'));
            $q->whereHas('user', function ($qq) use ($u) {
                $qq->where('name', 'like', "%{$u}%")
                    ->orWhere('email', 'like', "%{$u}%");
            });
        }

        // Filter pencarian sederhana di field JSON "data->nama"
        if ($r->filled('q')) {
            $val = trim($r->input('q'));

            // Coba pakai whereJsonContains (cocok jika data->nama array/teks tertentu)
            // Jika tidak cocok di DB Anda, aktifkan alternatif LIKE di bawah.
            $q->where(function ($qq) use ($val) {
                $qq->whereJsonContains('data->nama', $val)
                    // Alternatif LIKE (uncomment jika needed):
                    // ->orWhereRaw("JSON_EXTRACT(data, '$.nama') LIKE ?", ["%{$val}%"])
                ;
            });
        }

        $entries = $q->paginate(20)->withQueryString();

        return view('admin.entries.index', compact('entries'));
    }

    /**
     * Detail satu entry
     */
    public function show(FormEntry $entry)
    {
        // Pakai policy FormEntryPolicy@view
        $this->authorize('view', $entry);

        $entry->load(['form', 'user', 'files']);

        return view('admin.entries.show', compact('entry'));
    }

    /**
     * Hapus satu entry beserta file terkait
     */
    public function destroy(FormEntry $entry)
    {
        // Pakai policy FormEntryPolicy@delete
        $this->authorize('delete', $entry);

        // Hapus file PDF output kalau ada
        if ($entry->pdf_output_path) {
            Storage::disk('public')->delete($entry->pdf_output_path);
        }

        // Hapus lampiran yang tersimpan
        foreach ($entry->files as $f) {
            if ($f->path) {
                Storage::disk('public')->delete($f->path);
            }
        }

        $entry->delete();

        return back()->with('ok', 'Entry dihapus');
    }

    /**
     * Ekspor daftar entries ke CSV (streaming)
     */
    public function export(Request $r): StreamedResponse
    {
        $this->authorize('viewAny', FormEntry::class);

        $q = FormEntry::with(['form:id,title', 'user:id,name,email'])
            ->latest();

        if ($r->filled('form_id')) {
            $q->where('form_id', (int) $r->input('form_id'));
        }

        $filename = 'entries-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');

            // header CSV
            fputcsv($out, ['id', 'form_title', 'user_name', 'user_email', 'created_at', 'data_json']);

            $q->chunk(200, function ($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, [
                        $e->id,
                        $e->form->title ?? '',
                        $e->user->name ?? '',
                        $e->user->email ?? '',
                        optional($e->created_at)->toDateTimeString(),
                        json_encode($e->data, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Download PDF hasil render entry (jika ada)
     */
    public function downloadPdf(FormEntry $entry)
    {
        // Pakai policy FormEntryPolicy@view
        $this->authorize('view', $entry);

        $path = $entry->pdf_output_path;

        abort_unless($path && Storage::disk('public')->exists($path), 404);

        return response()->download(storage_path('app/public/' . $path));
    }


    public function downloadAll(\App\Models\FormEntry $entry)
    {
        // hak akses: sama seperti yang kamu pakai untuk lihat entry
        $this->authorize('view', $entry->form);

        // siapkan folder tmp
        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $stamp  = Carbon::now()->format('Ymd-His');
        $fname  = "entry-{$entry->id}-{$stamp}.zip";
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $fname;

        // buat ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat arsip ZIP.');
        }

        // 2.a. entry.json (meta + data)
        $entryJson = [
            'id'         => $entry->id,
            'form'       => [
                'id'    => $entry->form->id,
                'title' => $entry->form->title,
            ],
            'user'       => $entry->user ? [
                'id'    => $entry->user->id,
                'name'  => $entry->user->name,
                'email' => $entry->user->email,
            ] : null,
            'status'     => $entry->status,
            'created_at' => $entry->created_at?->toIso8601String(),
            'updated_at' => $entry->updated_at?->toIso8601String(),
            'data'       => $entry->data, // key => value
        ];
        $zip->addFromString('entry/entry.json', json_encode($entryJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 2.b. Optional: entry.csv (key,value) — biar gampang dibuka di Excel
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['field', 'value']);
        foreach ((array)$entry->data as $k => $v) {
            $val = is_array($v) ? implode(', ', $v) : $v;
            fputcsv($csv, [$k, $val]);
        }
        rewind($csv);
        $csvContent = stream_get_contents($csv);
        fclose($csv);
        $zip->addFromString('entry/entry.csv', $csvContent);

        // 2.c. Riwayat approval (jika ada)
        $approvals = $entry->approvals()->with('actor')->orderByDesc('id')->get()->map(function ($h) {
            return [
                'action'     => $h->action,
                'actor'      => $h->actor ? ['id' => $h->actor->id, 'name' => $h->actor->name, 'email' => $h->actor->email] : null,
                'notes'      => $h->notes,
                'created_at' => $h->created_at?->toIso8601String(),
            ];
        })->all();

        $zip->addFromString('history/approvals.json', json_encode($approvals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 2.d. PDF (jika ada)
        if ($entry->pdf_output_path && Storage::disk('public')->exists($entry->pdf_output_path)) {
            $zip->addFile(
                storage_path('app/public/' . $entry->pdf_output_path),
                'pdf/' . basename($entry->pdf_output_path)
            );
        }

        // 2.e. Semua lampiran
        // Struktur: attachments/{field_name}/{original_name}
        foreach ($entry->files as $f) {
            if (!$f->path) continue;
            if (!Storage::disk('public')->exists($f->path)) continue;

            $fieldDir = $f->field_name ?: 'unknown';
            // fallback nama file
            $niceName = $f->original_name ?: basename($f->path);

            // hindari karakter aneh di nama
            $safeName = Str::of($niceName)->replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-');
            $zip->addFile(
                storage_path('app/public/' . $f->path),
                "attachments/{$fieldDir}/{$safeName}"
            );
        }

        $zip->close();

        // kirim dan hapus setelah dikirim
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
    public function downloadDataPdf(FormEntry $entry)
{
    // Otorisasi — sesuaikan dengan kebijakanmu
    $this->authorize('view', $entry->form);

    // Buat nama file yang lebih deskriptif
    $formTitle = Str::slug($entry->form->title, '-'); // rapihin jadi slug (tanpa spasi/simbol aneh)
    $userName  = Str::slug($entry->user->name, '-');

    $fileName = "{$formTitle}-{$userName}-#{$entry->id}.pdf";

    // Render view PDF
    $pdf = Pdf::loadView('admin.entries.pdf_data', [
        'entry' => $entry,
    ])->setPaper('a4', 'portrait');

    return $pdf->download($fileName);
}

}
