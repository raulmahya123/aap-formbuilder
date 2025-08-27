<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicEntryRequest;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\FormEntryFile;
use Illuminate\Support\Facades\Storage;

class FormEntryController extends Controller
{
    /**
     * Simpan isian form (builder/pdf) + upload lampiran.
     * TIDAK ada proses render PDF bukti.
     */
    public function store(DynamicEntryRequest $r, Form $form)
    {
        $this->authorize('submit', $form);

        // Data tervalidasi dari FormRequest (prefix "data.")
        $validated = $r->validated();
        $data      = $validated['data'] ?? [];

        // Ambil fields dari schema (untuk tahu mana yang file)
        $schema = $form->schema;
        if (is_string($schema)) {
            $decoded = json_decode($schema, true);
            $schema  = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        $fields = is_array($schema)
            ? ($schema['fields'] ?? (array_is_list($schema) ? $schema : []))
            : [];
        $byName = collect($fields)->keyBy('name');

        // Pisahkan payload vs files
        $payload  = [];
        $filesBag = []; // [fieldName => UploadedFile[]]

        foreach ($data as $name => $val) {
            $type = $byName[$name]['type'] ?? null;

            if ($type === 'file') {
                $uploaded = $r->file("data.$name");
                if (is_array($uploaded)) {
                    foreach ($uploaded as $u) {
                        if ($u) $filesBag[$name][] = $u;
                    }
                } elseif ($uploaded) {
                    $filesBag[$name] = [$uploaded];
                }
                continue; // file tidak disimpan ke kolom data JSON
            }

            $payload[$name] = $val;
        }

        // Buat entry
        $entry = FormEntry::create([
            'form_id' => $form->id,
            'user_id' => optional($r->user())->id,
            'data'    => $payload,
            // 'pdf_output_path' tetap NULL karena kita tidak buat PDF
        ]);

        // Simpan lampiran
        foreach ($filesBag as $fieldName => $list) {
            foreach ($list as $uploaded) {
                $dir  = "form_entries/{$entry->id}";
                $path = $uploaded->store($dir, 'public');

                FormEntryFile::create([
                    'form_entry_id' => $entry->id,
                    'field_name'    => $fieldName,
                    'original_name' => $uploaded->getClientOriginalName(),
                    'mime'          => $uploaded->getClientMimeType(),
                    'size'          => $uploaded->getSize(),
                    'path'          => $path,
                ]);
            }
        }

        return redirect()
            ->route('front.forms.thanks', $form)
            ->with('success', 'Jawaban tersimpan.');
    }

    /**
     * (Opsional) Unduh PDF hasil render jawaban — kalau tidak dipakai, hapus method & routenya.
     * Dibiarkan di sini hanya untuk kompatibilitas; akan 404 jika tidak ada file.
     */
    public function downloadPdf(FormEntry $entry)
    {
        $this->authorize('view', $entry->form);

        abort_unless(
            $entry->pdf_output_path && Storage::disk('public')->exists($entry->pdf_output_path),
            404
        );

        return response()->download(storage_path('app/public/' . $entry->pdf_output_path));
    }

    /**
     * Unduh lampiran yang diunggah pada sebuah entry.
     */
    public function downloadAttachment(FormEntryFile $file)
    {
        $this->authorize('view', $file->entry->form);

        $path = $file->path;
        abort_unless(Storage::disk('public')->exists($path), 404);

        return response()->download(storage_path('app/public/' . $path), $file->original_name);
    }
}
