<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\DynamicEntryRequest;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\FormEntryFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class FormEntryController extends Controller
{
    /**
     * Simpan isian form (builder/pdf), upload lampiran, dan render bukti PDF.
     */
    public function store(DynamicEntryRequest $r, Form $form)
    {
        // Data tervalidasi sesuai schema (DynamicEntryRequest)
        $validated = $r->validated();

        // Pisahkan field file vs non-file
        $filesData = [];
        $payload   = [];

        $schemaFields = collect($form->schema['fields'] ?? [])->keyBy('name');

        foreach ($validated as $name => $val) {
            $type = $schemaFields[$name]['type'] ?? null;

            if ($type === 'file' && $r->hasFile($name)) {
                $filesData[$name] = $r->file($name);
            } else {
                $payload[$name] = $val;
            }
        }

        // Buat entry
        $entry = FormEntry::create([
            'form_id' => $form->id,
            'user_id' => $r->user()->id,
            'data'    => $payload,
        ]);

        // Simpan file ke storage/public/form_entries/{entry_id}/
        foreach ($filesData as $fieldName => $uploaded) {
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

        // Render bukti ke PDF (opsional)
        $filename = "entries/entry-{$entry->id}.pdf";
        Pdf::view('pdf.entry', [
                'form'  => $form,
                'entry' => $entry,
                'data'  => $payload,
            ])
            ->format('a4')
            ->save(storage_path('app/public/' . $filename));

        $entry->update(['pdf_output_path' => $filename]);

        return redirect()
            ->route('front.forms.thanks', $form)
            ->with('ok', 'Jawaban tersimpan');
    }

    /**
     * Unduh PDF hasil render jawaban.
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
