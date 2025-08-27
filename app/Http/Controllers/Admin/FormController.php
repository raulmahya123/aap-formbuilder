<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Form, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormController extends Controller
{
    public function index(Request $r)
    {
        $q = Form::with(['department', 'creator'])->latest();
        if ($r->filled('department_id')) $q->where('department_id', $r->department_id);
        $forms = $q->paginate(20);
        $departments = Department::orderBy('name')->get();
        return view('admin.forms.index', compact('forms', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.forms.create', compact('departments'));
    }

    public function store(Request $r)
    {
        $r->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'type'          => ['required', 'in:builder,pdf'],
            'schema'        => ['nullable', 'json'],
            'pdf'           => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $this->authorize('create', [\App\Models\Form::class, (int)$r->department_id]);

        $pdfPath = null;
        if ($r->type === 'pdf' && $r->hasFile('pdf')) {
            $pdfPath = $r->file('pdf')->store('forms/pdf', 'public');
        }

        $form = Form::create([
            'department_id' => (int)$r->department_id,
            'created_by'    => $r->user()->id,
            'title'         => $r->title,
            'type'          => $r->type,
            'schema'        => $r->type === 'builder' ? json_decode($r->schema, true) : null,
            'pdf_path'      => $pdfPath,
            'is_active'     => (bool)$r->boolean('is_active', true),
        ]);

        return redirect()->route('admin.forms.edit', $form)->with('ok', 'Form dibuat');
    }

    public function edit(Form $form)
    {
        $this->authorize('update', $form);
        $departments = Department::orderBy('name')->get();
        return view('admin.forms.edit', compact('form', 'departments'));
    }

    public function update(Request $r, Form $form)
    {
        $this->authorize('update', $form);

        $r->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'title'         => ['required', 'string', 'max:190'],
            'type'          => ['required', 'in:builder,pdf'],
            'schema'        => ['nullable', 'json'],
            'pdf'           => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $pdfPath = $form->pdf_path;
        if ($r->type === 'pdf' && $r->hasFile('pdf')) {
            if ($pdfPath) Storage::disk('public')->delete($pdfPath);
            $pdfPath = $r->file('pdf')->store('forms/pdf', 'public');
        }


        $form->update([
            'department_id' => (int)$r->department_id,
            'title'         => $r->title,
            'type'          => $r->type,
            'schema'        => $r->type === 'builder' ? json_decode($r->schema, true) : null,
            'pdf_path'      => $pdfPath,
            'is_active'     => (bool)$r->boolean('is_active', true),
        ]);

        return back()->with('ok', 'Form diperbarui');
    }

    public function destroy(Form $form)
    {
        $this->authorize('delete', $form);
        if ($form->pdf_path) Storage::disk('public')->delete($form->pdf_path);
        $form->delete();
        return redirect()->route('admin.forms.index')->with('ok', 'Form dihapus');
    }

    public function builder(\App\Models\Form $form)
    {
        $this->authorize('update', $form);
        abort_if($form->type !== 'builder', 404, 'Hanya untuk form tipe builder');

        // normalisasi schema minimal
        $schema = $form->schema ?? ['fields' => []];

        return view('admin.forms.builder', compact('form', 'schema'));
    }

    public function saveSchema(\Illuminate\Http\Request $r, \App\Models\Form $form)
    {
        $this->authorize('update', $form);
        abort_if($form->type !== 'builder', 404);

        $r->validate([
            'schema' => ['required', 'json'],
        ]);

        // Validasi ringan struktur (punya fields array)
        $decoded = json_decode($r->schema, true);
        if (!is_array($decoded) || !isset($decoded['fields']) || !is_array($decoded['fields'])) {
            return back()->withErrors(['schema' => 'Schema tidak valid: butuh objek dengan key "fields" berupa array.'])->withInput();
        }

        // simpan
        $form->update(['schema' => $decoded]);

        return redirect()->route('admin.forms.edit', $form)->with('ok', 'Schema tersimpan');
    }
}
