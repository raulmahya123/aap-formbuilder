<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Document, DocumentSignature, DocumentTemplate, DocumentAcl, Department};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index(Request $r){
        $docs = Document::with(['owner','department'])->latest()->paginate(20);
        return view('admin.documents.index', compact('docs'));
    }

    public function create(){
        $templates   = DocumentTemplate::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $defaultSections = [
            ['key'=>'tujuan','label'=>'Tujuan','html'=>''],
            ['key'=>'ruang_lingkup','label'=>'Ruang Lingkup','html'=>''],
            ['key'=>'referensi','label'=>'Referensi','html'=>''],
            ['key'=>'definisi','label'=>'Definisi','html'=>''],
            ['key'=>'tugas_tanggungjawab','label'=>'Tugas & Tanggung Jawab','html'=>''],
            ['key'=>'rincian_prosedur','label'=>'Rincian Prosedur','html'=>''],
            ['key'=>'alur_prosedur','label'=>'Alur Prosedur','html'=>''],
            ['key'=>'sanksi','label'=>'Sanksi','html'=>''],
            ['key'=>'lampiran','label'=>'Lampiran','html'=>''],
        ];
        return view('admin.documents.create', compact('templates','departments','defaultSections'));
    }

    public function store(Request $r){
        $data = $r->validate([
            'template_id'=>['nullable','exists:document_templates,id'],
            'title'=>['required','max:255'],
            'dept_code'=>['nullable','max:10'],
            'doc_type'=>['nullable','max:10'],
            'project_code'=>['nullable','max:10'],
            'revision_no'=>['nullable','integer','min:0'],
            'effective_date'=>['nullable','date'],
            'controlled_status'=>['required','in:controlled,uncontrolled,obsolete'],
            'class'=>['nullable','in:I,II,III,IV'],
            'department_id'=>['nullable','exists:departments,id'],
            'header_config'=>['nullable'],
            'footer_config'=>['nullable'],
            'signature_config'=>['nullable'],
            'sections'=>['nullable'],
        ]);

        foreach (['header_config','footer_config','signature_config','sections'] as $k) {
            if (is_string($data[$k] ?? null)) $data[$k] = json_decode($data[$k], true) ?: null;
        }

        // auto nomor sederhana
        $dept = strtoupper($data['dept_code'] ?? 'GEN');
        $type = strtoupper($data['doc_type'] ?? 'SOP');
        $seq  = (Document::where('dept_code',$dept)->where('doc_type',$type)->max('id') ?? 0) + 1;
        $data['doc_no'] = sprintf('%s-%s-%03d', $dept, $type, $seq);

        $data['owner_id'] = Auth::id();
        $doc = Document::create($data);

        foreach (($data['signature_config']['rows'] ?? []) as $i => $row) {
            $doc->signatures()->create([
                'role' => $row['role'] ?? 'Signer',
                'name' => $row['name'] ?? null,
                'position_title' => $row['position_title'] ?? null,
                'image_path' => $row['image_path'] ?? null,
                'order' => $i,
            ]);
        }

        return redirect()->route('admin.documents.edit',$doc)->with('success','Dokumen dibuat');
    }

    public function show(Document $document){
        $this->authorize('view',$document);
        return view('admin.documents.show', compact('document'));
    }

    public function edit(Document $document){
        $this->authorize('update',$document);
        $templates   = DocumentTemplate::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return view('admin.documents.edit', compact('document','templates','departments'));
    }

    public function update(Request $r, Document $document){
        $this->authorize('update',$document);
        $data = $r->validate([
            'title'=>['required','max:255'],
            'dept_code'=>['nullable','max:10'],
            'doc_type'=>['nullable','max:10'],
            'project_code'=>['nullable','max:10'],
            'revision_no'=>['nullable','integer','min:0'],
            'effective_date'=>['nullable','date'],
            'controlled_status'=>['required','in:controlled,uncontrolled,obsolete'],
            'class'=>['nullable','in:I,II,III,IV'],
            'department_id'=>['nullable','exists:departments,id'],
            'header_config'=>['nullable'],
            'footer_config'=>['nullable'],
            'signature_config'=>['nullable'],
            'sections'=>['nullable'],
        ]);

        foreach (['header_config','footer_config','signature_config','sections'] as $k) {
            if (is_string($data[$k] ?? null)) $data[$k] = json_decode($data[$k], true) ?: null;
        }

        $document->update($data);

        // refresh signatures
        $document->signatures()->delete();
        foreach (($data['signature_config']['rows'] ?? []) as $i => $row) {
            $document->signatures()->create([
                'role'=>$row['role'] ?? 'Signer',
                'name'=>$row['name'] ?? null,
                'position_title'=>$row['position_title'] ?? null,
                'image_path'=>$row['image_path'] ?? null,
                'order'=>$i,
            ]);
        }

        return back()->with('success','Dokumen diperbarui');
    }

    public function destroy(Document $document){
        $this->authorize('delete',$document);
        $document->delete();
        return redirect()->route('admin.documents.index')->with('success','Dokumen dihapus');
    }

    // Bagikan akses (ACL)
    public function share(Request $r, Document $document){
        $this->authorize('share',$document);
        $data = $r->validate([
            'perm' => ['required','in:view,edit,share,export,delete'],
            'user_id' => ['nullable','exists:users,id'],
            'department_id' => ['nullable','exists:departments,id'],
        ]);
        if(!$data['user_id'] && !$data['department_id']){
            return back()->with('error','Pilih user atau department.');
        }
        \App\Models\DocumentAcl::updateOrCreate(
            ['document_id'=>$document->id,'user_id'=>$data['user_id'],'department_id'=>$data['department_id'],'perm'=>$data['perm']],
            []
        );
        return back()->with('success','Akses dibagikan');
    }
}
