<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Site;
use App\Models\Form;
use App\Models\FormEntry;
use App\Models\SheManhour;
use App\Models\SheKpiTarget;
use App\Models\HseCorrectiveAction;
use App\Services\Hse\SheFormIntegrationService;
use App\Services\Hse\HseDashboardService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SheFeatureIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first() ?? User::factory()->create(['role' => 'super_admin']);
    }

    public function test_hse_dashboard_safety_renders_with_18_kpis()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('hse.dashboard.safety'));
        $response->assertStatus(200);
        $response->assertSee('Safety Performance');
        $response->assertSee('LTIFR');
        $response->assertSee('LTISR');
        $response->assertSee('Total Fatality');
        $response->assertSee('Property Damage');
        $response->assertSee('Near Miss');
    }

    public function test_manhours_management_flow()
    {
        $this->actingAs($this->admin);
        $site = Site::first() ?? Site::create(['code' => 'TEST', 'name' => 'Test Site']);

        // 1. Submit manhours
        $response = $this->post(route('hse.dashboard.manhours.store'), [
            'date' => now()->toDateString(),
            'site_id' => $site->id,
            'employee_count' => 150,
            'manhours' => 1200,
            'lost_hours' => 0,
            'notes' => 'Test manhour input',
        ]);
        $response->assertRedirect();

        $manhour = SheManhour::latest()->first();
        $this->assertNotNull($manhour);
        $this->assertEquals('Submitted', $manhour->status);
        $this->assertEquals(1200, $manhour->manhours);

        // 2. Validate manhours
        $valResponse = $this->post(route('hse.dashboard.manhours.validate', $manhour->id), [
            'action' => 'validate',
        ]);
        $valResponse->assertRedirect();

        $manhour->refresh();
        $this->assertEquals('Validated', $manhour->status);
        $this->assertNotNull($manhour->validated_at);
    }

    public function test_kpi_targets_management()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('hse.dashboard.targets.store'), [
            'kpi_key' => 'fatality',
            'year' => 2026,
            'target_value' => 0,
            'unit' => 'kasus',
            'direction' => 'lower',
        ]);
        $response->assertRedirect();

        $target = SheKpiTarget::where('kpi_key', 'fatality')->where('year', 2026)->first();
        $this->assertNotNull($target);
        $this->assertEquals(0, $target->target_value);
    }

    public function test_sap_dashboard_accessible()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('hse.dashboard.sap.index'));
        $response->assertStatus(200);
        $response->assertSee('Safety Accountability Program (SAP)');
        $response->assertSee('Inspeksi Pengawas');
        $response->assertSee('Planned Task Observation (PTO)');
        $response->assertSee('Hazard Report');
    }

    public function test_pica_verification_update()
    {
        $this->actingAs($this->admin);

        $pica = HseCorrectiveAction::create([
            'action_no' => 'PICA-TEST-' . time(),
            'title' => 'Test Action Verification',
            'priority' => 'Medium',
            'status' => 'Open',
        ]);

        $response = $this->post(route('hse.dashboard.actions.update', $pica->id), [
            'status' => 'Closed',
            'action_note' => 'Action verified successfully by SHE manager',
        ]);
        $response->assertRedirect();

        $pica->refresh();
        $this->assertEquals('Closed', $pica->status);
        $this->assertNotNull($pica->closed_at);
    }
}
