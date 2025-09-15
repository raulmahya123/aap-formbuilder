<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class FormBrowseController extends Controller
{
    /**
     * List semua form yang user boleh lihat.
     */
    public function index(Request $r)
{
    $DOCS = ['SOP','IK','FORM'];

    // Prioritas: route param → query string
    $dt = strtoupper((string)($r->route('doc_type') ?? $r->input('doc_type') ?? $r->input('doctype')));
    $currentDocType = in_array($dt, $DOCS, true) ? $dt : null;

    $q = Form::query()
        ->with(['department','site'])
        ->when($r->filled('q'), function ($qb) use ($r) {
            $term = '%'.$r->q.'%';
            $qb->where(function ($qq) use ($term) {
                $qq->where('title','like',$term)->orWhere('description','like',$term);
            });
        })
        ->where('is_active', true)
        ->when($currentDocType, fn($qb) => $qb->where('doc_type', $currentDocType))
        ->latest();

    // counts mengikuti pencarian (tanpa filter doc_type, supaya semua tab punya angka)
    $base = Form::query()
        ->when($r->filled('q'), function ($qb) use ($r) {
            $term = '%'.$r->q.'%';
            $qb->where(function ($qq) use ($term) {
                $qq->where('title','like',$term)->orWhere('description','like',$term);
            });
        })
        ->where('is_active', true);

    $counts = [
        'SOP'  => (clone $base)->where('doc_type','SOP')->count(),
        'IK'   => (clone $base)->where('doc_type','IK')->count(),
        'FORM' => (clone $base)->where('doc_type','FORM')->count(),
        'ALL'  => (clone $base)->count(),
    ];

    $forms = $q->paginate(12)->appends($r->query());

    return view('front.forms.index', compact('forms','counts','currentDocType'));
}


    /**
     * Tampilkan 1 form (isi atau preview).
     */
    public function show(Request $r, Form $form)
    {
        $user = $r->user();

        // Cek akses
        if (!$form->is_active) {
            abort(403, 'Form tidak aktif');
        }

        if (!$user->isAdmin()) {
            $allowed = $user->departments()->where('departments.id', $form->department_id)->exists();
            if (!$allowed) {
                abort(403, 'Anda tidak punya akses ke form ini');
            }
        }

        return view('front.forms.show', compact('form'));
    }

    /**
     * Preview (opsional).
     */
    public function preview(Request $r, Form $form)
    {
        $user = $r->user();

        if (!$form->is_active) {
            abort(403);
        }

        if (!$user->isAdmin()) {
            $allowed = $user->departments()->where('departments.id', $form->department_id)->exists();
            if (!$allowed) {
                abort(403);
            }
        }

        return view('front.forms.preview', compact('form'));
    }
}
