<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Form;        // sesuaikan
use App\Models\FormEntry;   // sesuaikan
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    public function index(Request $r)
    {
        $forms = Form::query()
            ->with(['department','site'])
            ->when($r->filled('q'), fn($q) =>
                $q->where(function($qq) use ($r) {
                    $qq->where('title','like','%'.$r->q.'%')
                       ->orWhere('description','like','%'.$r->q.'%');
                })
            )
            ->latest()->paginate(12);

        return view('front.forms.index', compact('forms'));
    }

    public function show(Form $form)
    {
        // tampilkan halaman isi form
        return view('front.forms.show', compact('form'));
    }

    public function submit(Request $r, Form $form)
    {
        // Validasi sederhana — sesuaikan field kamu
        $data = $r->validate([
            // 'answers' => 'required|array',
            // contoh:
            // 'answers.question_1' => 'required|string',
        ]);

        // Simpan entry
        $entry = FormEntry::create([
            'form_id' => $form->id,
            'user_id' => optional(Auth::user())->id,
            // 'answers' => $data['answers'] ?? [],
            // field lain...
        ]);

        // Redirect ke halaman yang ADA:
        return redirect()
            ->route('front.forms.entries.show', $entry->id)
            ->with('success', 'Terima kasih! Jawaban kamu sudah tersimpan.');
    }

    public function preview(Form $form)
    {
        return view('front.forms.preview', compact('form'));
    }

    public function entriesIndex()
    {
        $entries = FormEntry::query()
            ->with('form:id,title')
            ->when(Auth::check(), fn($q) => $q->where('user_id', Auth::id()))
            ->latest()->paginate(15);

        return view('front.forms.entries.index', compact('entries'));
    }

    public function entriesShow(FormEntry $entry)
    {
        // (opsional) batasi ke pemiliknya
        if (Auth::check() && $entry->user_id && $entry->user_id !== Auth::id()) {
            abort(403);
        }

        $entry->load('form');
        return view('front.forms.entries.show', compact('entry'));
    }
}
