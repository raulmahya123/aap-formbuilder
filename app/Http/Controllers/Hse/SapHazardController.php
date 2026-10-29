<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\SapHazardActivityLog;
use App\Models\SapHazardApproval;
use App\Models\SapHazardAttachment;
use App\Models\SapHazardCorrectiveAction;
use App\Models\SapHazardReport;
use App\Models\SapHazardRiskAssessment;
use App\Models\SapHazardTemporaryAction;
use App\Models\Site;
use App\Models\Department;
use App\Models\User;
use App\Services\Hse\HseDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SapHazardController extends Controller
{
    public function __construct(private readonly HseDashboardService $dashboardService)
    {
        $this->middleware(['auth']);
    }

    public function dashboard(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $base = SapHazardReport::query();
        if ($filters['scope_site_ids'] !== null) {
            $base->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($filters['site_id'])) $base->where('site_id', $filters['site_id']);
        if (!empty($filters['department_id'])) $base->where('department_id', $filters['department_id']);
        $base->whereBetween('report_date', [$filters['from']->toDateString(), $filters['to']->toDateString()]);

        $kpis = [
            ['label' => 'Total Hazard Report', 'value' => (clone $base)->count(), 'unit' => 'laporan'],
            ['label' => 'Submitted/Review', 'value' => (clone $base)->whereIn('status', ['Submitted', 'SHE Review'])->count(), 'unit' => 'laporan'],
            ['label' => 'Open/In Progress', 'value' => (clone $base)->whereIn('status', ['Assigned', 'In Progress', 'Reopened'])->count(), 'unit' => 'laporan'],
            ['label' => 'Closed', 'value' => (clone $base)->where('status', 'Closed')->count(), 'unit' => 'laporan'],
            ['label' => 'Very High Risk', 'value' => (clone $base)->where('risk_level', 'Very High')->count(), 'unit' => 'laporan'],
            ['label' => 'High Risk', 'value' => (clone $base)->where('risk_level', 'High')->count(), 'unit' => 'laporan'],
        ];

        $recent = (clone $base)->with(['reporter', 'site', 'assignedPic'])
            ->latest('report_date')->limit(10)->get();

        $caOpen = SapHazardCorrectiveAction::whereHas('hazard', function ($q) use ($filters) {
            if ($filters['scope_site_ids'] !== null) $q->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        })->whereNotIn('status', ['Verified', 'Closed'])->count();

        return view('hse.sap_hazard.dashboard', compact('filters', 'options', 'kpis', 'recent', 'caOpen'));
    }

    public function index(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SapHazardReport::with(['reporter', 'site', 'department', 'assignedPic'])->latest();
        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($filters['site_id'])) $query->where('site_id', $filters['site_id']);
        if (!empty($filters['department_id'])) $query->where('department_id', $filters['department_id']);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('risk_level')) $query->where('risk_level', $request->risk_level);

        $hazards = $query->paginate(\App\Support\Listing::perPage())->withQueryString();
        return view('hse.sap_hazard.index', compact('hazards', 'filters', 'options'));
    }

    public function create(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('hse.sap_hazard.create', compact('options', 'users', 'filters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'location_detail' => ['nullable', 'string', 'max:500'],
            'activity_related' => ['nullable', 'string', 'max:500'],
            'hazard_description' => ['required', 'string'],
            'potential_impact' => ['nullable', 'string'],
            'hazard_type' => ['required', 'in:Unsafe Action,Unsafe Condition,Environment,Equipment,Other'],
            'report_date' => ['nullable', 'date'],
            'report_time' => ['nullable', 'date_format:H:i'],
        ]);

        $year = now()->format('Y');
        $count = SapHazardReport::whereYear('created_at', $year)->count() + 1;
        $hazardNo = sprintf('SAP-HZD-%s-%05d', $year, $count);

        $reporter = $request->user();

        $hazard = SapHazardReport::create([
            'hazard_no' => $hazardNo,
            'document_code' => 'SHE-FM-007',
            'site_id' => $data['site_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'reporter_id' => $reporter->id,
            'reporter_nik' => $reporter->nik,
            'reporter_position' => $reporter->position,
            'reporter_dept' => $reporter->department?->name ?? $reporter->division,
            'report_date' => $data['report_date'] ?? now()->toDateString(),
            'report_time' => $data['report_time'] ?? now()->format('H:i'),
            'location' => $data['location'] ?? null,
            'location_detail' => $data['location_detail'] ?? null,
            'activity_related' => $data['activity_related'] ?? null,
            'hazard_description' => $data['hazard_description'],
            'potential_impact' => $data['potential_impact'] ?? null,
            'hazard_type' => $data['hazard_type'],
            'status' => 'Draft',
        ]);

        $this->log($hazard, 'created', 'Hazard Report dibuat');
        return redirect()->route('hse.dashboard.sap.hazard.edit', $hazard->id)->with('ok', 'Laporan bahaya berhasil dibuat. Lanjutkan penilaian risiko.');
    }

    public function edit(SapHazardReport $hazard)
    {
        $hazard->load(['riskAssessments', 'temporaryActions', 'correctiveActions.pic', 'attachments']);
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('hse.sap_hazard.edit', compact('hazard', 'users'));
    }

    public function assessRisk(Request $request, SapHazardReport $hazard)
    {
        if (!in_array($hazard->status, ['Draft', 'Returned', 'Revised'])) {
            return back()->with('error', 'Tidak dapat menilai risiko pada status saat ini.');
        }

        $data = $request->validate([
            'probability' => ['required', 'integer', 'min:1', 'max:5'],
            'severity' => ['required', 'integer', 'min:1', 'max:5'],
            'severity_dimensions' => ['nullable', 'array'],
            'risk_notes' => ['nullable', 'string'],
        ]);

        $p = (int)$data['probability'];
        $s = (int)$data['severity'];
        $score = $p * $s;
        $level = $this->riskLevel($score);

        DB::beginTransaction();
        try {
            SapHazardRiskAssessment::create([
                'hazard_id' => $hazard->id,
                'probability' => $p,
                'severity' => $s,
                'severity_dimensions' => $data['severity_dimensions'] ?? null,
                'risk_score' => $score,
                'risk_level' => $level,
                'justification' => $data['risk_notes'] ?? null,
                'assessed_by' => auth()->id(),
            ]);

            $hazard->probability = $p;
            $hazard->severity = $s;
            $hazard->severity_dimensions = $data['severity_dimensions'] ?? null;
            $hazard->risk_score = $score;
            $hazard->risk_level = $level;
            $hazard->is_critical = $level === 'Very High';
            $hazard->save();

            DB::commit();
            $this->log($hazard, 'risk_assessed', "Risiko dinilai: P{$p} x S{$s} = {$score} ({$level})");
            return back()->with('ok', "Penilaian risiko berhasil. Skor: {$score}, Level: {$level}");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menilai risiko: ' . $e->getMessage());
        }
    }

    private function riskLevel(int $score): string
    {
        if ($score >= 20) return 'Very High';
        if ($score >= 11) return 'High';
        if ($score >= 4) return 'Medium';
        return 'Low';
    }

    public function storeTemporaryAction(Request $request, SapHazardReport $hazard)
    {
        $data = $request->validate([
            'description' => ['required', 'string'],
            'action_date' => ['nullable', 'date'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        SapHazardTemporaryAction::create([
            'hazard_id' => $hazard->id,
            'description' => $data['description'],
            'action_date' => $data['action_date'] ?? now()->toDateString(),
            'pic_id' => $data['pic_id'] ?? auth()->id(),
            'status' => $data['status'] ?? 'Done',
            'remarks' => $data['remarks'] ?? null,
            'performed_by' => auth()->id(),
        ]);

        $this->log($hazard, 'temp_action_added', 'Tindakan sementara ditambahkan');
        return back()->with('ok', 'Tindakan sementara berhasil disimpan.');
    }

    public function submit(SapHazardReport $hazard)
    {
        if (!in_array($hazard->status, ['Draft', 'Returned', 'Revised'])) {
            return back()->with('error', 'Tidak dapat diajukan pada status saat ini.');
        }
        if (!$hazard->risk_score) {
            return back()->with('error', 'Penilaian risiko wajib dilakukan sebelum diajukan.');
        }

        DB::beginTransaction();
        try {
            $hazard->status = 'Submitted';
            $hazard->submitted_at = now();
            $hazard->save();

            SapHazardApproval::create([
                'hazard_id' => $hazard->id,
                'actor_id' => auth()->id(),
                'action' => 'Submitted',
                'comments' => 'Diajukan untuk review SHE',
            ]);

            $this->log($hazard, 'submitted', 'Hazard Report diajukan');
            DB::commit();
            return redirect()->route('hse.dashboard.sap.hazard.show', $hazard->id)->with('ok', 'Hazard Report berhasil diajukan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan: ' . $e->getMessage());
        }
    }

    public function show(SapHazardReport $hazard)
    {
        $hazard->load(['reporter', 'site', 'department', 'assignedPic', 'riskAssessments.assessedBy', 'temporaryActions.pic', 'correctiveActions.pic', 'correctiveActions.verifiedBy', 'approvals.actor', 'logs.user']);
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('hse.sap_hazard.show', compact('hazard', 'users'));
    }

    public function review(Request $request, SapHazardReport $hazard)
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,return,assign'],
            'review_notes' => ['nullable', 'string'],
            'assigned_pic_id' => ['nullable', 'exists:users,id'],
        ]);

        if (!in_array($hazard->status, ['Submitted', 'SHE Review'])) {
            return back()->with('error', 'Tidak dapat direview pada status saat ini.');
        }

        if ($data['action'] === 'return') {
            if (empty($data['review_notes'])) return back()->with('error', 'Catatan wajib diisi.');
            $hazard->status = 'Returned';
            $hazard->review_notes = $data['review_notes'];
            $hazard->reviewed_by = auth()->id();
            $hazard->reviewed_at = now();
            $hazard->save();
            SapHazardApproval::create(['hazard_id' => $hazard->id, 'actor_id' => auth()->id(), 'action' => 'Returned', 'comments' => $data['review_notes']]);
            $this->log($hazard, 'returned', 'Dikembalikan untuk perbaikan');
            return back()->with('ok', 'Hazard Report dikembalikan.');
        }

        if ($data['action'] === 'assign') {
            if (empty($data['assigned_pic_id'])) return back()->with('error', 'Pilih PIC untuk penugasan.');
            $hazard->status = 'Assigned';
            $hazard->assigned_pic_id = $data['assigned_pic_id'];
            $hazard->assigned_at = now();
            $hazard->reviewed_by = auth()->id();
            $hazard->reviewed_at = now();
            $hazard->review_notes = $data['review_notes'];
            $hazard->save();
            SapHazardApproval::create(['hazard_id' => $hazard->id, 'actor_id' => auth()->id(), 'action' => 'Assigned', 'comments' => 'Ditugaskan kepada PIC']);
            $this->log($hazard, 'assigned', 'Ditugaskan untuk tindak lanjut');
            return back()->with('ok', 'Hazard Report berhasil ditugaskan.');
        }

        $hazard->status = 'SHE Review';
        $hazard->reviewed_by = auth()->id();
        $hazard->reviewed_at = now();
        $hazard->review_notes = $data['review_notes'];
        $hazard->save();
        SapHazardApproval::create(['hazard_id' => $hazard->id, 'actor_id' => auth()->id(), 'action' => 'Reviewed', 'comments' => $data['review_notes'] ?? 'Review oleh SHE']);
        $this->log($hazard, 'reviewed', 'Direview oleh SHE');
        return back()->with('ok', 'Review berhasil.');
    }

    public function storeCa(Request $request, SapHazardReport $hazard)
    {
        $data = $request->validate([
            'corrective_action' => ['required', 'string'],
            'pic_id' => ['required', 'exists:users,id'],
            'target_completion_date' => ['nullable', 'date'],
        ]);

        SapHazardCorrectiveAction::create([
            'hazard_id' => $hazard->id,
            'corrective_action' => $data['corrective_action'],
            'pic_id' => $data['pic_id'],
            'target_completion_date' => $data['target_completion_date'] ?? null,
            'status' => 'Assigned',
        ]);

        if ($hazard->status === 'Assigned') {
            $hazard->status = 'In Progress';
            $hazard->save();
        }

        $this->log($hazard, 'ca_added', 'Tindakan perbaikan ditugaskan');
        return back()->with('ok', 'Tindakan perbaikan berhasil ditugaskan.');
    }

    public function submitCa(Request $request, SapHazardCorrectiveAction $ca)
    {
        $data = $request->validate([
            'evidence_notes' => ['nullable', 'string'],
        ]);

        $ca->status = 'Pending Verification';
        $ca->submitted_by = auth()->id();
        $ca->submitted_at = now();
        $ca->evidence_notes = $data['evidence_notes'] ?? null;
        $ca->save();

        $hazard = $ca->hazard;
        $hazard->status = 'Pending Verification';
        $hazard->save();

        $this->log($hazard, 'ca_submitted', 'Tindakan perbaikan diajukan untuk verifikasi');
        return back()->with('ok', 'Tindakan perbaikan diajukan untuk verifikasi SHE.');
    }

    public function verifyCa(Request $request, SapHazardCorrectiveAction $ca)
    {
        $data = $request->validate([
            'action' => ['required', 'in:verify,reject,close'],
            'verification_notes' => ['nullable', 'string'],
        ]);

        if ($data['action'] === 'reject') {
            $ca->status = 'Rejected';
            $ca->verification_notes = $data['verification_notes'];
            $ca->save();
            $ca->hazard->status = 'Reopened';
            $ca->hazard->save();
            $this->log($ca->hazard, 'ca_rejected', 'Tindakan perbaikan ditolak');
            return back()->with('ok', 'Tindakan perbaikan ditolak, dikembalikan untuk perbaikan.');
        }

        $ca->status = 'Verified';
        $ca->verified_by = auth()->id();
        $ca->verified_at = now();
        $ca->verification_notes = $data['verification_notes'];
        $ca->actual_completion_date = now()->toDateString();
        $ca->save();

        if ($data['action'] === 'close') {
            $ca->status = 'Closed';
            $ca->save();
            $hazard = $ca->hazard;
            $pending = $hazard->correctiveActions()->whereNotIn('status', ['Verified', 'Closed'])->count();
            if ($pending === 0) {
                $hazard->status = 'Closed';
                $hazard->closed_at = now();
                $hazard->closed_by = auth()->id();
                $hazard->save();
                SapHazardApproval::create(['hazard_id' => $hazard->id, 'actor_id' => auth()->id(), 'action' => 'Closed', 'comments' => 'Ditutup setelah verifikasi']);
                $this->log($hazard, 'closed', 'Hazard Report ditutup');
            }
            return back()->with('ok', 'Tindakan diverifikasi dan hazard ditutup.');
        }

        $this->log($ca->hazard, 'ca_verified', 'Tindakan perbaikan diverifikasi');
        return back()->with('ok', 'Tindakan perbaikan berhasil diverifikasi.');
    }

    private function log(SapHazardReport $hazard, string $action, string $description, array $meta = [])
    {
        SapHazardActivityLog::create([
            'hazard_id' => $hazard->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'meta' => $meta ?: null,
        ]);
    }

    public function reports(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SapHazardReport::with(['reporter', 'site', 'department', 'assignedPic'])
            ->whereIn('status', ['Submitted', 'SHE Review', 'Assigned', 'In Progress', 'Pending Verification', 'Closed']);

        if ($filters['scope_site_ids'] !== null) $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        if (!empty($filters['site_id'])) $query->where('site_id', $filters['site_id']);
        if (!empty($filters['department_id'])) $query->where('department_id', $filters['department_id']);
        if ($request->filled('risk_level')) $query->where('risk_level', $request->risk_level);

        $hazards = $query->latest()->paginate(\App\Support\Listing::perPage())->withQueryString();
        return view('hse.sap_hazard.reports', compact('hazards', 'filters', 'options'));
    }
}
