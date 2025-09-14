<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\FormEntryFile; // <- ganti model file yg benar
use Illuminate\Support\Str;

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
        if (Auth::check() && $entry->user_id && $entry->user_id !== Auth::id()) {
            abort(403);
        }
        $entry->load('form');
        return view('front.forms.entries.show', compact('entry'));
    }

    public function store(Request $r, Form $form)
    {
        // Ambil payload jawaban. Dukung dua skema name:
        // - data[...]
        // - answers[...]
        $payload = (array) $r->input('data', $r->input('answers', []));

        // (opsional) bersihkan '' jadi null
        array_walk($payload, function (&$v) {
            if ($v === '') $v = null;
        });

        $entry = FormEntry::create([
            'form_id' => $form->id,
            'user_id' => Auth::id(),
            'data'    => $payload, // <-- SIMPAN KE 'data', bukan 'answers'
        ]);

        // ===== Simpan lampiran (opsional) =====
        // Form input: <input type="file" name="files[]" multiple>
        if ($r->hasFile('files')) {
            foreach ((array) $r->file('files') as $i => $upload) {
                if (!$upload || !$upload->isValid()) continue;

                $dir  = 'form-entry-files/'.date('Y/m/d');
                $path = $upload->store($dir, 'public');

                $entry->files()->create([
                    'path'          => $path,
                    'original_name' => $upload->getClientOriginalName(),
                    'mime'          => $upload->getClientMimeType(),
                    'size'          => $upload->getSize(),
                    'field_name'    => 'files', // kalau kamu simpan by field
                ]);
            }
        }

        return redirect()
            ->route('front.forms.entries.show', $entry) // route kamu ada: front.forms.entries.show
            ->with('success', 'Terima kasih! Entri kamu sudah tersimpan.');
    }

    public function downloadAttachment(int $file)
    {
        $fileRow = FormEntryFile::query()->with('entry')->findOrFail($file);

        if (Auth::check() && $fileRow->entry && $fileRow->entry->user_id && $fileRow->entry->user_id !== Auth::id()) {
            abort(403);
        }

        $disk = $fileRow->disk ?? 'public';
        abort_unless(Storage::disk($disk)->exists($fileRow->path), 404);

        $name = $fileRow->original_name ?: basename($fileRow->path);
        return Storage::disk($disk)->download($fileRow->path, $name);
    }
}
