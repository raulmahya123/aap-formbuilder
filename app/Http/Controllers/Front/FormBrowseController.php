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
        $user = $r->user();

        $q = Form::query()
            ->with('department')
            ->where('is_active', true);

        // Filter berdasarkan department/site user (opsional)
        // contoh: hanya department yg user punya akses
        if (!$user->isAdmin()) {
            $q->whereIn('department_id', $user->departments()->pluck('departments.id'));
        }

        if ($r->filled('q')) {
            $q->where('title', 'like', "%{$r->q}%");
        }

        $forms = $q->paginate(20);

        return view('front.forms.index', compact('forms'));
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
