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
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /** Layout default untuk preview/merge jika template tidak punya layout */
    private const DEFAULT_LAYOUT = [
        'page'    => ['width' => 794, 'height' => 1123],
        'margins' => ['top' => 30, 'right' => 25, 'bottom' => 25, 'left' => 25],
        'font'    => ['size' => 11],
    ];

    /**
     * Daftar dokumen — hanya yang dapat diakses user (owner / departemen / ACL).
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
                $q->where('owner_id', $u->id)
                    ->orWhere(function ($qq) use ($u) {
                        $qq->whereNotNull('department_id')
                            ->where('department_id', $u->department_id);
                    })
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

        // payload templates utk dropdown + preview (sudah di-normalize)
        $templatesPayload = $templates->map(function ($t) {
            return $this->mapTemplateForClient($t);
        })->values();

        $defaultSections = [
            ['key' => 'tujuan',               'label' => 'Tujuan',                'html' => ''],
            ['key' => 'ruang_lingkup',        'label' => 'Ruang Lingkup',         'html' => ''],
            ['key' => 'referensi',            'label' => 'Referensi',             'html' => ''],
            ['key' => 'definisi',             'label' => 'Definisi',              'html' => ''],
            ['key' => 'tugas_tanggungjawab',  'label' => 'Tugas & Tanggung Jawab', 'html' => ''],
            ['key' => 'rincian_prosedur',     'label' => 'Rincian Prosedur',      'html' => ''],
            ['key' => 'alur_prosedur',        'label' => 'Alur Prosedur',         'html' => ''],
            ['key' => 'sanksi',               'label' => 'Sanksi',                'html' => ''],
            ['key' => 'lampiran',             'label' => 'Lampiran',              'html' => ''],
        ];

        return view('admin.documents.create', [
            'templates'        => $templates,           // untuk <option>
            'templatesPayload' => $templatesPayload,    // untuk JSON preview
            'departments'      => $departments,
            'defaultSections'  => $defaultSections,
        ]);
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

            // izinkan layout_config juga
            'layout_config'     => ['nullable'],
            'header_config'     => ['nullable'],
            'footer_config'     => ['nullable'],
            'signature_config'  => ['nullable'],
            'sections'          => ['nullable'],
        ]);

        // Decode JSON string → array
        foreach (['layout_config', 'header_config', 'footer_config', 'signature_config', 'sections'] as $k) {
            if (is_string($data[$k] ?? null)) $data[$k] = json_decode($data[$k], true) ?: null;
        }

        // Hydrate dari template bila dipilih
        $template = null;
        if (!empty($data['template_id'])) {
            $template = DocumentTemplate::find($data['template_id']);
            $data = $this->hydrateFromTemplate($template, $data);
        } else {
            // Tetapkan default layout walau tanpa template
            $data['layout_config'] = $data['layout_config'] ?? self::DEFAULT_LAYOUT;
        }

        // Auto nomor
        $dept = strtoupper($data['dept_code'] ?? 'GEN');
        $type = strtoupper($data['doc_type'] ?? 'SOP');
        $seq  = (Document::where('dept_code', $dept)->where('doc_type', $type)->max('id') ?? 0) + 1;
        $data['doc_no']   = sprintf('%s-%s-%03d', $dept, $type, $seq);
        $data['owner_id'] = Auth::id();

        $doc = Document::create($data);

        // Refresh signatures dari signature_config
        $doc->signatures()->delete();
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

        $document->load([
            'owner:id,name',
            'department:id,name',
            'acls' => fn($q) => $q->orderBy('perm')->orderBy('id'),
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

        $templatesPayload = $templates->map(function ($t) {
            return $this->mapTemplateForClient($t);
        })->values();

        $document->load([
            'acls' => fn($q) => $q->orderBy('perm')->orderBy('id'),
            'acls.user:id,name',
            'acls.department:id,name'
        ]);

        return view('admin.documents.edit', [
            'document'        => $document,
            'templates'       => $templates,
            'templatesPayload' => $templatesPayload,
            'departments'     => $departments,
        ]);
    }

    // app/Http/Controllers/Admin/DocumentController.php

    public function update(Request $r, Document $document)
    {
        $this->authorize('update', $document);

        // Ambil dan normalisasi payload
        $data = $r->validate([
            'template_id'       => ['nullable', 'integer'],
            'title'             => ['required', 'string', 'max:255'],
            'dept_code'         => ['nullable', 'string', 'max:50'],
            'doc_type'          => ['nullable', 'string', 'max:50'],
            'project_code'      => ['nullable', 'string', 'max:100'],
            'effective_date'    => ['nullable', 'date'],
            'class'             => ['nullable', 'in:I,II,III,IV'],
            'controlled_status' => ['nullable', 'in:controlled,uncontrolled,obsolete'],
            'department_id'     => ['nullable', 'integer', 'exists:departments,id'],
            'doc_no'            => ['nullable', 'string', 'max:100'],

            // string JSON dari form (hidden)
            'layout_config'     => ['nullable', 'string'],
            'header_config'     => ['nullable', 'string'],
            'footer_config'     => ['nullable', 'string'],
            'signature_config'  => ['nullable', 'string'],
            'sections'          => ['nullable', 'string'],

            // opsional
            'qr_text'           => ['nullable', 'string', 'max:500'],
            'barcode_text'      => ['nullable', 'string', 'max:500'],

            // NOTE: abaikan revision_no dari form (jika ada), kita yang atur sendiri.
        ]);

        $decode = function ($v) {
            if (is_null($v)) return null;
            if (is_string($v)) {
                $d = json_decode($v, true);
                return json_last_error() === JSON_ERROR_NONE ? $d : $v;
            }
            return $v;
        };

        // Siapkan nilai yang akan di-assign ke model
        $updates = [
            'template_id'       => $data['template_id']       ?? $document->template_id,
            'title'             => $data['title'],
            'dept_code'         => $data['dept_code']         ?? null,
            'doc_type'          => $data['doc_type']          ?? null,
            'project_code'      => $data['project_code']      ?? null,
            'effective_date'    => $data['effective_date']    ?? null,
            'class'             => $data['class']             ?? null,
            'controlled_status' => $data['controlled_status'] ?? $document->controlled_status,
            'department_id'     => $data['department_id']     ?? null,
            'doc_no'            => $data['doc_no']            ?? null,

            'layout_config'     => $decode($data['layout_config']    ?? null),
            'header_config'     => $decode($data['header_config']    ?? null),
            'footer_config'     => $decode($data['footer_config']    ?? null),
            'signature_config'  => $decode($data['signature_config'] ?? null),
            'sections'          => $decode($data['sections']         ?? null),

            'qr_text'           => $data['qr_text']           ?? null,
            'barcode_text'      => $data['barcode_text']      ?? null,
        ];

        // Isi model dengan perubahan
        $document->fill($updates);

        // Cek apakah ada field "konten" yang berubah
        $fieldsYangDicek = [
            'template_id',
            'title',
            'dept_code',
            'doc_type',
            'project_code',
            'effective_date',
            'class',
            'controlled_status',
            'department_id',
            'doc_no',
            'layout_config',
            'header_config',
            'footer_config',
            'signature_config',
            'sections',
            'qr_text',
            'barcode_text',
        ];
        $adaPerubahan = $document->isDirty($fieldsYangDicek);

        // Kalau ada perubahan, naikkan revision_no (abaikan input dari user)
        if ($adaPerubahan) {
            $document->revision_no = (int)($document->revision_no ?? 0) + 1;
        }

        $document->save();

        // Setelah update → balik ke index + flash message
        return redirect()
            ->route('admin.documents.index')
            ->with('ok', 'Dokumen berhasil diperbarui (Rev ' . $document->revision_no . ').');
    }



    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        $document->delete();

        return redirect()->route('admin.documents.index')->with('success', 'Dokumen dihapus');
    }

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

    public function revoke(Document $document, DocumentAcl $acl)
    {
        $this->authorize('share', $document);

        if ((int) $acl->document_id !== (int) $document->id) {
            abort(404);
        }

        $acl->delete();

        return back()->with('success', 'Akses dihapus.');
    }

    public function export(Document $document)
    {
        $this->authorize('export', $document);

        $document->load(['owner:id,name', 'department:id,name', 'signatures' => fn($q) => $q->orderBy('order')]);

        $view = 'admin.documents.export';

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, [
                'document' => $document
            ])->setPaper('a4', 'portrait');

            $filename = Str::slug($document->doc_no . '-' . $document->title) . '.pdf';
            return $pdf->download($filename);
        }

        $html = view($view, ['document' => $document])->render();
        $filename = Str::slug($document->doc_no . '-' . $document->title) . '.html';

        return response($html, 200, [
            'Content-Type'        => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /* =======================
     * Helpers
     * ======================= */

    /** Siapkan payload template untuk klien (Blade/Alpine) */
    private function mapTemplateForClient(DocumentTemplate $t): array
    {
        $layout    = $this->decodeArray($t->layout_config);
        $blocks    = $this->decodeArray($t->blocks_config);
        $header    = $this->decodeArray($t->header_config);
        $footer    = $this->decodeArray($t->footer_config);
        $signature = $this->decodeArray($t->signature_config);

        // merge layout dengan default
        $layout = array_replace_recursive(self::DEFAULT_LAYOUT, $layout ?? []);

        return [
            'id'        => $t->id,
            'name'      => $t->name,
            'layout'    => $layout,
            'blocks'    => $blocks,
            'header'    => $header,
            'footer'    => $footer,
            'signature' => $signature,
            'updated_at' => optional($t->updated_at)->toIso8601String(),
        ];
    }

    private function decodeArray($value): array
    {
        if (is_array($value)) return $value;
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }
    /** Tarik default dari template untuk field yang kosong / tidak dikirim */
    private function hydrateFromTemplate(?DocumentTemplate $t, array $data): array
    {
        // Default layout bila template null
        $defaultLayout = self::DEFAULT_LAYOUT;

        // Decode semua dari template (dengan helper yang sudah ada)
        $layout    = $t ? $this->decodeArray($t->layout_config)    : [];
        $header    = $t ? $this->decodeArray($t->header_config)    : [];
        $footer    = $t ? $this->decodeArray($t->footer_config)    : [];
        $signature = $t ? $this->decodeArray($t->signature_config) : [];

        // Jika request tidak mengirim, isi dari template
        $data['layout_config']    = $data['layout_config']    ?? array_replace_recursive($defaultLayout, $layout);
        $data['header_config']    = $data['header_config']    ?? $header;
        $data['footer_config']    = $data['footer_config']    ?? $footer;
        $data['signature_config'] = $data['signature_config'] ?? $signature;

        // Normalisasi sections: jika kosong, bisa pakai blok/konvensi default
        if (!isset($data['sections']) || empty($data['sections'])) {
            // Jika template punya blocks dengan default sections, boleh mapping di sini.
            // Untuk sekarang biarkan null → editor isi sendiri.
            $data['sections'] = null;
        }

        return $data;
    }
}
