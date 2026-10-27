<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\SapInspection;
use App\Models\SapInspectionAnswer;
use App\Models\SapInspectionApproval;
use App\Models\SapInspectionAttachment;
use App\Models\SapInspectionCa;
use App\Models\SapInspectionFinding;
use App\Models\SapInspectionItem;
use App\Models\SapInspectionLog;
use App\Models\SapInspectionSchedule;
use App\Models\SapInspectionSection;
use App\Models\SapInspectionTemplate;
use App\Models\Site;
use App\Models\Department;
use App\Models\User;
use App\Services\Hse\HseDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SapInspeksiController extends Controller
{
    public function __construct(private readonly HseDashboardService $dashboardService)
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);
        $user = $request->user();

        $query = SapInspection::with(['template', 'site', 'department', 'inspector'])
            ->latest();

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if (!$user?->isSuperAdmin() && $user?->role === 'admin_site') {
            // restrict
        }

        $inspections = $query->paginate(20)->withQueryString();
        $templates = SapInspectionTemplate::where('is_active', true)->get();

        return view('hse.sap_inspeksi.index', compact('inspections', 'templates', 'filters', 'options'));
    }

    public function dashboard(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $base = SapInspection::query();
        if ($filters['scope_site_ids'] !== null) {
            $base->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($filters['site_id'])) {
            $base->where('site_id', $filters['site_id']);
        }
        if (!empty($filters['department_id'])) {
            $base->where('department_id', $filters['department_id']);
        }

        $kpis = [
            ['label' => 'Total Inspeksi', 'value' => (clone $base)->count(), 'unit' => 'dokumen'],
            ['label' => 'Draft', 'value' => (clone $base)->where('status', 'Draft')->count(), 'unit' => 'dokumen'],
            ['label' => 'Menunggu Review', 'value' => (clone $base)->where('status', 'Submitted')->count(), 'unit' => 'dokumen'],
            ['label' => 'Disetujui', 'value' => (clone $base)->whereIn('status', ['Approved', 'Closed'])->count(), 'unit' => 'dokumen'],
        ];

        $findingsBase = SapInspectionFinding::query()->whereHas('inspection', function ($q) use ($filters) {
            if ($filters['scope_site_ids'] !== null) {
                $q->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
            }
            if (!empty($filters['site_id'])) {
                $q->where('site_id', $filters['site_id']);
            }
            if (!empty($filters['department_id'])) {
                $q->where('department_id', $filters['department_id']);
            }
        });

        $findingStats = [
            'open' => (clone $findingsBase)->where('status', 'Open')->count(),
            'in_progress' => (clone $findingsBase)->where('status', 'In Progress')->count(),
            'for_verification' => (clone $findingsBase)->where('status', 'For Verification')->count(),
            'closed' => (clone $findingsBase)->whereIn('status', ['Closed', 'Verified'])->count(),
        ];

        $recent = SapInspection::with(['template', 'site', 'inspector'])
            ->when($filters['scope_site_ids'] !== null, fn ($q) => $q->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]))
            ->latest()->limit(10)->get();

        $templates = SapInspectionTemplate::where('is_active', true)->withCount('inspections')->get();

        return view('hse.sap_inspeksi.dashboard', compact('filters', 'options', 'kpis', 'findingStats', 'recent', 'templates'));
    }

    public function templates(Request $request)
    {
        $templates = SapInspectionTemplate::withCount(['sections', 'items', 'inspections'])
            ->latest()->get();
        return view('hse.sap_inspeksi.templates.index', compact('templates'));
    }

    public function templateShow(SapInspectionTemplate $template)
    {
        $template->load(['sections.items']);
        return view('hse.sap_inspeksi.templates.show', compact('template'));
    }

    public function create(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);
        $templates = SapInspectionTemplate::where('is_active', true)->with(['sections.items'])->get();
        $sites = $options['sites'] ?? Site::orderBy('name')->get();
        $departments = $options['departments'] ?? Department::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('hse.sap_inspeksi.create', compact('templates', 'sites', 'departments', 'users', 'filters', 'options'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['required', 'exists:sap_inspection_templates,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'inspector_id' => ['nullable', 'exists:users,id'],
            'auditor_id' => ['nullable', 'exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'schedule_id' => ['nullable', 'exists:sap_inspection_schedules,id'],
        ]);

        $template = SapInspectionTemplate::findOrFail($data['template_id']);
        $year = now()->format('Y');
        $count = SapInspection::whereYear('created_at', $year)->count() + 1;
        $code = $template->code ?: 'SAP';
        $inspectionNo = sprintf('SHE-INSP-%s-%s-%05d', Str::slug($code), $year, $count);

        $inspection = SapInspection::create([
            'inspection_no' => $inspectionNo,
            'template_id' => $template->id,
            'schedule_id' => $data['schedule_id'] ?? null,
            'site_id' => $data['site_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'inspector_id' => $data['inspector_id'] ?? $request->user()?->id,
            'auditor_id' => $data['auditor_id'] ?? null,
            'location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'Draft',
            'max_score' => 100,
        ]);

        $this->log($inspection, 'created', 'Inspeksi dibuat');

        return redirect()->route('hse.sap.inspeksi.edit', $inspection->id)->with('ok', 'Inspeksi berhasil dibuat. Silakan isi checklist.');
    }

    public function edit(SapInspection $inspection)
    {
        $inspection->load(['template.sections.items', 'answers']);
        return view('hse.sap_inspeksi.edit', compact('inspection'));
    }

    public function saveAnswers(Request $request, SapInspection $inspection)
    {
        if ($inspection->status !== 'Draft' && $inspection->status !== 'Returned') {
            return back()->with('error', 'Inspeksi tidak dapat diedit pada status saat ini.');
        }

        $answers = $request->input('answers', []);
        $remarks = $request->input('remarks', []);

        DB::beginTransaction();
        try {
            foreach ($answers as $itemId => $answerVal) {
                if ($answerVal === null || $answerVal === '') {
                    continue;
                }
                $item = SapInspectionItem::find($itemId);
                if (!$item) continue;

                $score = $this->normalizeScore($answerVal, $item->answer_type);
                $nonConformity = false;
                if ($item->answer_type === 'score_1_4') {
                    $nonConformity = in_array((int)$score, [1, 2]);
                } elseif (in_array($item->answer_type, ['yes_no', 'yes_no_na'])) {
                    $nonConformity = $answerVal === 'TIDAK';
                }

                SapInspectionAnswer::updateOrCreate(
                    ['inspection_id' => $inspection->id, 'item_id' => $itemId],
                    [
                        'section_id' => $item->section_id,
                        'answer' => $answerVal,
                        'score' => $score,
                        'remarks' => $remarks[$itemId] ?? null,
                        'non_conformity' => $nonConformity,
                    ]
                );
            }

            if (!$inspection->started_at) {
                $inspection->started_at = now();
            }
            $inspection->save();

            DB::commit();
            $this->log($inspection, 'saved_answers', 'Jawaban checklist disimpan');
            return back()->with('ok', 'Jawaban berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function submit(SapInspection $inspection)
    {
        if ($inspection->status !== 'Draft' && $inspection->status !== 'Returned') {
            return back()->with('error', 'Inspeksi tidak dapat diajukan pada status saat ini.');
        }

        $answers = $inspection->answers()->count();
        $totalItems = $inspection->template->items()->count();
        if ($answers < $totalItems) {
            return back()->with('error', "Belum semua item diisi. Terisi {$answers} dari {$totalItems} item.");
        }

        DB::beginTransaction();
        try {
            $this->calculateScore($inspection);
            $inspection->status = 'Submitted';
            $inspection->submitted_at = now();
            $inspection->completed_at = $inspection->completed_at ?? now();
            $inspection->save();

            SapInspectionApproval::create([
                'inspection_id' => $inspection->id,
                'actor_id' => auth()->id(),
                'action' => 'Submitted',
                'comments' => 'Diajukan untuk review',
            ]);

            $this->log($inspection, 'submitted', 'Inspeksi diajukan untuk review');

            // Auto create findings from non-conformities
            $this->createFindingsFromAnswers($inspection);

            DB::commit();
            return redirect()->route('hse.sap.inspeksi.show', $inspection->id)->with('ok', 'Inspeksi berhasil diajukan untuk review.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan: ' . $e->getMessage());
        }
    }

    public function show(SapInspection $inspection)
    {
        $inspection->load(['template.sections.items.answers', 'answers', 'findings.pic', 'findings.cas', 'approvals.actor', 'logs.user', 'site', 'department', 'inspector']);
        return view('hse.sap_inspeksi.show', compact('inspection'));
    }

    public function review(Request $request, SapInspection $inspection)
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,return'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($inspection->status !== 'Submitted' && $inspection->status !== 'Under Review') {
            return back()->with('error', 'Tidak dapat direview pada status saat ini.');
        }

        if ($data['action'] === 'return') {
            if (empty($data['review_notes'])) {
                return back()->with('error', 'Catatan review wajib diisi untuk pengembalian.');
            }
            $inspection->status = 'Returned';
            $inspection->review_notes = $data['review_notes'];
            $inspection->reviewed_by = auth()->id();
            $inspection->reviewed_at = now();
            $inspection->save();

            SapInspectionApproval::create([
                'inspection_id' => $inspection->id,
                'actor_id' => auth()->id(),
                'action' => 'Returned',
                'comments' => $data['review_notes'],
            ]);
            $this->log($inspection, 'returned', 'Dikembalikan untuk revisi: ' . $data['review_notes']);
            return back()->with('ok', 'Inspeksi dikembalikan untuk revisi.');
        }

        $inspection->status = 'Approved';
        $inspection->review_notes = $data['review_notes'];
        $inspection->reviewed_by = auth()->id();
        $inspection->reviewed_at = now();
        $inspection->approved_by = auth()->id();
        $inspection->approved_at = now();
        $inspection->save();

        SapInspectionApproval::create([
            'inspection_id' => $inspection->id,
            'actor_id' => auth()->id(),
            'action' => 'Approved',
            'comments' => $data['review_notes'] ?? 'Disetujui',
        ]);
        $this->log($inspection, 'approved', 'Inspeksi disetujui');
        return back()->with('ok', 'Inspeksi berhasil disetujui.');
    }

    public function findings(Request $request, SapInspection $inspection)
    {
        $inspection->load(['findings.pic', 'findings.cas']);
        return view('hse.sap_inspeksi.findings', compact('inspection'));
    }

    public function storeFinding(Request $request, SapInspection $inspection)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'risk_class' => ['required', 'in:Low,Medium,High,Critical'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'answer_id' => ['nullable', 'exists:sap_inspection_answers,id'],
            'item_id' => ['nullable', 'exists:sap_inspection_items,id'],
        ]);

        $year = now()->format('Y');
        $count = SapInspectionFinding::whereYear('created_at', $year)->count() + 1;
        $findingNo = sprintf('FIND-SAP-%s-%05d', $year, $count);

        SapInspectionFinding::create([
            'finding_no' => $findingNo,
            'inspection_id' => $inspection->id,
            'answer_id' => $data['answer_id'] ?? null,
            'item_id' => $data['item_id'] ?? null,
            'site_id' => $inspection->site_id,
            'department_id' => $inspection->department_id,
            'title' => $data['title'],
            'description' => $data['description'],
            'risk_class' => $data['risk_class'],
            'pic_id' => $data['pic_id'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => 'Open',
        ]);

        $this->log($inspection, 'finding_created', 'Temuan dibuat: ' . $data['title']);
        return back()->with('ok', 'Temuan berhasil ditambahkan.');
    }

    public function updateFinding(Request $request, SapInspectionFinding $finding)
    {
        $data = $request->validate([
            'status' => ['required', 'in:Open,In Progress,For Verification,Verified,Closed,Rejected'],
            'closure_notes' => ['nullable', 'string'],
            'verified_by' => ['nullable', 'exists:users,id'],
        ]);

        $finding->status = $data['status'];
        if (!empty($data['closure_notes'])) {
            $finding->closure_notes = $data['closure_notes'];
        }
        if ($data['status'] === 'Verified' || $data['status'] === 'Closed') {
            $finding->verified_by = $data['verified_by'] ?? auth()->id();
            $finding->verified_at = now();
            if ($data['status'] === 'Closed') {
                $finding->closed_at = now();
            }
        }
        $finding->save();

        $this->log($finding->inspection, 'finding_updated', 'Temuan ' . $finding->finding_no . ' status: ' . $data['status']);
        return back()->with('ok', 'Status temuan berhasil diperbarui.');
    }

    public function storeCa(Request $request, SapInspectionFinding $finding)
    {
        $data = $request->validate([
            'corrective_action' => ['required', 'string'],
            'preventive_action' => ['nullable', 'string'],
            'target_completion_date' => ['nullable', 'date'],
        ]);

        SapInspectionCa::create([
            'finding_id' => $finding->id,
            'inspection_id' => $finding->inspection_id,
            'action_by' => auth()->id(),
            'corrective_action' => $data['corrective_action'],
            'preventive_action' => $data['preventive_action'] ?? null,
            'target_completion_date' => $data['target_completion_date'] ?? null,
            'status' => 'Submitted',
        ]);

        if ($finding->status === 'Open') {
            $finding->status = 'In Progress';
            $finding->save();
        }

        $this->log($finding->inspection, 'ca_submitted', 'Tindakan perbaikan diajukan untuk temuan ' . $finding->finding_no);
        return back()->with('ok', 'Tindakan korektif/pencegahan berhasil diajukan.');
    }

    public function verifyCa(Request $request, SapInspectionCa $ca)
    {
        $data = $request->validate([
            'action' => ['required', 'in:verify,reject'],
            'verification_notes' => ['nullable', 'string'],
        ]);

        if ($data['action'] === 'reject') {
            $ca->status = 'Rejected';
            $ca->verification_notes = $data['verification_notes'];
            $ca->save();
            $this->log($ca->inspection, 'ca_rejected', 'CA ditolak: ' . ($data['verification_notes'] ?? ''));
            return back()->with('ok', 'Tindakan perbaikan ditolak.');
        }

        $ca->status = 'Verified';
        $ca->verified_by = auth()->id();
        $ca->verified_at = now();
        $ca->verification_notes = $data['verification_notes'];
        $ca->actual_completion_date = now()->toDateString();
        $ca->save();

        // Check if all cas verified
        $finding = $ca->finding;
        $pending = $finding->cas()->whereNotIn('status', ['Verified', 'Closed'])->count();
        if ($pending === 0) {
            $finding->status = 'For Verification';
            $finding->save();
        }

        $this->log($ca->inspection, 'ca_verified', 'CA diverifikasi untuk temuan ' . $finding->finding_no);
        return back()->with('ok', 'Tindakan perbaikan berhasil diverifikasi.');
    }

    private function normalizeScore($answerVal, $answerType)
    {
        if ($answerType === 'score_1_4') {
            return is_numeric($answerVal) ? (int)$answerVal : null;
        }
        if ($answerType === 'yes_no' || $answerType === 'yes_no_na') {
            if ($answerVal === 'YA') return 4;
            if ($answerVal === 'TIDAK') return 1;
            if ($answerVal === 'NA') return null;
        }
        return null;
    }

    private function calculateScore(SapInspection $inspection)
    {
        $answers = $inspection->answers()->whereNotNull('score')->get();
        $items = $inspection->template->items()->where('answer_type', '!=', 'text')->count();
        if ($items == 0) {
            $inspection->score = 100;
            $inspection->percentage = 100;
            return;
        }
        $totalScore = $answers->sum('score');
        $maxPossible = $items * 4;
        $percentage = ($totalScore / $maxPossible) * 100;
        $inspection->score = round($totalScore, 2);
        $inspection->percentage = round($percentage, 2);
    }

    private function createFindingsFromAnswers(SapInspection $inspection)
    {
        $answers = $inspection->answers()->where('non_conformity', true)->get();
        foreach ($answers as $answer) {
            if (SapInspectionFinding::where('answer_id', $answer->id)->exists()) {
                continue;
            }
            $item = $answer->item;
            $year = now()->format('Y');
            $count = SapInspectionFinding::whereYear('created_at', $year)->count() + 1;
            $findingNo = sprintf('FIND-SAP-%s-%05d', $year, $count);

            $risk = match ($answer->score) {
                1 => 'Critical',
                2 => 'High',
                default => 'Medium',
            };

            SapInspectionFinding::create([
                'finding_no' => $findingNo,
                'inspection_id' => $inspection->id,
                'answer_id' => $answer->id,
                'item_id' => $item->id,
                'site_id' => $inspection->site_id,
                'department_id' => $inspection->department_id,
                'title' => 'Ketidaksesuaian: ' . Str::limit(strip_tags($item->question), 100),
                'description' => 'Ditemukan ketidaksesuaian pada item inspeksi. Jawaban: ' . ($answer->answer ?? '-') . '. Catatan: ' . ($answer->remarks ?? '-'),
                'risk_class' => $risk,
                'pic_id' => null,
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'Open',
            ]);
        }
    }

    private function log(SapInspection $inspection, string $action, string $description, array $meta = [])
    {
        SapInspectionLog::create([
            'inspection_id' => $inspection->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'meta' => $meta ?: null,
        ]);
    }

    public function scheduleIndex(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $schedules = SapInspectionSchedule::with(['template', 'site', 'department', 'assignee'])
            ->latest()->paginate(20);
        return view('hse.sap_inspeksi.schedule.index', compact('schedules', 'filters'));
    }

    public function scheduleCreate(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);
        $templates = SapInspectionTemplate::where('is_active', true)->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('hse.sap_inspeksi.schedule.create', compact('templates', 'options', 'users', 'filters'));
    }

    public function scheduleStore(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['required', 'exists:sap_inspection_templates,id'],
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'period' => ['nullable', 'string'],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'integer'],
        ]);

        $data['created_by'] = auth()->id();
        $data['status'] = 'Planned';
        SapInspectionSchedule::create($data);

        return redirect()->route('hse.sap.inspeksi.schedule.index')->with('ok', 'Jadwal inspeksi berhasil dibuat.');
    }

    public function reports(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SapInspection::with(['template', 'site', 'department', 'inspector'])
            ->whereIn('status', ['Submitted', 'Under Review', 'Approved', 'Closed']);

        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        $inspections = $query->latest()->paginate(30)->withQueryString();
        $templates = SapInspectionTemplate::where('is_active', true)->get();

        return view('hse.sap_inspeksi.reports', compact('inspections', 'templates', 'filters', 'options'));
    }
}
