<?php

namespace Tests\Feature;

use App\Models\{User, Department, Site};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleRedesignTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => 'super_admin', 'is_active' => true])->save();
        return $user;
    }

    public function test_module_index_pages_render_shared_header_and_pagination(): void
    {
        $this->actingAs($this->administrator());
        foreach (['admin.dashboard','admin.wellness.index','admin.departments.index','admin.forms.index','admin.qa.index','admin.contracts.index','admin.users.active.index','admin.sites.index','admin.companies.index','admin.groups.index','admin.indicators.index','admin.daily.index','admin.documents.index','admin.document_templates.index','admin.entries.index','admin.site_access.index','admin.reports.monthly','user.daily_notes.index','ccm-reports.index','admin.hipo.index'] as $route) {
            $response = $this->get(route($route));
            $response->assertOk();
            $response->assertSee('ui-page-header', false);
            $response->assertSee('data-toast-provider', false);
        }
    }

    public function test_department_search_pagination_and_modal_crud_keep_validation(): void
    {
        $this->actingAs($this->administrator());
        foreach (range(1, 12) as $i) Department::create(['name' => 'Department '.$i, 'slug' => 'department-'.$i]);
        $this->get(route('admin.departments.index'))->assertViewHas('departments', fn ($rows) => $rows->perPage() === 10 && $rows->total() === 12);
        $this->get(route('admin.departments.index', ['q' => 'Department 12', 'per_page' => 25]))->assertViewHas('departments', fn ($rows) => $rows->total() === 1 && $rows->perPage() === 25);
        $this->post(route('admin.departments.store'), ['name' => 'Engineering'])->assertRedirect();
        $department = Department::where('name', 'Engineering')->firstOrFail();
        $this->put(route('admin.departments.update', $department), ['name' => 'Engineering Updated'])->assertRedirect();
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'name' => 'Engineering Updated']);
        $this->delete(route('admin.departments.destroy', $department))->assertRedirect();
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->post(route('admin.departments.store'), ['name' => ''])->assertSessionHasErrors('name');
    }

    public function test_site_switch_preserves_permission_and_clears_stale_filters(): void
    {
        $site = Site::create(['name' => 'QA Site', 'code' => 'QA']);
        $this->actingAs($this->administrator())
            ->from('/admin/dashboard?site_id=999&page=4&q=keep')
            ->post(route('admin.sites.switch'), ['site_id' => $site->id])
            ->assertRedirect('/admin/dashboard?q=keep')->assertSessionHas('active_site_id', $site->id);
        $user = User::factory()->create();
        $user->forceFill(['role' => 'user'])->save();
        $this->actingAs($user)->get(route('admin.departments.create'))->assertForbidden();
    }

    public function test_current_wellness_questionnaire_saves_without_fabricating_legacy_answers(): void
    {
        $this->post(route('wellness.store'), ['name'=>'Contoh QA','nik'=>'QA-1','position'=>'Operator','site'=>'QA','shift'=>'pagi','sleep_before_shift'=>'lebih_6_jam','health_complaint'=>'tidak_ada','drowsy_medication'=>'tidak','work_readiness'=>'ya'])->assertRedirect(route('wellness.create'));
        $this->assertDatabaseHas('wellness_responses', ['nik'=>'QA-1','work_readiness'=>'ya','mood'=>null,'stress_level'=>null]);
    }

    public function test_mandala_metadata_modal_uses_existing_scope_and_save_flow(): void
    {
        $this->actingAs($admin = $this->administrator());
        $company = \App\Models\Company::create(['code'=>'QA','name'=>'QA Company','slug'=>'qa-company']);
        $site = Site::create(['code'=>'QA','name'=>'QA Site','company_id'=>$company->id]);
        $department = Department::create(['name'=>'Engineering','slug'=>'engineering']);
        $payload = ['title'=>'QA Form','company_id'=>$company->id,'department_id'=>$department->id,'site_id'=>$site->id,'doc_type'=>'FORM','type'=>'builder','schema'=>'{"fields":[]}','_ui_modal_url'=>route('admin.forms.create')];
        $this->post(route('admin.forms.store'), $payload)->assertRedirect(route('admin.forms.index'));
        $form = \App\Models\Form::where('title','QA Form')->firstOrFail();
        $this->get(route('admin.forms.edit',$form))->assertOk()->assertSee('data-form-sites', false)->assertSee('form="mandala-csv-import"', false);
        $this->put(route('admin.forms.update',$form), array_merge($payload,['title'=>'QA Updated']))->assertRedirect(route('admin.forms.index'));
        $this->assertDatabaseHas('forms',['id'=>$form->id,'title'=>'QA Updated','site_id'=>$site->id]);
    }

    public function test_department_admin_only_sees_assigned_site_wellness_and_cannot_manage_users(): void
    {
        $user = User::factory()->create(['role'=>'admin_site']);
        $department = Department::create(['name'=>'Site Team','slug'=>'site-team']);
        $department->users()->attach($user->id, ['dept_role'=>'dept_admin']);
        $allowed = Site::create(['code'=>'ONE','name'=>'Assigned Site']);
        $other = Site::create(['code'=>'TWO','name'=>'Other Site']);
        $user->sites()->attach($allowed->id);
        foreach ([$allowed, $other] as $site) \App\Models\WellnessResponse::create(['name'=>'QA '.$site->code,'nik'=>'QA-'.$site->id,'position'=>'Operator','site'=>$site->code,'shift'=>'pagi','sleep_before_shift'=>'lebih_6_jam','health_complaint'=>'tidak_ada','drowsy_medication'=>'tidak','work_readiness'=>'ya']);
        $this->actingAs($user)->get(route('admin.wellness.index'))->assertOk()
            ->assertViewHas('responses', fn ($rows) => $rows->total() === 1 && $rows->first()->site === 'ONE')
            ->assertViewHas('stats', fn ($stats) => $stats['total'] === 1);
        $this->get(route('admin.wellness.index', ['site_id'=>$other->id]))->assertForbidden();
        $this->get(route('admin.users.active.index'))->assertForbidden();
        $group = \App\Models\IndicatorGroup::create(['name'=>'Safety','code'=>'SAFETY','order_index'=>1,'is_active'=>true]);
        $indicator = \App\Models\Indicator::create(['indicator_group_id'=>$group->id,'name'=>'Hours','code'=>'HOURS','data_type'=>'int','agg'=>'sum','unit'=>'jam','order_index'=>1]);
        foreach ([$allowed, $other] as $site) \App\Models\IndicatorDaily::create(['site_id'=>$site->id,'indicator_id'=>$indicator->id,'date'=>today(),'value'=>10]);
        $this->get(route('daily.index'))->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1 && $rows->first()->site_id === $allowed->id);
    }

    public function test_legacy_wellness_answers_remain_visible_without_inventing_readiness(): void
    {
        \App\Models\WellnessResponse::forceCreate(['name'=>'Legacy QA','mood'=>'Tenang','stress_level'=>3]);
        $this->actingAs($this->administrator())->get(route('admin.wellness.index'))->assertOk()->assertSee('Belum tersedia')->assertSee('Pemeriksaan sebelumnya')->assertSee('Tenang');
    }

    public function test_jobsite_options_display_master_sites_and_respect_site_permissions(): void
    {
        $user = User::factory()->create(['role'=>'admin_site']);
        $department = Department::create(['name'=>'Jobsite Team','slug'=>'jobsite-team']);
        $department->users()->attach($user->id, ['dept_role'=>'dept_admin']);
        $company = \App\Models\Company::create(['code'=>'COMP','name'=>'Company','slug'=>'company']);
        $allowed = Site::create(['code'=>'SITE-1','name'=>'Area Operasional','company_id'=>$company->id]);
        Site::create(['code'=>'SITE-2','name'=>'Area Terlarang','company_id'=>$company->id]);
        $user->sites()->attach($allowed->id);
        $this->actingAs($user)->get(route('ccm-reports.create'))->assertOk()
            ->assertSee('id="ccm-reporter"', false)
            ->assertViewHas('reporters', fn ($reporters) => $reporters->contains('id', $user->id))
            ->assertViewHas('jobsites', fn ($options) => $options->count() === 1 && $options->first()['label'] === 'SITE-1 — Area Operasional' && $options->first()['value'] === 'COMP-SITE-1');
        $this->get(route('admin.hipo.create'))->assertOk()
            ->assertViewHas('sites', fn ($options) => $options->count() === 1 && $options->first()['id'] === $allowed->id && $options->first()['label'] === 'SITE-1 — Area Operasional');
    }

    public function test_report_lists_resolve_jobsite_from_master_site_without_rewriting_history(): void
    {
        $this->actingAs($this->administrator());
        $company = \App\Models\Company::create(['code'=>'COMP','name'=>'Company','slug'=>'company']);
        $site = Site::create(['code'=>'SITE-1','name'=>'Area Operasional','company_id'=>$company->id]);
        $this->get(route('ccm-reports.index'))->assertOk()
            ->assertViewHas('jobsiteLabels', fn ($labels) => $labels->get('COMP-SITE-1') === 'SITE-1 — Area Operasional');
        $hipo = new \App\Models\HipoReport(['jobsite'=>'COMP-SITE-1']);
        $hipo->setRelation('site', $site);
        $this->assertSame('SITE-1 — Area Operasional', $hipo->jobsite_display);
        $this->assertSame('COMP-SITE-1', $hipo->jobsite);
        $hipo->setRelation('site', null);
        $this->assertSame('COMP-SITE-1', $hipo->jobsite_display);
    }
}
