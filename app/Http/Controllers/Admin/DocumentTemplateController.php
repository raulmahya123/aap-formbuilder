<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function index() {
        $templates = DocumentTemplate::latest()->paginate(20);
        return view('admin.document_templates.index', compact('templates'));
    }

    public function create() {
        return view('admin.document_templates.create');
    }

    public function store(Request $r) {
        $data = $r->validate([
            'name' => ['required','max:120'],
            // optional: terima json string
            'header_config'    => ['nullable'],
            'footer_config'    => ['nullable'],
            'signature_config' => ['nullable'],
            'layout_config'    => ['nullable'],
        ]);
        foreach (['header_config','footer_config','signature_config','layout_config'] as $k) {
            if (is_string($data[$k] ?? null)) $data[$k] = json_decode($data[$k], true) ?: null;
        }
        DocumentTemplate::create($data);
        return redirect()->route('admin.document_templates.index')->with('success','Template dibuat');
    }
}
