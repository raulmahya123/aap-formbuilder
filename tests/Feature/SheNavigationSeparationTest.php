<?php

namespace Tests\Feature;

use App\Models\{SapInspectionTemplate, User};
use App\Support\SheNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SheNavigationSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_module_separates_dashboards_from_input_and_all_links_exist(): void
    {
        $user = User::factory()->create(['role'=>'super_admin', 'is_active'=>true]);
        $this->actingAs($user);
        $walk = function ($items) use (&$walk) {
            foreach ($items as $item) {
                if (isset($item['children'])) $walk($item['children']);
                else $this->assertTrue(Route::has($item['route']), $item['route']);
            }
        };
        $groups = SheNavigation::groups();
        $this->assertCount(6, $groups);
        foreach ($groups as $group) {
            $labels = array_column($group['children'], 'label');
            $this->assertContains('Dashboard', $labels);
            $this->assertContains('Form Input', $labels);
        }
        $walk($groups);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Form Input');
    }

    public function test_standalone_forms_render_existing_post_actions_without_dashboard_filters(): void
    {
        $this->actingAs(User::factory()->create(['role'=>'super_admin', 'is_active'=>true]));
        foreach ([
            'risk.create'=>'risk.assessment.store', 'pica.create'=>'pica.store',
            'documents.create'=>'documents.store', 'incidents.create'=>'incidents.store',
            'training.create'=>'training.record.store', 'environment.create'=>'environment.record.store',
        ] as $create => $store) {
            $this->get(route('hse.dashboard.'.$create))->assertOk()
                ->assertSee(route('hse.dashboard.'.$store), false)
                ->assertDontSee('name="period"', false)->assertSee('Kembali ke daftar');
        }
    }

    public function test_inspection_forms_are_nested_and_template_selection_is_preserved(): void
    {
        $user = User::factory()->create(['role'=>'super_admin', 'is_active'=>true]);
        $template = SapInspectionTemplate::create(['code'=>'SHE-FM-QA', 'name'=>'Inspeksi QA', 'is_active'=>true, 'version'=>1, 'scoring_type'=>'numeric_1_4']);
        $this->actingAs($user)->get(route('admin.dashboard'))->assertOk()->assertSee('SHE-FM-QA');
        $this->get(route('hse.dashboard.sap.inspeksi.create', ['template_id'=>$template->id]))
            ->assertOk()->assertSee('value="'.$template->id.'" selected', false);
    }

    public function test_user_without_hse_feature_cannot_open_standalone_forms(): void
    {
        $user = User::factory()->create(['role'=>'admin_site', 'is_active'=>true, 'feature_access'=>['dashboard']]);
        $this->actingAs($user)->get(route('hse.dashboard.risk.create'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertOk()->assertDontSee('Form Risk Assessment');
    }
}
