<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Document, DocumentAcl, User, Department};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentAclController extends Controller
{
    private const PERMS = ['view','edit','delete','share','export'];

    /**
     * Kelola ACL untuk satu dokumen (tampilan).
     */
    public function index(Document $document)
    {
        // Kelola akses → gunakan ability 'share'
        $this->authorize('share', $document);

        $acls = DocumentAcl::with(['user:id,name', 'department:id,name', 'document:id,title'])
            ->where('document_id', $document->id)
            ->orderBy('perm')
            ->get();

        $users       = User::orderBy('name')->get(['id','name']);
        $departments = Department::orderBy('name')->get(['id','name']);
        $documents   = Document::orderBy('title')->get(['id','title']);

        return view('admin.documents.acl', compact('document','acls','users','departments','documents'));
    }

    /**
     * STORE (single dokumen):
     * POST /admin/documents/{document}/acl
     * Mengizinkan user_only, dept_only, atau keduanya.
     */
    public function store(Request $r, Document $document)
    {
        $this->authorize('share', $document);

        $data = $r->validate([
            'user_id'        => ['nullable','integer','exists:users,id'],
            'department_id'  => ['nullable','integer','exists:departments,id'],
            'perm'           => ['required', Rule::in(self::PERMS)],
        ], [], [
            'user_id'       => 'User',
            'department_id' => 'Departemen',
            'perm'          => 'Permission',
        ]);

        $hasUser = !empty($data['user_id']);
        $hasDept = !empty($data['department_id']);

        // Minimal salah satu harus ada
        if (!$hasUser && !$hasDept) {
            return back()->withErrors([
                'user_id'       => 'Pilih minimal User atau Departemen.',
                'department_id' => 'Pilih minimal User atau Departemen.',
            ])->withInput();
        }

        // Susun target; kalau dua-duanya diisi → buat dua ACL
        $targets = [];
        if ($hasUser) $targets[] = ['user_id' => $data['user_id'], 'department_id' => null];
        if ($hasDept) $targets[] = ['user_id' => null, 'department_id' => $data['department_id']];

        $created = 0; $skipped = 0;

        foreach ($targets as $t) {
            $row = DocumentAcl::updateOrCreate(
                [
                    'document_id'   => $document->id,
                    'user_id'       => $t['user_id'],
                    'department_id' => $t['department_id'],
                    'perm'          => $data['perm'],
                ],
                [] // tak ada kolom lain untuk update
            );
            $row->wasRecentlyCreated ? $created++ : $skipped++;
        }

        $msg = "Akses ditambahkan: {$created} baru";
        if ($skipped > 0) $msg .= ", {$skipped} duplikat di-skip";
        return back()->with('success', $msg);
    }

    /**
     * STORE BULK (multi dokumen):
     * POST /admin/documents/acl
     * Mengizinkan user_only, dept_only, atau keduanya.
     */
    public function storeBulk(Request $r)
    {
        $data = $r->validate([
            'document_ids'   => ['required','array','min:1'],
            'document_ids.*' => ['integer','exists:documents,id'],
            'user_id'        => ['nullable','integer','exists:users,id'],
            'department_id'  => ['nullable','integer','exists:departments,id'],
            'perm'           => ['required', Rule::in(self::PERMS)],
        ], [], [
            'document_ids'  => 'Dokumen',
            'user_id'       => 'User',
            'department_id' => 'Departemen',
            'perm'          => 'Permission',
        ]);

        $hasUser = !empty($data['user_id']);
        $hasDept = !empty($data['department_id']);

        // Minimal salah satu harus ada
        if (!$hasUser && !$hasDept) {
            return back()->withErrors([
                'user_id'       => 'Pilih minimal User atau Departemen.',
                'department_id' => 'Pilih minimal User atau Departemen.',
            ])->withInput();
        }

        $targets = [];
        if ($hasUser) $targets[] = ['user_id' => $data['user_id'], 'department_id' => null];
        if ($hasDept) $targets[] = ['user_id' => null, 'department_id' => $data['department_id']];

        $created = 0; $skipped = 0;

        foreach ($data['document_ids'] as $docId) {
            $document = Document::findOrFail($docId);
            $this->authorize('share', $document);

            foreach ($targets as $t) {
                $row = DocumentAcl::updateOrCreate(
                    [
                        'document_id'   => $docId,
                        'user_id'       => $t['user_id'],
                        'department_id' => $t['department_id'],
                        'perm'          => $data['perm'],
                    ],
                    []
                );
                $row->wasRecentlyCreated ? $created++ : $skipped++;
            }
        }

        $msg = "Akses ditambahkan: {$created} baru";
        if ($skipped > 0) $msg .= ", {$skipped} duplikat di-skip";
        return back()->with('success', $msg);
    }

    public function destroy(Document $document, DocumentAcl $acl)
    {
        $this->authorize('share', $document);

        abort_if($acl->document_id !== $document->id, 404);

        $acl->delete();

        return back()->with('success','Akses dihapus.');
    }
}
