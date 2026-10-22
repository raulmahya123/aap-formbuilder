<?php

namespace Tests\Feature;

use App\Models\{HipoReport, Site, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HipoPicSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pic_dropdown_includes_users_from_other_sites_and_update_persists(): void
    {
        $admin = User::factory()->create(['role'=>'super_admin', 'is_active'=>true]);
        $person = User::factory()->create(['name'=>'PIC Other Site']);
        $site = Site::create(['code'=>'QA', 'name'=>'QA Site']);
        $this->actingAs($admin)->get(route('admin.hipo.create'))->assertOk()
            ->assertSee('PIC Other Site')->assertSee('name="pic_engineering"', false);
        $report = HipoReport::create([
            'user_id'=>$admin->id, 'site_id'=>$site->id, 'jobsite'=>'QA',
            'reporter_name'=>$admin->name, 'report_time'=>now(), 'shift'=>'Shift 1',
            'source'=>'Hazard Report', 'category'=>'Nearmiss', 'jenis_hipo'=>'Nearmiss',
            'description'=>'Existing description', 'potential_consequence'=>'Fatality', 'status'=>'Open',
            'kta'=>'Unsafe condition', 'tta'=>'Unsafe action',
        ]);
        $this->get(route('admin.hipo.show', $report))->assertOk()->assertSee('PIC Other Site');
        $this->put(route('admin.hipo.update', $report), ['status'=>'Open', 'pic'=>$person->name])
            ->assertSessionHasNoErrors()->assertRedirect();
        $report->refresh();
        $this->assertSame($person->name, $report->pic);
        $this->assertSame($person->name, $report->pic_ppe);
        $this->assertSame('Existing description', $report->description);
    }
}
