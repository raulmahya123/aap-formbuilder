<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\SapPtoActivityLog;
use App\Models\SapPtoAnswer;
use App\Models\SapPtoApproval;
use App\Models\SapPtoChecklistItem;
use App\Models\SapPtoChecklistTemplate;
use App\Models\SapPtoFinding;
use App\Models\SapPtoRecord;
use App\Models\SapPtoSchedule;
use App\Models\Site;
use App\Models\Department;
use App\Models\User;
use App\Services\Hse\HseDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SapPtoController extends Controller
{
    public function __construct(private readonly HseDashboardService $dashboardService)
    {
        $this->middleware(['auth']);
    }

    public function dashboard(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $base = SapPtoRecord::query();
        if ($filters['scope_site_ids'] !== null) {
            $base->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($filters['site_id'])) $base->where('site_id', $filters['site_id']);
        if (!empty($filters['department_id'])) $base->where('department_id', $filters['department_id']);

        $kpis = [
            ['label' => 'Total PTO', 'value' => (clone $base)->count(), 'unit' => 'observasi'],
            ['label' => 'Draft/Submitted', 'value' => (clone $base)->whereIn('status', ['Draft', 'Submitted'])->count(), 'unit' => 'observasi'],
            ['label' => 'Menunggu Review', 'value' => (clone $base)->whereIn('status', ['Submitted', 'Supervisor Review'])->count(), 'unit' => 'observasi'],
            ['label' => 'Disetujui/Approved', 'value' => (clone $base)->whereIn('status', ['Approved', 'Closed'])->count(), 'unit' => 'observasi'],
            ['label' => 'Total Perilaku Berisiko', 'value' => (clone $base)->sum('risky_behaviors_count'), 'unit' => 'temuan'],
            ['label' => 'Rata-rata Safe Rate', 'value' => round((clone $base)->whereNotNull('safe_rate')->avg('safe_rate') ?? 0, 1), 'unit' => '%'],
        ];

        $recent = SapPtoRecord::with(['observer', 'site', 'observedEmployee'])
            ->when($filters['scope_site_ids'] !== null, fn ($q) => $q->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]))
            ->latest()->limit(10)->get();

        $findingsOpen = SapPtoFinding::whereHas('pto', function ($q) use ($filters) {
            if ($filters['scope_site_ids'] !== null) $q->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        })->whereIn('status', ['Open', 'In Progress'])->count();

        return view('hse.sap_pto.dashboard', compact('filters', 'options', 'kpis', 'recent', 'findingsOpen'));
    }

    public function index(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SapPtoRecord::with(['observer', 'site', 'department', 'observedEmployee'])->latest();
        if ($filters['scope_site_ids'] !== null) {
            $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        }
        if (!empty($filters['site_id'])) $query->where('site_id', $filters['site_id']);
        if (!empty($filters['department_id'])) $query->where('department_id', $filters['department_id']);
        if ($request->filled('status')) $query->where('status', $request->status);

        $ptos = $query->paginate(20)->withQueryString();
        return view('hse.sap_pto.index', compact('ptos', 'filters', 'options'));
    }

    public function create(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);
        $users = User::where('is_active', true)->orderBy('name')->get();
        $template = SapPtoChecklistTemplate::where('is_active', true)->first() ?? SapPtoChecklistTemplate::first();
        return view('hse.sap_pto.create', compact('users', 'options', 'filters', 'template'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'work_activity' => ['required', 'string', 'max:500'],
            'related_procedure' => ['nullable', 'string', 'max:1000'],
            'observation_date' => ['required', 'date'],
            'observation_time' => ['nullable', 'date_format:H:i'],
            'duration' => ['nullable', 'string', 'max:50'],
            'work_location' => ['nullable', 'string', 'max:255'],
            'observed_employee_id' => ['nullable', 'exists:users,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
        ]);

        $year = now()->format('Y');
        $count = SapPtoRecord::whereYear('created_at', $year)->count() + 1;
        $ptoNo = sprintf('SAP-PTO-%s-%05d', $year, $count);

        $observer = $request->user();
        $observed = $data['observed_employee_id'] ? User::find($data['observed_employee_id']) : null;
        $supervisor = $data['supervisor_id'] ? User::find($data['supervisor_id']) : null;

        $pto = SapPtoRecord::create([
            'pto_no' => $ptoNo,
            'document_code' => 'SHE-FM-019',
            'site_id' => $data['site_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'work_activity' => $data['work_activity'],
            'related_procedure' => $data['related_procedure'] ?? null,
            'observation_date' => $data['observation_date'],
            'observation_time' => $data['observation_time'] ?? null,
            'duration' => $data['duration'] ?? null,
            'work_location' => $data['work_location'] ?? null,
            'observer_id' => $observer->id,
            'observed_employee_id' => $observed?->id,
            'observer_nik' => $observer->nik,
            'observer_position' => $observer->position,
            'observer_dept' => $observer->department?->name ?? $observer->division,
            'observed_nik' => $observed?->nik,
            'observed_position' => $observed?->position,
            'observed_dept' => $observed?->department?->name ?? $observed?->division,
            'supervisor_id' => $supervisor?->id,
            'supervisor_name' => $supervisor?->name,
            'supervisor_dept' => $supervisor?->department?->name ?? $supervisor?->division,
            'status' => 'Draft',
        ]);

        $this->log($pto, 'created', 'PTO dibuat');
        return redirect()->route('hse.dashboard.sap.pto.edit', $pto->id)->with('ok', 'PTO berhasil dibuat. Silakan isi checklist observasi.');
    }

    public function edit(SapPtoRecord $pto)
    {
        $pto->load(['answers', 'template.items']);
        if (!$pto->template_id) {
            $template = SapPtoChecklistTemplate::where('is_active', true)->with('items')->first();
            if ($template) {
                $pto->template_id = $template->id;
                $pto->save();
                $pto->setRelation('template', $template);
            }
        } else {
            $pto->load('template.items');
        }
        return view('hse.sap_pto.edit', compact('pto'));
    }

    public function saveChecklist(Request $request, SapPtoRecord $pto)
    {
        if (!in_array($pto->status, ['Draft', 'Returned', 'Revised'])) {
            return back()->with('error', 'PTO tidak dapat diedit pada status saat ini.');
        }

        $answers = $request->input('answers', []);
        $remarks = $request->input('remarks', []);
        $apd = $request->input('apd', []);
        $other = $request->input('other_note', []);
        $sub = $request->input('sub_selections', []);

        DB::beginTransaction();
        try {
            $totalChecklist = 0;
            $applicable = 0;
            $safe = 0;
            $unsafe = 0;
            $na = 0;
            $risky = 0;

            foreach ($answers as $itemId => $answerVal) {
                if ($answerVal === null || $answerVal === '') continue;
                $item = SapPtoChecklistItem::find($itemId);
                if (!$item) continue;

                $totalChecklist++;
                if ($answerVal === 'NA') {
                    $na++;
                    continue;
                }
                $applicable++;

                $isSafe = $this->evaluateSafe($answerVal, $item);
                $isRisky = $this->evaluateRisky($answerVal, $item);

                if ($isSafe) $safe++;
                if (!$isSafe) $unsafe++;
                if ($isRisky) $risky++;

                SapPtoAnswer::updateOrCreate(
                    ['pto_id' => $pto->id, 'item_id' => $itemId],
                    [
                        'answer' => $answerVal,
                        'is_safe' => $isSafe,
                        'is_risky' => $isRisky,
                        'apd_used' => $apd[$itemId] ?? null,
                        'sub_selections' => $sub[$itemId] ?? null,
                        'other_note' => $other[$itemId] ?? null,
                        'remarks' => $remarks[$itemId] ?? null,
                        'requires_follow_up' => $isRisky || !$isSafe,
                    ]
                );
            }

            $pto->total_checklist = $totalChecklist;
            $pto->applicable_checklist = $applicable;
            $pto->safe_observations = $safe;
            $pto->unsafe_observations = $unsafe;
            $pto->na_count = $na;
            $pto->risky_behaviors_count = $risky;
            if ($applicable > 0) {
                $pto->safe_rate = round(($safe / $applicable) * 100, 2);
                $pto->unsafe_rate = round(($unsafe / $applicable) * 100, 2);
            }
            $pto->save();

            DB::commit();
            $this->log($pto, 'checklist_saved', 'Checklist disimpan');
            return back()->with('ok', 'Checklist berhasil disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    private function evaluateSafe($answerVal, SapPtoChecklistItem $item)
    {
        if ($item->expected_safe_answer && $item->expected_safe_answer !== '') {
            return $answerVal === $item->expected_safe_answer;
        }
        if ($item->unsafe_answer && $item->unsafe_answer !== '') {
            return $answerVal !== $item->unsafe_answer;
        }
        // Default logic: for most procedure/compliance items YA is safe; for risk-behavior items YA can be unsafe
        // We mark based on item_no/section context via is_risk_indicator
        if ($item->is_risk_indicator) {
            // Items asking if worker does risky action -> YA means risky/unsafe
            if (stripos($item->question, 'membetulkan') !== false && stripos($item->question, 'APD') !== false) {
                return $answerVal === 'YA'; // correcting PPE is safe
            }
            return $answerVal !== 'YA'; // doing rushed/forcing is risky when YA
        }
        return $answerVal === 'YA';
    }

    private function evaluateRisky($answerVal, SapPtoChecklistItem $item)
    {
        if ($item->is_risk_indicator && $answerVal === 'YA') {
            $q = strtolower($item->question);
            if (str_contains($q, 'terburu') || str_contains($q, 'memaksakan') || str_contains($q, 'tiba-tiba') || str_contains($q, 'berbahaya')) {
                return true;
            }
            if (str_contains($q, 'membetulkan') && str_contains($q, 'apd')) {
                return false;
            }
            return true;
        }
        if (!$item->is_risk_indicator && $answerVal === 'TIDAK') {
            return true;
        }
        return false;
    }

    public function updateDetails(Request $request, SapPtoRecord $pto)
    {
        if (!in_array($pto->status, ['Draft', 'Returned', 'Revised'])) {
            return back()->with('error', 'Tidak dapat mengedit pada status saat ini.');
        }

        $data = $request->validate([
            'observation_description' => ['nullable', 'string'],
            'safe_behavior' => ['nullable', 'string'],
            'risky_behavior' => ['nullable', 'string'],
            'unsafe_condition' => ['nullable', 'string'],
            'potential_impact' => ['nullable', 'string'],
            'immediate_action' => ['nullable', 'string'],
            'immediate_action_date' => ['nullable', 'date'],
            'immediate_action_pic_id' => ['nullable', 'exists:users,id'],
            'immediate_action_status' => ['nullable', 'string'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'supervisor_comment' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'follow_up_action' => ['nullable', 'string'],
            'informed_to_observed' => ['nullable', 'boolean'],
        ]);

        $pto->fill($data);
        if (!empty($data['supervisor_id'])) {
            $sup = User::find($data['supervisor_id']);
            if ($sup) {
                $pto->supervisor_id = $sup->id;
                $pto->supervisor_name = $sup->name;
                $pto->supervisor_dept = $sup->department?->name ?? $sup->division;
            }
        }
        $pto->informed_to_observed = $request->boolean('informed_to_observed');
        $pto->save();

        $this->log($pto, 'details_updated', 'Detail hasil pengamatan diperbarui');
        return back()->with('ok', 'Detail hasil pengamatan berhasil disimpan.');
    }

    public function submit(SapPtoRecord $pto)
    {
        if (!in_array($pto->status, ['Draft', 'Returned', 'Revised'])) {
            return back()->with('error', 'Tidak dapat diajukan pada status saat ini.');
        }
        $answersCount = $pto->answers()->count();
        if ($answersCount === 0) {
            return back()->with('error', 'Checklist belum diisi.');
        }

        DB::beginTransaction();
        try {
            $pto->status = 'Submitted';
            $pto->submitted_at = now();
            $pto->save();

            SapPtoApproval::create([
                'pto_id' => $pto->id,
                'actor_id' => auth()->id(),
                'action' => 'Submitted',
                'comments' => 'Diajukan untuk review atasan',
            ]);

            $this->createFindings($pto);
            $this->log($pto, 'submitted', 'PTO diajukan untuk supervisor review');
            DB::commit();
            return redirect()->route('hse.dashboard.sap.pto.show', $pto->id)->with('ok', 'PTO berhasil diajukan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan: ' . $e->getMessage());
        }
    }

    public function show(SapPtoRecord $pto)
    {
        $pto->load(['observer', 'observedEmployee', 'site', 'department', 'supervisor', 'answers.item', 'findings', 'approvals.actor', 'logs.user']);
        return view('hse.sap_pto.show', compact('pto'));
    }

    public function review(Request $request, SapPtoRecord $pto)
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,return'],
            'review_notes' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'follow_up_action' => ['nullable', 'string'],
        ]);

        if (!in_array($pto->status, ['Submitted', 'Supervisor Review', 'Resubmitted'])) {
            return back()->with('error', 'Tidak dapat direview pada status saat ini.');
        }

        if ($data['action'] === 'return') {
            if (empty($data['review_notes'])) {
                return back()->with('error', 'Catatan wajib diisi untuk pengembalian.');
            }
            $pto->status = 'Returned';
            $pto->review_notes = $data['review_notes'];
            $pto->reviewed_by = auth()->id();
            $pto->reviewed_at = now();
            $pto->save();

            SapPtoApproval::create(['pto_id' => $pto->id, 'actor_id' => auth()->id(), 'action' => 'Returned', 'comments' => $data['review_notes']]);
            $this->log($pto, 'returned', 'Dikembalikan untuk revisi');
            return back()->with('ok', 'PTO dikembalikan.');
        }

        $pto->status = 'Approved';
        $pto->review_notes = $data['review_notes'] ?? $pto->review_notes;
        $pto->recommendation = $data['recommendation'] ?? $pto->recommendation;
        $pto->follow_up_action = $data['follow_up_action'] ?? $pto->follow_up_action;
        $pto->reviewed_by = auth()->id();
        $pto->reviewed_at = now();
        $pto->approved_by = auth()->id();
        $pto->approved_at = now();
        $pto->supervisor_review_date = now()->toDateString();
        $pto->save();

        SapPtoApproval::create(['pto_id' => $pto->id, 'actor_id' => auth()->id(), 'action' => 'Approved', 'comments' => $data['review_notes'] ?? 'Disetujui atasan']);
        $this->log($pto, 'approved', 'PTO disetujui');
        return back()->with('ok', 'PTO berhasil disetujui.');
    }

    public function findings(SapPtoRecord $pto)
    {
        $pto->load(['findings.pic']);
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('hse.sap_pto.findings.index', compact('pto', 'users'));
    }

    public function storeFinding(Request $request, SapPtoRecord $pto)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'risk_class' => ['required', 'in:Low,Medium,High,Critical'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $year = now()->format('Y');
        $count = SapPtoFinding::whereYear('created_at', $year)->count() + 1;
        $findingNo = sprintf('FIND-PTO-%s-%05d', $year, $count);

        SapPtoFinding::create([
            'finding_no' => $findingNo,
            'pto_id' => $pto->id,
            'site_id' => $pto->site_id,
            'department_id' => $pto->department_id,
            'title' => $data['title'],
            'description' => $data['description'],
            'risk_class' => $data['risk_class'],
            'pic_id' => $data['pic_id'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => 'Open',
        ]);

        $pto->findings_count = $pto->findings()->count();
        $pto->save();

        $this->log($pto, 'finding_created', 'Temuan PTO dibuat');
        return back()->with('ok', 'Temuan berhasil ditambahkan.');
    }

    public function updateFinding(Request $request, SapPtoFinding $finding)
    {
        $data = $request->validate([
            'status' => ['required', 'in:Open,In Progress,For Verification,Verified,Closed,Rejected'],
            'closure_notes' => ['nullable', 'string'],
        ]);

        $finding->status = $data['status'];
        if (!empty($data['closure_notes'])) $finding->closure_notes = $data['closure_notes'];
        if (in_array($data['status'], ['Verified', 'Closed'])) {
            $finding->verified_by = auth()->id();
            $finding->verified_at = now();
            if ($data['status'] === 'Closed') $finding->closed_at = now();
        }
        $finding->save();

        $this->log($finding->pto, 'finding_updated', 'Temuan ' . $finding->finding_no . ' - ' . $data['status']);
        return back()->with('ok', 'Status temuan diperbarui.');
    }

    private function createFindings(SapPtoRecord $pto)
    {
        $riskyAnswers = $pto->answers()->where('is_risky', true)->with('item')->get();
        foreach ($riskyAnswers as $ans) {
            if (SapPtoFinding::where('answer_id', $ans->id)->exists()) continue;
            $year = now()->format('Y');
            $count = SapPtoFinding::whereYear('created_at', $year)->count() + 1;
            $findingNo = sprintf('FIND-PTO-%s-%05d', $year, $count);

            SapPtoFinding::create([
                'finding_no' => $findingNo,
                'pto_id' => $pto->id,
                'answer_id' => $ans->id,
                'item_id' => $ans->item_id,
                'site_id' => $pto->site_id,
                'department_id' => $pto->department_id,
                'title' => 'Perilaku/Kondisi Berisiko - Observasi PTO',
                'description' => 'Ditemukan: ' . Str::limit(strip_tags($ans->item->question ?? ''), 150) . '. Catatan: ' . ($ans->remarks ?? '-'),
                'risk_class' => 'Medium',
                'pic_id' => null,
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'Open',
            ]);
        }
        $pto->findings_count = $pto->findings()->count();
        $pto->save();
    }

    private function log(SapPtoRecord $pto, string $action, string $description, array $meta = [])
    {
        SapPtoActivityLog::create([
            'pto_id' => $pto->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'meta' => $meta ?: null,
        ]);
    }

    public function scheduleIndex(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $schedules = SapPtoSchedule::with(['observer', 'site', 'department'])->latest()->paginate(20);
        return view('hse.sap_pto.schedule.index', compact('schedules', 'filters'));
    }

    public function scheduleCreate(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('hse.sap_pto.schedule.create', compact('options', 'users', 'filters'));
    }

    public function scheduleStore(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['nullable', 'exists:sites,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'observer_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'period' => ['nullable', 'string'],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'integer'],
        ]);
        $data['created_by'] = auth()->id();
        $data['status'] = 'Planned';
        SapPtoSchedule::create($data);
        return redirect()->route('hse.dashboard.sap.pto.schedule.index')->with('ok', 'Jadwal PTO berhasil dibuat.');
    }

    public function reports(Request $request)
    {
        $filters = $this->dashboardService->filters($request);
        $options = $this->dashboardService->options($filters);

        $query = SapPtoRecord::with(['observer', 'site', 'department'])
            ->whereIn('status', ['Submitted', 'Supervisor Review', 'Approved', 'Closed']);

        if ($filters['scope_site_ids'] !== null) $query->whereIn('site_id', $filters['scope_site_ids'] ?: [-1]);
        if (!empty($filters['site_id'])) $query->where('site_id', $filters['site_id']);
        if (!empty($filters['department_id'])) $query->where('department_id', $filters['department_id']);

        $ptos = $query->latest()->paginate(30)->withQueryString();
        return view('hse.sap_pto.reports', compact('ptos', 'filters', 'options'));
    }
}
