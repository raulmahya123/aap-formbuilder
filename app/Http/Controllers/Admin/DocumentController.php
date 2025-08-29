<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Document,
    DocumentSignature,
    DocumentTemplate,
    DocumentAcl,
    Department
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Daftar dokumen — hanya yang dapat diakses user:
     * - Owner
     * - Satu departemen (auto-allow view/export)
     * - Ada ACL (user/department) dengan perm view|edit|delete|share|export
     */
    public function index(Request $r)
    {
        $this->authorize('viewAny', Document::class);
        $u = Auth::user();

        $docs = Document::query()
            ->with(['owner:id,name', 'department:id,name'])
            ->when($r->filled('q'), function ($q) use ($r) {
                $term = trim($r->input('q'));
                $q->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', "%{$term}%")
                       ->orWhere('doc_no', 'like', "%{$term}%")
                       ->orWhere('dept_code', 'like', "%{$term}%")
                       ->orWhere('doc_type', 'like', "%{$term}%");
                });
            })
            ->where(function ($q) use ($u) {
                // Owner
                $q->where('owner_id', $u->id)
                  // Satu departemen
                  ->orWhere(function ($qq) use ($u) {
                      $qq->whereNotNull('department_id')
                         ->where('department_id', $u->department_id);
                  })
                  // ACL (user/department) yang memberikan akses
                  ->orWhereExists(function ($qq) use ($u) {
                      $qq->selectRaw(1)
                        ->from('document_acls as da')
                        ->whereColumn('da.document_id', 'documents.id')
                        ->where(function ($qb) use ($u) {
                            $qb->where('da.user_id', $u->id)
                               ->orWhere('da.department_id', $u->department_id);
                        })
                        ->whereIn('da.perm', ['view', 'edit', 'delete', 'share', 'export']);
                  });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.documents.index', compact('docs'));
    }

    public function create()
    {
        $this->authorize('create', Document::class);

        $templates   = DocumentTemplate::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        $defaultSections = [
            ['key' => 'tujuan',               'label' => 'Tujuan',                'html' => ''],
            ['key' => 'ruang_lingkup',        'label' => 'Ruang Lingkup',         'html' => ''],
            ['key' => 'referensi',            'label' => 'Referensi',             'html' => ''],
            ['key' => 'definisi',             'label' => 'Definisi',              'html' => ''],
            ['key' => 'tugas_tanggungjawab',  'label' => 'Tugas & Tanggung Jawab','html' => ''],
            ['key' => 'rincian_prosedur',     'label' => 'Rincian Prosedur',      'html' => ''],
            ['key' => 'alur_prosedur',        'label' => 'Alur Prosedur',         'html' => ''],
            ['key' => 'sanksi',               'label' => 'Sanksi',                'html' => ''],
            ['key' => 'lampiran',             'label' => 'Lampiran',              'html' => ''],
        ];

        return view('admin.documents.create', compact('templates', 'departments', 'defaultSections'));
    }

    public function store(Request $r)
    {
        $this->authorize('create', Document::class);

        $data = $r->validate([
            'template_id'       => ['nullable', 'exists:document_templates,id'],
            'title'             => ['required', 'max:255'],
            'dept_code'         => ['nullable', 'max:10'],
            'doc_type'          => ['nullable', 'max:10'],
            'project_code'      => ['nullable', 'max:10'],
            'revision_no'       => ['nullable', 'integer', 'min:0'],
            'effective_date'    => ['nullable', 'date'],
            'controlled_status' => ['required', 'in:controlled,uncontrolled,obsolete'],
            'class'             => ['nullable', 'in:I,II,III,IV'],
            'department_id'     => ['nullable', 'exists:departments,id'],
            'header_config'     => ['nullable'],
            'footer_config'     => ['nullable'],
            'signature_config'  => ['nullable'],
            'sections'          => ['nullable'],
        ]);

        foreach (['header_config', 'footer_config', 'signature_config', 'sections'] as $k) {
            if (is_string($data[$k] ?? null)) {
                $data[$k] = json_decode($data[$k], true) ?: null;
            }
        }

        // Auto nomor sederhana berdasarkan dept_code + doc_type
        $dept = strtoupper($data['dept_code'] ?? 'GEN');
        $type = strtoupper($data['doc_type'] ?? 'SOP');
        $seq  = (Document::where('dept_code', $dept)->where('doc_type', $type)->max('id') ?? 0) + 1;
        $data['doc_no']   = sprintf('%s-%s-%03d', $dept, $type, $seq);
        $data['owner_id'] = Auth::id();

        $doc = Document::create($data);

        // Simpan baris tanda tangan
        foreach (($data['signature_config']['rows'] ?? []) as $i => $row) {
            $doc->signatures()->create([
                'role'           => $row['role'] ?? 'Signer',
                'name'           => $row['name'] ?? null,
                'position_title' => $row['position_title'] ?? null,
                'image_path'     => $row['image_path'] ?? null,
                'order'          => $i,
            ]);
        }

        return redirect()->route('admin.documents.edit', $doc)->with('success', 'Dokumen dibuat');
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);

        // tampilkan ACL & relasi supaya UI bisa render daftar akses
        $document->load([
            'owner:id,name',
            'department:id,name',
            'acls' => function ($q) {
                $q->orderBy('perm')->orderBy('id');
            },
            'acls.user:id,name',
            'acls.department:id,name'
        ]);

        return view('admin.documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $this->authorize('update', $document);

        $templates   = DocumentTemplate::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        // load ACL agar form share bisa ditampilkan di halaman edit
        $document->load([
            'acls' => function ($q) {
                $q->orderBy('perm')->orderBy('id');
            },
            'acls.user:id,name',
            'acls.department:id,name'
        ]);

        return view('admin.documents.edit', compact('document', 'templates', 'departments'));
    }

    public function update(Request $r, Document $document)
    {
        $this->authorize('update', $document);

        $data = $r->validate([
            'title'             => ['required', 'max:255'],
            'dept_code'         => ['nullable', 'max:10'],
            'doc_type'          => ['nullable', 'max:10'],
            'project_code'      => ['nullable', 'max:10'],
            'revision_no'       => ['nullable', 'integer', 'min:0'],
            'effective_date'    => ['nullable', 'date'],
            'controlled_status' => ['required', 'in:controlled,uncontrolled,obsolete'],
            'class'             => ['nullable', 'in:I,II,III,IV'],
            'department_id'     => ['nullable', 'exists:departments,id'],
            'header_config'     => ['nullable'],
            'footer_config'     => ['nullable'],
            'signature_config'  => ['nullable'],
            'sections'          => ['nullable'],
        ]);

        foreach (['header_config', 'footer_config', 'signature_config', 'sections'] as $k) {
            if (is_string($data[$k] ?? null)) {
                $data[$k] = json_decode($data[$k], true) ?: null;
            }
        }

        $document->update($data);

        // refresh signatures
        $document->signatures()->delete();
        foreach (($data['signature_config']['rows'] ?? []) as $i => $row) {
            $document->signatures()->create([
                'role'           => $row['role'] ?? 'Signer',
                'name'           => $row['name'] ?? null,
                'position_title' => $row['position_title'] ?? null,
                'image_path'     => $row['image_path'] ?? null,
                'order'          => $i,
            ]);
        }

        return back()->with('success', 'Dokumen diperbarui');
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        $document->delete();

        return redirect()->route('admin.documents.index')->with('success', 'Dokumen dihapus');
    }

    /**
     * Bagikan akses (ACL)
     * - target: user_id atau department_id (salah satu wajib ada)
     */
    public function share(Request $r, Document $document)
    {
        $this->authorize('share', $document);

        $data = $r->validate([
            'perm'          => ['required', 'in:view,edit,share,export,delete'],
            'user_id'       => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        if (!$data['user_id'] && !$data['department_id']) {
            return back()->with('error', 'Pilih user atau department.');
        }

        DocumentAcl::updateOrCreate(
            [
                'document_id'   => $document->id,
                'user_id'       => $data['user_id'],
                'department_id' => $data['department_id'],
                'perm'          => $data['perm'],
            ],
            []
        );

        return back()->with('success', 'Akses dibagikan');
    }

    /**
     * Cabut akses (hapus baris ACL)
     * Route contoh:
     * DELETE /admin/documents/{document}/acl/{acl}  -> name: admin.documents.acl.revoke
     */
    public function revoke(Document $document, DocumentAcl $acl)
    {
        $this->authorize('share', $document);

        // pastikan ACL milik dokumen ini
        if ((int) $acl->document_id !== (int) $document->id) {
            abort(404);
        }

        $acl->delete();

        return back()->with('success', 'Akses dihapus.');
    }

    /**
     * Export dokumen (PDF).
     * - Pakai barryvdh/laravel-dompdf jika tersedia.
     * - Jika tidak, fallback ke download HTML.
     */
    public function export(Document $document)
    {
        $this->authorize('export', $document);

        $document->load(['owner:id,name', 'department:id,name', 'signatures' => function ($q) {
            $q->orderBy('order');
        }]);

        $view = 'admin.documents.export'; // siapkan blade export sesuai kebutuhan

        // Jika DomPDF tersedia
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
                'document' => $document
            ])->setPaper('a4', 'portrait');

            $filename = Str::slug($document->doc_no . '-' . $document->title) . '.pdf';
            return $pdf->download($filename);
        }

        // Fallback: unduh HTML
        $html = view($view, ['document' => $document])->render();
        $filename = Str::slug($document->doc_no . '-' . $document->title) . '.html';

        return response($html, 200, [
            'Content-Type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
