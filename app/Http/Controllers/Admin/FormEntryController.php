<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormEntry;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

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
}
