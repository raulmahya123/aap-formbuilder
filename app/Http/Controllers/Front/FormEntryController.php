<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Form;
use App\Models\FormEntry;
// Opsional jika ada tabel file lampiran:
use App\Models\EntryFile;

class FormEntryController extends Controller
{
    public function index(Request $r)
    {
        $entries = FormEntry::query()
            ->with(['form:id,title,slug'])
            ->when(Auth::check(), fn($q) => $q->where('user_id', Auth::id()))
            ->latest()
            ->paginate(15);

        return view('front.forms.entries.index', compact('entries'));
    }

    public function show(FormEntry $entry)
    {
        // Batasi akses hanya pemilik (opsional)
        if (Auth::check() && $entry->user_id && $entry->user_id !== Auth::id()) {
            abort(403);
        }
        $entry->load('form');
        return view('front.forms.entries.show', compact('entry'));
    }

    public function store(Request $r, Form $form)
    {
        // Validasi minimal (silakan sesuaikan field)
        $data = $r->validate([
            'answers' => 'nullable|array',
            'answers.*' => 'nullable|string',
            // contoh upload file (opsional):
            // 'files.*' => 'file|max:10240',
        ]);

        $entry = FormEntry::create([
            'form_id' => $form->id,
            'user_id' => optional(Auth::user())->id,
            'answers' => $data['answers'] ?? [],
            // simpan meta lain jika perlu...
        ]);

        // (Opsional) simpan file
        // if ($r->hasFile('files')) {
        //     foreach ($r->file('files') as $upload) {
        //         $path = $upload->store("form_entries/{$entry->id}", 'public');
        //         EntryFile::create([
        //             'entry_id' => $entry->id,
        //             'disk' => 'public',
        //             'path' => $path,
        //             'original_name' => $upload->getClientOriginalName(),
        //             'mime' => $upload->getClientMimeType(),
        //         ]);
        //     }
        // }

        // Redirect yang PASTI ADA: entries.show
        return redirect()
            ->route('front.forms.entries.show', $entry->id)
            ->with('success', 'Terima kasih! Entri kamu sudah tersimpan.');
    }

    public function downloadAttachment(int $file)
    {
        // Contoh implementasi sederhana (pakai tabel entry_files)
        $fileRow = EntryFile::query()->findOrFail($file);

        // (Opsional) cek kepemilikan
        if (Auth::check() && $fileRow->entry && $fileRow->entry->user_id && $fileRow->entry->user_id !== Auth::id()) {
            abort(403);
        }

        $disk = $fileRow->disk ?? 'public';
        if (!Storage::disk($disk)->exists($fileRow->path)) {
            abort(404);
        }
        return Storage::disk($disk)->download($fileRow->path, $fileRow->original_name ?? basename($fileRow->path));
    }
}
