<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormEntry;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class FormEntryController extends Controller
{
    public function index(Request $r)
    {
        $this->authorize('viewAny', FormEntry::class); // buat policy sederhana atau gate admin
        $q = FormEntry::with(['form:id,title,department_id','user:id,name,email'])
                ->latest();

        if ($r->filled('form_id')) $q->where('form_id', $r->integer('form_id'));
        if ($r->filled('user'))    $q->whereHas('user', fn($qq)=>$qq->where('name','like','%'.$r->user.'%')->orWhere('email','like','%'.$r->user.'%'));
        if ($r->filled('q'))       $q->whereJsonContains('data->nama', $r->q); // contoh filter sederhana

        $entries = $q->paginate(20)->withQueryString();
        return view('admin.entries.index', compact('entries'));
    }

    public function show(FormEntry $entry)
    {
        $this->authorize('view', $entry->form);
        $entry->load(['form','user','files']);
        return view('admin.entries.show', compact('entry'));
    }

    public function destroy(FormEntry $entry)
    {
        $this->authorize('delete', $entry->form);
        // hapus file PDF + lampiran
        if ($entry->pdf_output_path) Storage::disk('public')->delete($entry->pdf_output_path);
        foreach ($entry->files as $f) Storage::disk('public')->delete($f->path);
        $entry->delete();
        return back()->with('ok','Entry dihapus');
    }

    public function export(Request $r): StreamedResponse
    {
        $this->authorize('viewAny', FormEntry::class);

        $q = FormEntry::with(['form:id,title','user:id,name,email'])->latest();
        if ($r->filled('form_id')) $q->where('form_id', $r->integer('form_id'));

        $filename = 'entries-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function() use ($q){
            $out = fopen('php://output', 'w');
            // header CSV
            fputcsv($out, ['id','form_title','user_name','user_email','created_at','data_json']);
            $q->chunk(200, function($rows) use ($out){
                foreach ($rows as $e) {
                    fputcsv($out, [
                        $e->id,
                        $e->form->title ?? '',
                        $e->user->name ?? '',
                        $e->user->email ?? '',
                        $e->created_at->toDateTimeString(),
                        json_encode($e->data, JSON_UNESCAPED_UNICODE)
                    ]);
                }
            });
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadPdf(FormEntry $entry)
    {
        $this->authorize('view', $entry->form);
        abort_unless($entry->pdf_output_path && Storage::disk('public')->exists($entry->pdf_output_path), 404);
        return response()->download(storage_path('app/public/'.$entry->pdf_output_path));
    }
}
