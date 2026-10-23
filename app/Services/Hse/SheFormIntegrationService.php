<?php

namespace App\Services\Hse;

use App\Models\Form;
use App\Models\FormEntry;
use App\Models\HseIncident;
use App\Models\HseInspection;
use App\Models\HseFinding;
use App\Models\HseCorrectiveAction;
use App\Models\HseRiskAssessment;
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
     */
    public function syncEntryToShe(FormEntry $entry): array
    {
        $form = $entry->form;
        if (!$form) {
            return ['status' => 'ignored', 'reason' => 'No form associated'];
        }

        $title = strtolower($form->title ?? '');
        $data = $entry->data ?? [];
        $siteId = $form->site_id;
        $deptId = $form->department_id;
        $userId = $entry->user_id;

        $created = [];

        DB::beginTransaction();
        try {
            // 1. FATALITY / INCIDENT
            if (str_contains($title, 'kecelakaan') || str_contains($title, 'fatality') || str_contains($title, 'insiden') || str_contains($title, 'injury')) {
                $type = 'Other';
                $category = 'Incident';
                $severity = 'Medium';
                $lostDays = 0;

                if (str_contains($title, 'fatality')) {
                    $type = 'Fatality';
                    $severity = 'Critical';
                    $lostDays = 6000;
                } elseif (str_contains($title, 'lti')) {
                    $type = 'Lost Time Injury';
                    $severity = 'High';
                    $lostDays = (int) ($data['lost_days'] ?? $data['hari_hilang'] ?? 1);
                } elseif (str_contains($title, 'property damage') || str_contains($title, 'kerusakan')) {
                    $type = 'Property Damage';
                    $severity = 'Medium';
                }

                $incident = HseIncident::firstOrCreate(
                    [
                        'incident_no' => 'INC-ENTRY-' . $entry->id,
                    ],
                    [
                        'site_id' => $siteId,
                        'department_id' => $deptId,
                        'reported_by' => $userId,
                        'occurred_at' => $entry->created_at ?? now(),
                        'reported_at' => $entry->created_at ?? now(),
                        'category' => $category,
                        'type' => $type,
                        'severity' => $severity,
                        'description' => 'Disubmit via form: ' . ($form->title ?? 'Form') . '. ' . ($data['keterangan'] ?? $data['deskripsi'] ?? ''),
                        'lost_time_days' => $lostDays,
                        'recordable' => in_array($type, ['Fatality', 'Lost Time Injury', 'Medical Treatment Case']),
                        'status' => 'Open',
                    ]
                );
                $created['incident'] = $incident->id;

                // Create PICA Investigation if high or critical
                if (in_array($severity, ['High', 'Critical'])) {
                    $this->createPica(
                        sourceModule: 'Investigation',
                        sourceModel: $incident,
                        siteId: $siteId,
                        deptId: $deptId,
                        title: 'Investigasi Insiden: ' . ($form->title ?? 'Incident'),
                        description: $incident->description,
                        priority: 'High',
                        assignedTo: $userId
                    );
                }
            }

            // 2. INSPEKSI
            if (str_contains($title, 'inspeksi') || str_contains($title, 'checklist') || str_contains($title, 'inspection')) {
                $insp = HseInspection::firstOrCreate(
                    [
                        'inspection_no' => 'INSP-ENTRY-' . $entry->id,
                    ],
                    [
                        'site_id' => $siteId,
                        'department_id' => $deptId,
                        'inspector_id' => $userId,
                        'title' => $form->title ?? 'Inspeksi',
                        'type' => 'Routine',
                        'scheduled_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                        'completed_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                        'score' => isset($data['score']) ? (float)$data['score'] : 100.0,
                        'status' => 'Completed',
                        'notes' => 'Otomatis dari Form Submission #' . $entry->id,
                    ]
                );
                $created['inspection'] = $insp->id;

                // Check for findings in data
                if (!empty($data['temuan']) || !empty($data['finding']) || !empty($data['catatan_temuan'])) {
                    $findingDesc = $data['temuan'] ?? $data['finding'] ?? $data['catatan_temuan'];
                    $finding = HseFinding::firstOrCreate(
                        [
                            'finding_no' => 'FIND-ENTRY-' . $entry->id,
                        ],
                        [
                            'findingable_type' => HseInspection::class,
                            'findingable_id' => $insp->id,
                            'site_id' => $siteId,
                            'department_id' => $deptId,
                            'assigned_to' => $userId,
                            'title' => 'Temuan: ' . substr($findingDesc, 0, 50),
                            'description' => $findingDesc,
                            'severity' => 'Medium',
                            'status' => 'Open',
                            'due_date' => now()->addDays(7)->toDateString(),
                        ]
                    );
                    $created['finding'] = $finding->id;

                    // Automatically generate PICA Inspection
                    $this->createPica(
                        sourceModule: 'Inspection',
                        sourceModel: $finding,
                        siteId: $siteId,
                        deptId: $deptId,
                        title: 'Tindak Lanjut Temuan Inspeksi #' . $insp->id,
                        description: $findingDesc,
                        priority: 'Medium',
                        assignedTo: $userId
                    );
                }
            }

            // 3. PTO (Planned Task Observation)
            if (str_contains($title, 'pto') || str_contains($title, 'task observation') || str_contains($title, 'observasi tugas')) {
                $insp = HseInspection::firstOrCreate(
                    [
                        'inspection_no' => 'PTO-ENTRY-' . $entry->id,
                    ],
                    [
                        'site_id' => $siteId,
                        'department_id' => $deptId,
                        'inspector_id' => $userId,
                        'title' => 'PTO: ' . ($form->title ?? 'Observasi Tugas'),
                        'type' => 'PTO',
                        'scheduled_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                        'completed_date' => $entry->created_at?->toDateString() ?? now()->toDateString(),
                        'score' => 100.0,
                        'status' => 'Completed',
                        'notes' => 'Submission PTO #' . $entry->id,
                    ]
                );
                $created['pto'] = $insp->id;

                if (!empty($data['penyimpangan']) || !empty($data['deviation'])) {
                    $devDesc = $data['penyimpangan'] ?? $data['deviation'];
                    $this->createPica(
                        sourceModule: 'PTO',
                        sourceModel: $insp,
                        siteId: $siteId,
                        deptId: $deptId,
                        title: 'Penyimpangan PTO #' . $insp->id,
                        description: $devDesc,
                        priority: 'Medium',
                        assignedTo: $userId
                    );
                }
            }

            // 4. HAZARD REPORT
            if (str_contains($title, 'hazard') || str_contains($title, 'bahaya')) {
                $hipo = HipoReport::firstOrCreate(
                    [
                        'source' => 'ENTRY-' . $entry->id,
                    ],
                    [
                        'user_id' => $userId,
                        'site_id' => $siteId,
                        'jobsite' => $form->site?->name ?? 'Site',
                        'reporter_name' => $entry->user?->name ?? 'Reporter',
                        'report_time' => $entry->created_at ?? now(),
                        'jenis_hipo' => 'Hazard Report',
                        'description' => $data['deskripsi'] ?? $data['bahaya'] ?? 'Hazard report submission',
                        'risk_level' => $data['risk_level'] ?? 'Medium',
                        'status' => 'Open',
                    ]
                );
                $created['hazard'] = $hipo->id;

                if (($data['need_followup'] ?? false) || !empty($data['tindakan_perbaikan'])) {
                    $this->createPica(
                        sourceModule: 'Hazard Report',
                        sourceModel: $hipo,
                        siteId: $siteId,
                        deptId: $deptId,
                        title: 'Tindak Lanjut Bahaya #' . $hipo->id,
                        description: $hipo->description,
                        priority: 'High',
                        assignedTo: $userId
                    );
                }

                // Sync to Risk Register without duplicate
                $riskLevel = $data['risk_level'] ?? 'Medium';
                $scoreMap = ['Low' => 2, 'Medium' => 6, 'High' => 12, 'Extreme' => 20];
                $score = $scoreMap[$riskLevel] ?? 6;
                $risk = HseRiskAssessment::firstOrCreate(
                    [
                        'activity' => 'Hazard: ' . ($hipo->description ? \Illuminate\Support\Str::limit($hipo->description, 80) : 'Hazard #' . $hipo->id),
                        'hazard' => $hipo->category ?? 'Hazard Report Finding',
                        'site_id' => $siteId,
                    ],
                    [
                        'department_id' => $deptId,
                        'owner_id' => $userId,
                        'existing_controls' => $hipo->control_engineering ?? $hipo->control_administrative ?? 'Existing standard control',
                        'category' => $hipo->category ?? 'General',
                        'likelihood' => max(1, min(5, (int) round($score / 4))),
                        'consequence' => max(1, min(5, (int) round($score / 3))),
                        'inherent_score' => $score,
                        'residual_likelihood' => max(1, min(5, (int) round($score / 4))),
                        'residual_consequence' => max(1, min(5, (int) round($score / 3))),
                        'residual_score' => $score,
                        'risk_level' => $riskLevel,
                        'treatment' => $hipo->control_engineering ?? 'Control implementation',
                        'treatment_status' => 'In Progress',
                        'status' => 'Active',
                    ]
                );
                $created['risk_assessment'] = $risk->id;
            }

            // 5. RISK ASSESSMENT SUBMISSION
            if (str_contains($title, 'risk assessment') || str_contains($title, 'penilaian risiko') || str_contains($title, 'ibpr') || str_contains($title, 'hiradc')) {
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
                        'treatment_status' => 'In Progress',
                        'status' => 'Active',
                    ]
                );
                $created['risk_assessment'] = $risk->id;

                if (in_array($level, ['High', 'Extreme'])) {
                    $this->createPica(
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
                }
            }

            DB::commit();
            return ['status' => 'success', 'created' => $created];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('SheFormIntegrationService sync error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
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
                link: route('hse.dashboard.actions')
            );
        }

        return $pica;
    }
}
