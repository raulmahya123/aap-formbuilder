<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\Department; // <= tambah
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FormController extends Controller
{
    private const DOC_TYPES = ['SOP','IK','FORM'];

    /** Normalisasi doc_type dari request, terima doc_type/doctype, all/* => null */
    private function normalizeDocType(Request $r): ?string
    {
        $raw = $r->input('doc_type', $r->input('doctype'));
        if ($raw === null) return null;

        $val = strtoupper(trim((string)$raw));
        if ($val === '' || $val === 'ALL' || $val === '*') return null;

        return in_array($val, self::DOC_TYPES, true) ? $val : null;
    }

    public function index(Request $r)
    {
        // ===== base query list form
        $q = Form::query()
            ->with(['department','site'])
            ->when($r->filled('q'), function ($qb) use ($r) {
                $term = '%'.$r->q.'%';
                $qb->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', $term)
                       ->orWhere('description','like', $term);
                });
            })
            ->where('is_active', true);

        // filter department (opsional)
        if ($r->filled('department_id')) {
            $q->where('department_id', (int) $r->department_id);
        }

        // filter doc_type (opsional)
        $currentDocType = $this->normalizeDocType($r);
        if ($currentDocType) {
            $q->where('doc_type', $currentDocType);
        }

        // ===== counts per doc_type (mengikuti filter q + department_id + is_active)
        $baseForCount = Form::query()
            ->where('is_active', true)
            ->when($r->filled('q'), function ($qb) use ($r) {
                $term = '%'.$r->q.'%';
                $qb->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', $term)
                       ->orWhere('description','like', $term);
                });
            })
            ->when($r->filled('department_id'), fn($qb) => $qb->where('department_id', (int) $r->department_id));

        $counts = [
            'SOP'  => (clone $baseForCount)->where('doc_type','SOP')->count(),
            'IK'   => (clone $baseForCount)->where('doc_type','IK')->count(),
            'FORM' => (clone $baseForCount)->where('doc_type','FORM')->count(),
            'ALL'  => (clone $baseForCount)->count(),
        ];

        // ===== pagination
        $perPage = in_array((int)$r->per_page, [10,12,20,50,100], true) ? (int)$r->per_page : 12;
        $forms = $q->latest()->paginate($perPage)->appends($r->query());

        // ===== kirim semua departemen untuk grid tiles (color opsional)
        $departments = Department::orderBy('name')->get(['id','name','color']);

        return view('front.forms.index', [
            'forms'          => $forms,
            'counts'         => $counts,
            'currentDocType' => $currentDocType,
            'departments'    => $departments,
        ]);
    }

    public function show(Form $form)
    {
        return view('front.forms.show', compact('form'));
    }

    public function submit(Request $r, Form $form)
    {
        // === Kumpulkan jawaban dinamis dari schema (untuk tipe builder)
        $answers = [];
        if ($form->type === 'builder') {
            $fields = (array) ($form->schema['fields'] ?? []);
            foreach ($fields as $f) {
                $name = $f['name'] ?? Str::slug($f['label'] ?? 'field','_');
                $type = $f['type'] ?? 'text';

                if ($type === 'file') {
                    if ($r->hasFile($name)) {
                        $uploaded = $r->file($name);
                        $path = $uploaded->store('form-entries/files', 'public');
                        $answers[$name] = [
                            'stored_path' => $path,
                            'original'    => $uploaded->getClientOriginalName(),
                            'size'        => $uploaded->getSize(),
                            'mime'        => $uploaded->getClientMimeType(),
                        ];
                    } else {
                        $answers[$name] = null;
                    }
                } elseif ($type === 'checkbox') {
                    $answers[$name] = array_values((array) $r->input($name, []));
                } else {
                    $answers[$name] = $r->input($name);
                }
            }
        } else {
            // tipe "pdf": contoh field sederhana
            $answers = ['catatan' => $r->input('catatan')];
        }

        $entry = FormEntry::create([
            'form_id'        => $form->id,
            'user_id'        => optional(Auth::user())->id,
            'doc_type'       => strtoupper((string)$form->doc_type), // ikut form
            'data'           => $answers,
            'pdf_output_path'=> null,
        ]);

        return redirect()
            ->route('front.forms.entries.show', $entry->id)
            ->with('success', 'Terima kasih! Jawaban kamu sudah tersimpan.');
    }

    public function preview(Form $form)
    {
        return view('front.forms.preview', compact('form'));
    }

    public function entriesIndex(Request $r)
    {
        $q = FormEntry::query()
            ->with('form:id,title,doc_type')
            ->when(Auth::check(), fn($qb) => $qb->where('user_id', Auth::id()));

        // filter doc_type (opsional)
        $currentDocType = $this->normalizeDocType($r);
        if ($currentDocType) {
            $q->where('doc_type', $currentDocType);
        }

        $entries = $q->latest()->paginate(15)->appends($r->query());

        // counts per doc_type (mengikuti filter user_id)
        $baseForCount = FormEntry::query()
            ->when(Auth::check(), fn($qb) => $qb->where('user_id', Auth::id()));

        $entryCounts = [
            'SOP'  => (clone $baseForCount)->where('doc_type','SOP')->count(),
            'IK'   => (clone $baseForCount)->where('doc_type','IK')->count(),
            'FORM' => (clone $baseForCount)->where('doc_type','FORM')->count(),
            'ALL'  => (clone $baseForCount)->count(),
        ];

        return view('front.forms.entries.index', compact('entries','entryCounts','currentDocType'));
    }

    public function entriesShow(FormEntry $entry)
    {
        if (Auth::check() && $entry->user_id && $entry->user_id !== Auth::id()) {
            abort(403);
        }

        $entry->load('form');
        return view('front.forms.entries.show', compact('entry'));
    }
}
