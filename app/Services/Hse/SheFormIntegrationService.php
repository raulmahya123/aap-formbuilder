<?php

namespace App\Services\Hse;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\HseIncident;
use App\Models\HseInspection;
use App\Models\HseFinding;
use App\Models\HseCorrectiveAction;
use App\Models\HseRiskAssessment;
use App\Models\HseTrainingCourse;
use App\Models\HseTrainingRecord;
use App\Models\HseEnvironmentalRecord;
use App\Models\HipoReport;
use App\Models\SheNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SheFormIntegrationService
{
    /**
     * Map and process an approved/submitted FormEntry into relevant SHE domain records.
     * Uses explicit `she_integration_type` configuration first, with strict fallback.
     */
    public function syncEntryToShe(FormEntry $entry): array
    {
        $form = $entry->form;
        if (!$form) {
            return ['status' => 'ignored', 'reason' => 'No form associated'];
        }

        $integrationType = $form->she_integration_type;
        $title = strtolower($form->title ?? '');

        // If explicitly disabled
        if ($integrationType === 'none') {
            return ['status' => 'ignored', 'reason' => 'Form integration explicitly set to none'];
        }

        // Determine effective type
        $type = $integrationType;
        if (empty($type)) {
            $type = match (true) {
                preg_match('/\b(fatality|lti|kecelakaan|insiden|injury|property damage)\b/i', $title) === 1 => 'incident',
                preg_match('/\b(pto|observasi tugas|task observation)\b/i', $title) === 1 => 'pto',
                preg_match('/\b(inspeksi|checklist|inspection|audit)\b/i', $title) === 1 => 'inspection',
                preg_match('/\b(hazard|bahaya|hipo|safety observation)\b/i', $title) === 1 => 'hazard',
                preg_match('/\b(risk|risiko|ibpr|hiradc)\b/i', $title) === 1 => 'risk',
                preg_match('/\b(pelatihan|training|attendance|safety meeting)\b/i', $title) === 1 => 'training',
                preg_match('/\b(lingkungan|limbah|waste|environmental)\b/i', $title) === 1 => 'environmental',
                default => null,
            };
        }

        if (!$type) {
            return ['status' => 'ignored', 'reason' => 'No SHE mapping configured for this form'];
        }

        $data = $entry->data ?? [];
        $siteId = $form->site_id;
        $deptId = $form->department_id;
        $userId = $entry->user_id;

        $created = [];

        DB::beginTransaction();
        try {
            switch ($type) {
                case 'incident':
                    $created = array_merge($created, $this->handleIncident($entry, $form, $data, $siteId, $deptId, $userId));
                    break;

                case 'pto':
                    $created = array_merge($created, $this->handlePto($entry, $form, $data, $siteId, $deptId, $userId));
                    break;

                case 'inspection':
                    $created = array_merge($created, $this->handleInspection($entry, $form, $data, $siteId, $deptId, $userId));
                    break;

                case 'hazard':
                    $created = array_merge($created, $this->handleHazard($entry, $form, $data, $siteId, $deptId, $userId));
                    break;

                case 'risk':
                    $created = array_merge($created, $this->handleRisk($entry, $form, $data, $siteId, $deptId, $userId));
                    break;

                case 'training':
                    $created = array_merge($created, $this->handleTraining($entry, $form, $data, $siteId, $deptId, $userId));
                    break;

                case 'environmental':
                    $created = array_merge($created, $this->handleEnvironmental($entry, $form, $data, $siteId, $deptId, $userId));
                    break;
            }

            DB::commit();
            return ['status' => 'success', 'integration_type' => $type, 'created' => $created];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SheFormIntegrationService sync error: ' . $e->getMessage(), [
                'entry_id' => $entry->id,
                'form_id' => $form->id,
                'type' => $type,
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function handleIncident(FormEntry $entry, Form $form, array $data, ?int $siteId, ?int $deptId, ?int $userId): array
    {
        $category = $data['kategori'] ?? $data['category'] ?? 'Incident';
        $incType = $data['tipe'] ?? $data['type'] ?? 'Other';
        $severity = $data['severity'] ?? $data['keparahan'] ?? 'Medium';
        $lostDays = (int) ($data['lost_days'] ?? $data['hari_hilang'] ?? 0);

        if (stripos($form->title, 'fatality') !== false || stripos($incType, 'fatality') !== false) {
            $incType = 'Fatality';
            $severity = 'Critical';
            $lostDays = 6000;
        } elseif (stripos($form->title, 'lti') !== false || stripos($incType, 'lti') !== false) {
            $incType = 'Lost Time Injury';
            $severity = 'High';
            $lostDays = max(1, $lostDays);
        }

        $recordable = in_array($incType, ['Fatality', 'Lost Time Injury', 'Medical Treatment Case', 'Restricted Work Case']);

        $incident = HseIncident::firstOrCreate(
            ['incident_no' => 'INC-ENTRY-' . $entry->id],
            [
                'site_id' => $siteId,
                'department_id' => $deptId,
                'reported_by' => $userId,
                'occurred_at' => $entry->created_at ?? now(),
                'reported_at' => $entry->created_at ?? now(),
                'location' => $data['lokasi'] ?? $data['location'] ?? 'Area Operasional',
                'category' => $category,
                'type' => $incType,
                'severity' => in_array($severity, ['Low', 'Medium', 'High', 'Critical']) ? $severity : 'Medium',
                'description' => 'Disubmit via form: ' . ($form->title ?? 'Form') . '. ' . ($data['keterangan'] ?? $data['deskripsi'] ?? ''),
                'immediate_action' => $data['tindakan_langsung'] ?? $data['immediate_action'] ?? null,
                'lost_time_days' => $lostDays,
                'recordable' => $recordable,
                'status' => 'Reported',
                'investigation_status' => 'Pending',
            ]
        );

        $out = ['incident' => $incident->id];

        if (in_array($incident->severity, ['High', 'Critical']) || $recordable) {
            $pica = $this->createPica(
                sourceModule: 'Investigation',
                sourceModel: $incident,
                siteId: $siteId,
                deptId: $deptId,
                title: 'Investigasi Insiden: ' . ($form->title ?? 'Incident'),
                description: $incident->description,
                priority: in_array($incident->severity, ['High', 'Critical']) ? 'High' : 'Medium',
                assignedTo: $userId
            );
            $out['pica'] = $pica->id;
        }

        return $out;
    }

    private function handlePto(FormEntry $entry, Form $form, array $data, ?int $siteId, ?int $deptId, ?int $userId): array
    {
        $insp = HseInspection::firstOrCreate(
            ['inspection_no' => 'PTO-ENTRY-' . $entry->id],
            [
                'site_id' => $siteId,
                'department_id' => $deptId,
                'inspector_id' => $userId,
                'title' => 'PTO: ' . ($form->title ?? 'Observasi Tugas Terencana'),
                'type' => 'PTO',
                'scheduled_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                'completed_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                'score' => isset($data['score']) ? (float)$data['score'] : 100.0,
                'max_score' => 100.0,
                'status' => 'Completed',
                'notes' => 'Submission PTO #' . $entry->id,
            ]
        );

        $out = ['pto' => $insp->id];

        if (!empty($data['penyimpangan']) || !empty($data['deviation'])) {
            $devDesc = $data['penyimpangan'] ?? $data['deviation'];
            $pica = $this->createPica(
                sourceModule: 'PTO',
                sourceModel: $insp,
                siteId: $siteId,
                deptId: $deptId,
                title: 'Penyimpangan PTO #' . $insp->id,
                description: $devDesc,
                priority: 'Medium',
                assignedTo: $userId
            );
            $out['pica'] = $pica->id;
        }

        return $out;
    }

    private function handleInspection(FormEntry $entry, Form $form, array $data, ?int $siteId, ?int $deptId, ?int $userId): array
    {
        $insp = HseInspection::firstOrCreate(
            ['inspection_no' => 'INSP-ENTRY-' . $entry->id],
            [
                'site_id' => $siteId,
                'department_id' => $deptId,
                'inspector_id' => $userId,
                'title' => $form->title ?? 'Inspeksi SHE',
                'type' => $data['type'] ?? 'Routine',
                'scheduled_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                'completed_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                'score' => isset($data['score']) ? (float)$data['score'] : 100.0,
                'max_score' => 100.0,
                'status' => 'Completed',
                'notes' => 'Otomatis dari Form Submission #' . $entry->id,
            ]
        );

        $out = ['inspection' => $insp->id];

        if (!empty($data['temuan']) || !empty($data['finding']) || !empty($data['catatan_temuan'])) {
            $findingDesc = $data['temuan'] ?? $data['finding'] ?? $data['catatan_temuan'];
            $pica = $this->createPica(
                sourceModule: 'Inspection',
                sourceModel: $insp,
                siteId: $siteId,
                deptId: $deptId,
                title: 'Tindak Lanjut Temuan Inspeksi #' . $insp->id,
                description: $findingDesc,
                priority: 'Medium',
                assignedTo: $userId
            );
            $out['pica'] = $pica->id;
        }

        return $out;
    }

    private function handleHazard(FormEntry $entry, Form $form, array $data, ?int $siteId, ?int $deptId, ?int $userId): array
    {
        $hipo = HipoReport::firstOrCreate(
            ['source' => 'ENTRY-' . $entry->id],
            [
                'user_id' => $userId,
                'site_id' => $siteId,
                'jobsite' => $form->site?->name ?? 'Site',
                'reporter_name' => $entry->user?->name ?? 'Reporter',
                'report_time' => $entry->created_at ?? now(),
                'jenis_hipo' => 'Hazard Report',
                'description' => $data['deskripsi'] ?? $data['bahaya'] ?? 'Hazard report submission',
                'risk_level' => $data['risk_level'] ?? 'Medium',
                'shift' => $data['shift'] ?? 'Shift 1',
                'category' => $data['category'] ?? 'High Potential Hazard',
                'potential_consequence' => $data['potential_consequence'] ?? 'Injury Non LTI',
                'potential_risk_level' => $data['risk_level'] ?? 'Medium',
                'kta' => $data['kta'] ?? 'KTA-01',
                'tta' => $data['tta'] ?? 'TTA-01',
                'status' => 'Open',
            ]
        );

        $out = ['hazard' => $hipo->id];

        if (($data['need_followup'] ?? false) || !empty($data['tindakan_perbaikan'])) {
            $pica = $this->createPica(
                sourceModule: 'Hazard Report',
                sourceModel: $hipo,
                siteId: $siteId,
                deptId: $deptId,
                title: 'Tindak Lanjut Bahaya #' . $hipo->id,
                description: $hipo->description,
                priority: 'High',
                assignedTo: $userId
            );
            $out['pica'] = $pica->id;
        }

        return $out;
    }

    private function handleRisk(FormEntry $entry, Form $form, array $data, ?int $siteId, ?int $deptId, ?int $userId): array
    {
        $activity = $data['aktivitas'] ?? $data['activity'] ?? ($form->title ?? 'Risk Assessment Entry #' . $entry->id);
        $hazard = $data['bahaya'] ?? $data['hazard'] ?? 'Identified Operational Hazard';
        $l = max(1, min(5, (int) ($data['likelihood'] ?? $data['peluang'] ?? 3)));
        $c = max(1, min(5, (int) ($data['consequence'] ?? $data['konsekuensi'] ?? 3)));
        $inherentScore = $l * $c;
        $level = HseRiskAssessment::levelForScore($inherentScore);

        $resL = max(1, min(5, (int) ($data['residual_likelihood'] ?? $l)));
        $resC = max(1, min(5, (int) ($data['residual_consequence'] ?? $c)));
        $resScore = $resL * $resC;

        $risk = HseRiskAssessment::firstOrCreate(
            [
                'activity' => $activity,
                'hazard' => $hazard,
                'site_id' => $siteId,
            ],
            [
                'department_id' => $deptId,
                'owner_id' => $userId,
                'existing_controls' => $data['existing_controls'] ?? $data['kontrol_eksisting'] ?? null,
                'category' => $data['kategori'] ?? $data['category'] ?? 'Operational',
                'likelihood' => $l,
                'consequence' => $c,
                'inherent_score' => $inherentScore,
                'residual_likelihood' => $resL,
                'residual_consequence' => $resC,
                'residual_score' => $resScore,
                'risk_level' => $level,
                'treatment' => $data['treatment'] ?? $data['pengendalian'] ?? null,
                'treatment_due_date' => !empty($data['due_date']) ? Carbon::parse($data['due_date'])->toDateString() : null,
                'treatment_status' => !empty($data['treatment']) ? 'In Progress' : 'Not Required',
                'status' => 'Active',
            ]
        );

        $out = ['risk_assessment' => $risk->id];

        if (in_array($level, ['High', 'Extreme'])) {
            $pica = $this->createPica(
                sourceModule: 'Risk Assessment',
                sourceModel: $risk,
                siteId: $siteId,
                deptId: $deptId,
                title: 'Mitigasi Risiko ' . $level . ': ' . \Illuminate\Support\Str::limit($activity, 50),
                description: "Bahaya: {$hazard}. Kontrol mitigasi: " . ($risk->treatment ?? 'Perlu implementasi kontrol kritis.'),
                priority: $level === 'Extreme' ? 'High' : 'Medium',
                assignedTo: $userId,
                dueDate: $risk->treatment_due_date?->toDateString()
            );
            $out['pica'] = $pica->id;
        }

        return $out;
    }

    private function handleTraining(FormEntry $entry, Form $form, array $data, ?int $siteId, ?int $deptId, ?int $userId): array
    {
        $courseTitle = $data['course_title'] ?? $data['judul_pelatihan'] ?? $form->title;
        $course = HseTrainingCourse::firstOrCreate(
            ['title' => $courseTitle],
            [
                'category' => $data['category'] ?? 'General Safety',
                'validity_months' => (int) ($data['validity_months'] ?? 12),
                'is_mandatory' => (bool) ($data['is_mandatory'] ?? true),
            ]
        );

        $record = HseTrainingRecord::firstOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $userId,
                'site_id' => $siteId,
                'scheduled_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
            ],
            [
                'completed_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                'score' => isset($data['score']) ? (float)$data['score'] : 100.0,
                'status' => 'Completed',
            ]
        );

        return ['training_record' => $record->id];
    }

    private function handleEnvironmental(FormEntry $entry, Form $form, array $data, ?int $siteId, ?int $deptId, ?int $userId): array
    {
        $metric = $data['metric'] ?? $data['jenis_pemantauan'] ?? 'Waste Generated';
        $value = (float) ($data['value'] ?? $data['jumlah'] ?? 0);
        $unit = $data['unit'] ?? $data['satuan'] ?? 'Ton';

        $record = HseEnvironmentalRecord::firstOrCreate(
            [
                'site_id' => $siteId,
                'recorded_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                'metric' => $metric,
                'value' => $value,
            ],
            [
                'department_id' => $deptId,
                'recorded_by' => $userId,
                'unit' => $unit,
                'notes' => 'Submission form #' . $entry->id . ': ' . ($data['keterangan'] ?? ''),
            ]
        );

        return ['environmental_record' => $record->id];
    }

    /**
     * Helper to create a single PICA action without duplicates
     */
    public function createPica(
        string $sourceModule,
        Model $sourceModel,
        ?int $siteId,
        ?int $deptId,
        string $title,
        ?string $description,
        string $priority = 'Medium',
        ?int $assignedTo = null,
        ?string $dueDate = null
    ): HseCorrectiveAction {
        $actionNo = 'PICA-' . strtoupper(substr($sourceModule, 0, 3)) . '-' . $sourceModel->id;

        $pica = HseCorrectiveAction::firstOrCreate(
            [
                'action_no' => $actionNo,
            ],
            [
                'source_type' => get_class($sourceModel),
                'source_id' => $sourceModel->id,
                'source_module' => 'PICA ' . $sourceModule,
                'site_id' => $siteId,
                'department_id' => $deptId,
                'assigned_to' => $assignedTo,
                'title' => $title,
                'description' => $description,
                'priority' => $priority,
                'due_date' => $dueDate ?: now()->addDays(14)->toDateString(),
                'status' => 'Open',
            ]
        );

        if ($assignedTo) {
            SheNotification::notify(
                userId: $assignedTo,
                title: 'Tugas PICA Baru: ' . $pica->action_no,
                message: "Anda ditugaskan pada corrective action: {$title}. Harap tindak lanjuti sebelum due date.",
                type: 'pica_assigned',
                priority: strtolower($priority) === 'high' ? 'high' : 'medium',
                link: route('hse.dashboard.pica.detail', $pica->id)
            );
        }

        return $pica;
    }
}
