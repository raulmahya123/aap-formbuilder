<?php
namespace Tests\Feature;

use App\Models\{User, Site, SapInspectionTemplate, SapInspection, SapPtoRecord, SapHazardReport, UserSiteAccess};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedActionsAndExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_pdf_is_a_real_download_and_validates_payload(): void
    {
        $payload = ['title'=>'Daftar Site', 'columns'=>['Nama', 'Status'], 'rows'=>[['Site A', 'Aktif']]];
        $this->postJson(route('table_export.pdf'), $payload)->assertUnauthorized();
        $this->actingAs(User::factory()->create(['is_active'=>true]));
        $response = $this->postJson(route('table_export.pdf'), $payload)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->postJson(route('table_export.pdf'), ['title'=>'X', 'columns'=>['A'], 'rows'=>[['1','2']]])->assertUnprocessable();
        $this->postJson(route('table_export.pdf'), ['title'=>'X', 'columns'=>['A'], 'rows'=>array_fill(0,101,['1'])])->assertUnprocessable();
        $html = view('exports.table', $payload + ['title'=>'<script>alert(1)</script>', 'exportedAt'=>now()])->render();
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_sap_exports_and_soft_delete_respect_sites_status_and_features(): void
    {
        $admin = User::factory()->create(['role'=>'super_admin','is_active'=>true]);
        $site = Site::create(['code'=>'ACT','name'=>'Action Test']);
        $template = SapInspectionTemplate::create(['code'=>'ACT-01','name'=>'Action Checklist','scoring_method'=>'numeric_1_4','is_active'=>true]);
        $records = [
            'inspeksi'=>SapInspection::create(['inspection_no'=>'ACT-I','template_id'=>$template->id,'site_id'=>$site->id,'inspector_id'=>$admin->id,'status'=>'Draft']),
            'pto'=>SapPtoRecord::create(['pto_no'=>'ACT-P','site_id'=>$site->id,'observer_id'=>$admin->id,'status'=>'Draft']),
            'hazard'=>SapHazardReport::create(['hazard_no'=>'ACT-H','site_id'=>$site->id,'reporter_id'=>$admin->id,'hazard_description'=>'Kondisi licin','status'=>'Draft']),
        ];
        $this->actingAs($admin);
        foreach ($records as $module=>$record) {
            $this->get(route('hse.dashboard.sap.record.export', [$module,$record->id,'print']))->assertOk()->assertSee('MANDALA')->assertSee('Action Test');
            $response=$this->get(route('hse.dashboard.sap.record.export', [$module,$record->id,'pdf']))->assertOk()->assertHeader('content-type','application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent());
            $this->get(route('hse.dashboard.sap.'.$module.'.index'))->assertOk()->assertSee('aria-label="Unduh PDF laporan"',false)->assertSee('aria-label="Hapus draft"',false);
        }
        $outsider=User::factory()->create(['role'=>'admin_site','is_active'=>true]);
        $this->actingAs($outsider);
        foreach ($records as $module=>$record) {
            $this->get(route('hse.dashboard.sap.record.export', [$module,$record->id,'pdf']))->assertForbidden();
            $this->delete(route('hse.dashboard.sap.record.destroy',[$module,$record->id]))->assertForbidden();
        }
        $this->actingAs($admin);
        $records['pto']->update(['status'=>'Submitted']);
        $this->delete(route('hse.dashboard.sap.record.destroy',['pto',$records['pto']->id]))->assertForbidden();
        $this->delete(route('hse.dashboard.sap.record.destroy',['inspeksi',$records['inspeksi']->id]))->assertRedirect();
        $this->assertSoftDeleted('sap_inspections',['id'=>$records['inspeksi']->id]);
        $this->assertDatabaseHas('sap_inspection_logs',['inspection_id'=>$records['inspeksi']->id,'action'=>'deleted']);
        $restricted=User::factory()->create(['role'=>'admin_site','is_active'=>true,'feature_access'=>[]]);
        $this->actingAs($restricted)->get(route('hse.dashboard.sap.record.export',['pto',$records['pto']->id,'pdf']))->assertForbidden();
    }
}
