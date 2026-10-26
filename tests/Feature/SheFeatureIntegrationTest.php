<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Site;
use App\Models\Department;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\SheManhour;
use App\Models\SheKpiTarget;
use App\Models\HseKpiTarget;
use App\Models\SheSapTarget;
use App\Models\HseIncident;
use App\Models\HseInspection;
use App\Models\HipoReport;
use App\Models\HseRiskAssessment;
use App\Models\HseCorrectiveAction;
use App\Models\SheDocument;
use App\Models\HseTrainingCourse;
use App\Models\HseTrainingRecord;
use App\Models\HseEnvironmentalRecord;
use App\Services\Hse\SheFormIntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SheFeatureIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Site $site;
    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create([
            'name' => 'Operasional Tambang',
            'code' => 'OPS',
        ]);

        $this->site = Site::create([
            'code' => 'SITE-A',
            'name' => 'Site Alpha',
        ]);

        $this->admin = User::factory()->create([
            'name' => 'SHE Manager',
            'email' => 'she.manager@mandala.com',
            'role' => 'super_admin',
            'is_active' => true,
            'department_id' => $this->department->id,
        ]);
        $this->admin->sites()->attach($this->site->id);
    }

    public function test_hse_dashboard_safety_renders_kpis_and_drilldowns(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('hse.dashboard.safety'));
        $response->assertStatus(200);
        $response->assertSee('Safety Performance');
        $response->assertSee('Total Fatality');
        $response->assertSee('Total Lost Time Injury (LTI)');
        $response->assertSee('LTIFR');
        $response->assertSee('LTISR');
        $response->assertSee('Total Manhours');
    }

    public function test_manhours_submit_and_validate(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('hse.dashboard.manhours.store'), [
            'date' => now()->toDateString(),
            'site_id' => $this->site->id,
            'department_id' => $this->department->id,
            'employee_count' => 120,
            'manhours' => 960,
            'lost_hours' => 0,
            'notes' => 'Jam kerja operasional shift 1 & 2',
        ]);
        $response->assertRedirect();

        $manhour = SheManhour::latest()->first();
        $this->assertNotNull($manhour);
        $this->assertEquals(960, $manhour->manhours);
        $this->assertEquals('Submitted', $manhour->status);

        $valResponse = $this->post(route('hse.dashboard.manhours.validate', $manhour->id), [
            'action' => 'validate',
        ]);
        $valResponse->assertRedirect();

        $manhour->refresh();
        $this->assertEquals('Validated', $manhour->status);
        $this->assertNotNull($manhour->validated_at);
    }

    public function test_kpi_target_synchronization(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('hse.dashboard.targets.store'), [
            'kpi_key' => 'ltifr',
            'year' => 2026,
            'site_id' => $this->site->id,
            'target_value' => 0.5,
            'unit' => 'rate',
            'direction' => 'lower',
        ]);
        $response->assertRedirect();

        $sheTarget = SheKpiTarget::where('kpi_key', 'ltifr')->where('year', 2026)->first();
        $this->assertNotNull($sheTarget);
        $this->assertEquals(0.5, $sheTarget->target_value);

        $hseTarget = HseKpiTarget::where('key', 'ltifr')->first();
        $this->assertNotNull($hseTarget);
        $this->assertEquals(0.5, $hseTarget->target_value);
    }

    public function test_real_sap_calculation_without_mock_numbers(): void
    {
        $this->actingAs($this->admin);

        // 1. Create completed inspection and PTO
        HseInspection::create([
            'inspection_no' => 'INSP-001',
            'site_id' => $this->site->id,
            'department_id' => $this->department->id,
            'inspector_id' => $this->admin->id,
            'title' => 'Inspeksi Area Pit',
            'type' => 'Routine',
            'scheduled_date' => now()->toDateString(),
            'completed_date' => now()->toDateString(),
            'score' => 95.0,
            'status' => 'Completed',
        ]);

        HseInspection::create([
            'inspection_no' => 'PTO-001',
            'site_id' => $this->site->id,
            'department_id' => $this->department->id,
            'inspector_id' => $this->admin->id,
            'title' => 'PTO Hauling',
            'type' => 'PTO',
            'scheduled_date' => now()->toDateString(),
            'completed_date' => now()->toDateString(),
            'score' => 90.0,
            'status' => 'Completed',
        ]);

        // 2. Create Hazard report
        HipoReport::create([
            'source' => 'Hazard Report',
            'user_id' => $this->admin->id,
            'site_id' => $this->site->id,
            'jobsite' => $this->site->name,
            'reporter_name' => $this->admin->name,
            'report_time' => now(),
            'shift' => 'Shift 1',
            'category' => 'High Potential Hazard',
            'potential_consequence' => 'Injury Non LTI',
            'potential_risk_level' => 'Medium',
            'kta' => 'KTA-01',
            'tta' => 'TTA-01',
            'jenis_hipo' => 'Hazard Report',
            'description' => 'Tanggul jalan hauling retak',
            'risk_level' => 'Medium',
            'status' => 'Open',
        ]);

        // 3. Set SAP target
        SheSapTarget::create([
            'site_id' => $this->site->id,
            'department_id' => $this->department->id,
            'user_id' => $this->admin->id,
            'year' => (int) now()->year,
            'month' => (int) now()->month,
            'target_inspection' => 2,
            'target_pto' => 1,
            'target_hazard' => 2,
        ]);

        // Test employee performance
        $empRes = $this->get(route('hse.dashboard.sap.employee'));
        $empRes->assertStatus(200);
        $empRes->assertSee($this->admin->name);
        $empRes->assertDontSee('style="width: 85%"'); // Mock 85% must be completely removed

        // Test department performance
        $deptRes = $this->get(route('hse.dashboard.sap.department'));
        $deptRes->assertStatus(200);
        $deptRes->assertSee($this->department->name);
        $deptRes->assertDontSee('rand(80, 100)');
    }

    public function test_incident_lifecycle_and_auto_pica_generation(): void
    {
        $this->actingAs($this->admin);

        // 1. Submit incident (LTI / High)
        $incRes = $this->post(route('hse.dashboard.incidents.store'), [
            'site_id' => $this->site->id,
            'department_id' => $this->department->id,
            'location' => 'Hauling Road KM 12',
            'occurred_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'category' => 'Incident',
            'type' => 'Lost Time Injury',
            'severity' => 'High',
            'description' => 'Operator tergelincir saat menuruni tangga unit DT.',
            'lost_time_days' => 5,
            'injured_name' => 'Budi Santoso',
            'injury_type' => 'Fraktur Pergelangan Kaki',
        ]);
        $incRes->assertRedirect();

        $incident = HseIncident::latest()->first();
        $this->assertNotNull($incident);
        $this->assertEquals('Reported', $incident->status);
        $this->assertTrue((bool)$incident->recordable);

        // Verify Auto PICA creation
        $pica = HseCorrectiveAction::where('source_type', HseIncident::class)
            ->where('source_id', $incident->id)
            ->first();
        $this->assertNotNull($pica, 'PICA must be automatically generated for High severity LTI incident');
        $this->assertEquals('Open', $pica->status);

        // 2. Review incident
        $this->post(route('hse.dashboard.incidents.review', $incident->id), [
            'review_notes' => 'Data kronologi lengkap, lanjutkan investigasi.',
        ])->assertRedirect();

        $incident->refresh();
        $this->assertEquals('Under Review', $incident->status);

        // 3. Investigate incident
        $this->post(route('hse.dashboard.incidents.investigate', $incident->id), [
            'root_cause' => 'Tangga licin akibat ceceran lumpur dan operator tidak menggunakan 3-point contact.',
            'corrective_action' => 'Pembersihan tangga berkala sebelum shift jalan.',
            'preventive_action' => 'Refresh training 3-point contact untuk seluruh operator.',
        ])->assertRedirect();

        $incident->refresh();
        $this->assertEquals('Completed', $incident->investigation_status);

        // 4. Approve incident
        $this->post(route('hse.dashboard.incidents.approve', $incident->id))->assertRedirect();
        $incident->refresh();
        $this->assertEquals('Approved', $incident->status);

        // 5. Attempt close incident while PICA is still Open -> must be blocked
        $closeRes = $this->post(route('hse.dashboard.incidents.close', $incident->id));
        $closeRes->assertSessionHas('error');
        $incident->refresh();
        $this->assertEquals('Approved', $incident->status);

        // Close PICA first
        $pica->update(['status' => 'Closed', 'closed_at' => now()]);

        // Now incident close should succeed
        $this->post(route('hse.dashboard.incidents.close', $incident->id))->assertRedirect();
        $incident->refresh();
        $this->assertEquals('Closed', $incident->status);
    }

    public function test_risk_register_lifecycle(): void
    {
        $this->actingAs($this->admin);

        // 1. Create risk assessment
        $response = $this->post(route('hse.dashboard.risk.assessment.store'), [
            'site_id' => $this->site->id,
            'department_id' => $this->department->id,
            'activity' => 'Penggalian lereng tebing tinggi',
            'hazard' => 'Longsoran dinding pit',
            'category' => 'Mining Operation',
            'likelihood' => 4,
            'consequence' => 5,
            'residual_likelihood' => 2,
            'residual_consequence' => 3,
            'existing_controls' => 'Geotech monitoring radar',
            'treatment' => 'Pembuatan bench berjenjang sesuai desain geoteknik',
        ]);
        $response->assertRedirect();

        $risk = HseRiskAssessment::latest()->first();
        $this->assertNotNull($risk);
        $this->assertEquals(20, $risk->inherent_score);
        $this->assertEquals('Extreme', $risk->risk_level);
        $this->assertEquals(6, $risk->residual_score);

        // 2. Review risk
        $this->post(route('hse.dashboard.risk.review', $risk->id), [
            'review_notes' => 'Kontrol geoteknik telah diverifikasi tim teknik.',
        ])->assertRedirect();

        $risk->refresh();
        $this->assertEquals('Reviewed', $risk->status);

        // 3. Approve risk
        $this->post(route('hse.dashboard.risk.approve', $risk->id))->assertRedirect();
        $risk->refresh();
        $this->assertEquals('Approved', $risk->status);

        // 4. Archive risk
        $this->post(route('hse.dashboard.risk.close', $risk->id))->assertRedirect();
        $risk->refresh();
        $this->assertEquals('Archived', $risk->status);
    }

    public function test_she_form_integration_with_explicit_type(): void
    {
        $service = app(SheFormIntegrationService::class);

        $form = Form::create([
            'site_id' => $this->site->id,
            'department_id' => $this->department->id,
            'created_by' => $this->admin->id,
            'type' => 'custom',
            'doc_type' => 'form',
            'title' => 'Formulir Pelaporan Insiden Lapangan',
            'she_integration_type' => 'incident',
            'schema' => [],
            'status' => 'published',
        ]);

        $entry = FormEntry::create([
            'form_id' => $form->id,
            'user_id' => $this->admin->id,
            'status' => 'approved',
            'data' => [
                'kategori' => 'Incident',
                'tipe' => 'Lost Time Injury',
                'severity' => 'High',
                'deskripsi' => 'Insiden mekanik terluka saat maintenance loader',
                'lost_days' => 3,
            ],
        ]);

        $result = $service->syncEntryToShe($entry);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('incident', $result['integration_type']);

        $incident = HseIncident::where('incident_no', 'INC-ENTRY-' . $entry->id)->first();
        $this->assertNotNull($incident);
        $this->assertEquals('Lost Time Injury', $incident->type);
        $this->assertEquals('High', $incident->severity);

        // Test idempotency: syncing same entry again should not create duplicate
        $resultSecond = $service->syncEntryToShe($entry);
        $this->assertEquals('success', $resultSecond['status']);
        $this->assertEquals(1, HseIncident::where('incident_no', 'INC-ENTRY-' . $entry->id)->count());
    }

    public function test_document_repository_lifecycle_and_security(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        // 1. Upload new SOP
        $file = UploadedFile::fake()->create('sop_hauling_safe_work.pdf', 500, 'application/pdf');

        $storeRes = $this->post(route('hse.dashboard.documents.store'), [
            'document_code' => 'SOP-SHE-OPS-001',
            'title' => 'SOP Pengoperasian Dump Truck Aman',
            'category' => 'SOP',
            'department_id' => $this->department->id,
            'site_id' => $this->site->id,
            'version' => '1.0',
            'effective_date' => now()->toDateString(),
            'review_date' => now()->addYear()->toDateString(),
            'file_document' => $file,
        ]);
        $storeRes->assertRedirect();

        $doc = SheDocument::where('document_code', 'SOP-SHE-OPS-001')->first();
        $this->assertNotNull($doc);
        $this->assertEquals('Submitted', $doc->status);

        // 2. Approve document
        $this->post(route('hse.dashboard.documents.approve', $doc->id), [
            'action' => 'approve_publish',
            'notes' => 'SOP disetujui untuk operasional.',
        ])->assertRedirect();

        $doc->refresh();
        $this->assertEquals('Published', $doc->status);
        $this->assertNotNull($doc->approved_date);

        // 3. Download document
        $downloadRes = $this->get(route('hse.dashboard.documents.download', $doc->id));
        $downloadRes->assertStatus(200);

        // 4. Test security: restricted user without site access is rejected
        $otherSite = Site::create(['code' => 'OTHER', 'name' => 'Site Other']);
        $restrictedUser = User::factory()->create([
            'role' => 'supervisor',
            'is_active' => true,
        ]);
        $restrictedUser->sites()->attach($otherSite->id);

        $this->actingAs($restrictedUser);
        $forbiddenRes = $this->get(route('hse.dashboard.documents.download', $doc->id));
        $forbiddenRes->assertStatus(403);
    }

    public function test_all_she_dashboard_and_operations_routes_render_ok()
    {
        $this->actingAs($this->admin);

        $routes = [
            '/hse/dashboard/overview',
            '/hse/dashboard/safety',
            '/hse/dashboard/incidents',
            '/hse/dashboard/inspections',
            '/hse/dashboard/training',
            '/hse/dashboard/environment',
            '/hse/dashboard/risk-compliance',
            '/hse/dashboard/actions',
            '/hse/dashboard/sites',
            '/hse/dashboard/reports',
            '/hse/dashboard/manhours',
            '/hse/dashboard/manhours/create',
            '/hse/dashboard/targets',
            '/hse/dashboard/targets/create',
            '/hse/dashboard/sap',
            '/hse/dashboard/sap/achievement/leading',
            '/hse/dashboard/sap/achievement/lagging',
            '/hse/dashboard/sap/employee-performance',
            '/hse/dashboard/sap/department-performance',
            '/hse/dashboard/sap/targets',
            '/hse/dashboard/sap/targets/create',
            '/hse/dashboard/risk-register',
            '/hse/dashboard/risk-register/list',
            '/hse/dashboard/documents',
            '/hse/dashboard/performance/fatality',
            '/hse/dashboard/performance/lti',
            '/hse/dashboard/performance/near-miss',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->status(), "Failed asserting 200 for URL: {$url}");
        }
    }
}
