<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\SheDocument;
use App\Models\SheDocumentRevision;
use App\Models\SheNotification;
use App\Models\Department;
use App\Models\Site;
use App\Models\User;
use App\Services\Hse\HseDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class SheDocumentController extends Controller
{
    public function create(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);
        $departments = $options['departments'];
        return view('hse.operations.form-page', [
            'formKey'=>'document', 'title'=>'Upload Dokumen',
            'description'=>'Lengkapi metadata dan file dokumen sebelum diajukan untuk review.',
            'backHref'=>route('hse.dashboard.documents.index'),
            'options'=>$options, 'departments'=>$departments,
            'category'=>$request->query('category'),
        ]);
    }

    public function __construct(private readonly HseDashboardService $dashboardService)
    {
        $this->middleware(['auth']);
    }

    /**
     * Document Dashboard
     */
    public function dashboard(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SheDocument::query();
        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $query->where('site_id', $filters['site_id']);
        }
        $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
        if (!empty($deptId)) {
            $query->where('department_id', $deptId);
        }

        $allDocs = (clone $query)->get();
        $totalDocs = $allDocs->count();
        $published = $allDocs->where('status', 'Published')->count();
        $inReview = $allDocs->whereIn('status', ['Submitted', 'In Review'])->count();
        $drafts = $allDocs->where('status', 'Draft')->count();
        $obsolete = $allDocs->where('status', 'Obsolete')->count();

        // Expiring in next 30 days or already overdue for review
        $expiring = $allDocs->filter(function ($d) {
            return $d->status === 'Published' && $d->review_date && $d->review_date <= now()->addDays(30);
        })->count();

        // Stats by Category
        $byCategory = [
            'Policy' => $allDocs->where('category', 'Policy')->count(),
            'SOP' => $allDocs->where('category', 'SOP')->count(),
            'IK' => $allDocs->where('category', 'IK')->count(),
            'Form' => $allDocs->where('category', 'Form')->count(),
            'Other' => $allDocs->where('category', 'Other')->count(),
        ];

        // Recent Published Documents
        $recentDocs = (clone $query)->with(['department:id,name', 'site:id,name,code', 'uploader:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        // Expiring Documents List
        $expiringDocs = (clone $query)->with(['department:id,name', 'uploader:id,name'])
            ->where('status', 'Published')
            ->whereNotNull('review_date')
            ->whereDate('review_date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('review_date')
            ->limit(6)
            ->get();

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.documents.dashboard', compact(
            'filters', 'options', 'totalDocs', 'published', 'inReview', 'drafts', 'obsolete',
            'expiring', 'byCategory', 'recentDocs', 'expiringDocs', 'departments'
        ));
    }

    /**
     * Document List (Filtered by Category or Status)
     */
    public function index(Request $request, ?string $category = null)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SheDocument::with(['department:id,name', 'site:id,name,code', 'uploader:id,name', 'approver:id,name'])
            ->latest();

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if ($filters['site_id']) {
            $query->where('site_id', $filters['site_id']);
        }
        $deptId = $filters['department_id'] ?? $filters['dept_id'] ?? null;
        if (!empty($deptId)) {
            $query->where('department_id', $deptId);
        }

        // Submenu Mapping
        $title = 'All Documents';
        if ($category === 'policy') {
            $query->where('category', 'Policy');
            $title = 'Policy Repository';
        } elseif ($category === 'sop') {
            $query->where('category', 'SOP');
            $title = 'Standard Operating Procedures (SOP)';
        } elseif ($category === 'ik') {
            $query->where('category', 'IK');
            $title = 'Instruksi Kerja (IK)';
        } elseif ($category === 'form') {
            $query->where('category', 'Form');
            $title = 'Form Documents (Document Repository)';
        } elseif ($category === 'approval') {
            $query->whereIn('status', ['Submitted', 'In Review']);
            $title = 'Document Approval Queue';
        } elseif ($category === 'expiring') {
            $query->where('status', 'Published')
                  ->whereNotNull('review_date')
                  ->whereDate('review_date', '<=', now()->addDays(30)->toDateString());
            $title = 'Expiring / Review Due Documents';
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->input('department_id'));
        }
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($b) use ($q) {
                $b->where('document_code', 'like', "%{$q}%")
                  ->orWhere('title', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $documents = $query->paginate(\App\Support\Listing::perPage())->withQueryString();
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.documents.index', compact('filters', 'options', 'documents', 'title', 'category', 'departments'));
    }

    /**
     * Show Document Detail & Revision History
     */
    public function show(Request $request, SheDocument $document)
    {
        $user = $request->user();
        if ($user && !$this->canAccessDocument($user, $document)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat dokumen ini.');
        }

        $document->load(['department', 'site', 'uploader', 'approver', 'revisions.revisor']);
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('hse.operations.documents.show', compact('document', 'departments'));
    }

    /**
     * Store New Document
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'document_code' => ['required', 'string', 'max:100', 'unique:she_documents,document_code'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:Policy,SOP,IK,Form,Other'],
            'description' => ['nullable', 'string', 'max:2000'],
            'department_id' => ['required', 'exists:departments,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'version' => ['required', 'string', 'max:20'],
            'effective_date' => ['required', 'date'],
            'review_date' => ['nullable', 'date'],
            'file_document' => ['required', 'file', 'max:20480'], // max 20MB
        ]);

        $file = $request->file('file_document');
        $fileName = $file->getClientOriginalName();
        $fileType = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();
        $filePath = $file->store('she_documents', 'public');

        $doc = SheDocument::create([
            'document_code' => $data['document_code'],
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'department_id' => $data['department_id'],
            'site_id' => $data['site_id'] ?? null,
            'uploaded_by' => $request->user()?->id,
            'version' => $data['version'],
            'revision_number' => 0,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'effective_date' => $data['effective_date'],
            'review_date' => $data['review_date'] ?? now()->addYear()->toDateString(),
            'status' => 'Submitted',
        ]);

        // Create initial revision entry
        SheDocumentRevision::create([
            'document_id' => $doc->id,
            'revision_number' => 0,
            'version' => $data['version'],
            'file_path' => $filePath,
            'change_summary' => 'Initial document release.',
            'revised_by' => $request->user()?->id,
        ]);

        return redirect()->route('hse.dashboard.documents.show', $doc->id)->with('ok', 'Dokumen ' . $doc->document_code . ' berhasil diunggah dan diajukan untuk review.');
    }

    /**
     * Submit Revision / Update Document File
     */
    public function revise(Request $request, SheDocument $document)
    {
        $data = $request->validate([
            'version' => ['required', 'string', 'max:20'],
            'change_summary' => ['required', 'string', 'max:2000'],
            'effective_date' => ['required', 'date'],
            'review_date' => ['nullable', 'date'],
            'file_document' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file_document');
        $filePath = $file->store('she_documents', 'public');
        $nextRev = $document->revision_number + 1;

        SheDocumentRevision::create([
            'document_id' => $document->id,
            'revision_number' => $nextRev,
            'version' => $data['version'],
            'file_path' => $filePath,
            'change_summary' => $data['change_summary'],
            'revised_by' => $request->user()?->id,
        ]);

        $document->update([
            'version' => $data['version'],
            'revision_number' => $nextRev,
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'effective_date' => $data['effective_date'],
            'review_date' => $data['review_date'] ?? now()->addYear()->toDateString(),
            'status' => 'In Review',
        ]);

        return back()->with('ok', 'Revisi dokumen versi ' . $data['version'] . ' berhasil disimpan dan masuk ke In Review.');
    }

    /**
     * Approval Workflow (Approve, Publish, Reject)
     */
    public function approve(Request $request, SheDocument $document)
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve_publish,reject'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['action'] === 'approve_publish') {
            $document->update([
                'status' => 'Published',
                'approved_by' => $request->user()?->id,
                'approved_date' => now()->toDateString(),
                'rejection_reason' => null,
            ]);

            if ($document->uploaded_by) {
                SheNotification::notify(
                    userId: $document->uploaded_by,
                    title: 'Dokumen Disetujui & Diterbitkan',
                    message: "Dokumen {$document->document_code} - {$document->title} telah disetujui dan berstatus Published.",
                    type: 'document_approved',
                    priority: 'medium',
                    link: route('hse.dashboard.documents.show', $document->id)
                );
            }

            return back()->with('ok', 'Dokumen berhasil disetujui dan telah Diterbitkan (Published).');
        } else {
            $document->update([
                'status' => 'Draft',
                'rejection_reason' => $data['notes'] ?: 'Dokumen ditolak pada tahap review.',
            ]);

            if ($document->uploaded_by) {
                SheNotification::notify(
                    userId: $document->uploaded_by,
                    title: 'Review Dokumen Ditolak',
                    message: "Dokumen {$document->document_code} dikembalikan ke Draft: " . ($data['notes'] ?: 'Harap revisi dokumen.'),
                    type: 'document_rejected',
                    priority: 'high',
                    link: route('hse.dashboard.documents.show', $document->id)
                );
            }

            return back()->with('ok', 'Dokumen ditolak dan dikembalikan ke status Draft.');
        }
    }

    /**
     * Mark Document as Obsolete
     */
    public function obsolete(Request $request, SheDocument $document)
    {
        $document->update([
            'status' => 'Obsolete',
        ]);

        return back()->with('ok', 'Dokumen telah ditandai sebagai Obsolete / Kadaluarsa.');
    }

    /**
     * Download Document File
     */
    public function download(Request $request, SheDocument $document)
    {
        $user = $request->user();
        if ($user && !$this->canAccessDocument($user, $document)) {
            abort(403, 'Akses unduh ditolak untuk dokumen ini.');
        }

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan di server.');
        }

        $path = Storage::disk('public')->path($document->file_path);
        return response()->download($path, $document->file_name ?: ($document->document_code . '.' . $document->file_type));
    }

    /**
     * Preview Document (Inline)
     */
    public function preview(Request $request, SheDocument $document)
    {
        $user = $request->user();
        if ($user && !$this->canAccessDocument($user, $document)) {
            abort(403, 'Akses pratinjau ditolak untuk dokumen ini.');
        }

        if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        $path = Storage::disk('public')->path($document->file_path);
        $mime = Storage::disk('public')->mimeType($document->file_path);

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . ($document->file_name ?: 'document.pdf') . '"',
        ]);
    }

    private function canAccessDocument(User $user, SheDocument $doc): bool
    {
        if (in_array($user->role, ['super_admin', 'admin_ho'])) {
            return true;
        }

        // Draft check: only uploader or admin can access
        if ($doc->status === 'Draft' && $doc->uploaded_by !== $user->id) {
            return false;
        }

        // Site check: if document belongs to a specific site, user must have access to that site
        if ($doc->site_id) {
            $hasSite = $user->sites()->where('sites.id', $doc->site_id)->exists();
            if (!$hasSite) {
                return false;
            }
        }

        return true;
    }
}
