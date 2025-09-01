<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Document, DocumentAcl, User, Department};
use Illuminate\Http\Request;

class DocumentAclController extends Controller
{
    public function index(Document $document)
    {
        $this->authorize('update', $document);
        $acls = DocumentAcl::with(['user','department'])
            ->where('document_id',$document->id)
            ->get();

        $users = User::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('admin.documents.acl', compact('document','acls','users','departments'));
    }

    public function store(Request $r, Document $document)
    {
        $this->authorize('update', $document);

        DocumentAcl::create([
            'document_id'   => $document->id,
            'user_id'       => $r->user_id,
            'department_id' => $r->department_id,
            'perm'          => $r->perm,
        ]);

        return back()->with('success','Akses ditambahkan.');
    }

    public function destroy(Document $document, DocumentAcl $acl)
    {
        $this->authorize('update',$document);
        $acl->delete();
        return back()->with('success','Akses dihapus.');
    }
}
