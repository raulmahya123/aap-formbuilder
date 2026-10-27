<?php

namespace Tests\Feature;

use App\Models\{Department, Site, User, WellnessResponse};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WellnessMasterAndMedicationTest extends TestCase
{
    use RefreshDatabase;

    private function answers(): array
    {
        return ['name'=>'Test', 'nik'=>'QA', 'position'=>'Operator',
            'department_id'=>Department::firstOrCreate(['slug'=>'she'], ['name'=>'SHE'])->id,
            'site_id'=>Site::firstOrCreate(['code'=>'QA'], ['name'=>'QA Site'])->id,
            'shift'=>'pagi', 'sleep_before_shift'=>'6_jam', 'sleep_quality'=>'baik',
            'heavy_drowsiness'=>'tidak', 'health_complaint'=>'tidak',
            'drowsy_medication'=>'ya', 'work_readiness'=>'ya'];
    }

    public function test_public_form_loads_master_dropdowns_and_rejects_unknown_ids(): void
    {
        $payload = $this->answers();
        $this->get(route('wellness.create'))->assertOk()->assertSee('Pilih departemen')->assertSee('QA Site')
            ->assertSee('name="department_id"', false)->assertSee('name="site_id"', false)
            ->assertSee('Nama obat yang dikonsumsi');
        $payload['department_id'] = 999999;
        $payload['site_id'] = 999999;
        $this->post(route('wellness.store'), $payload)->assertSessionHasErrors(['department_id','site_id']);
        $this->assertDatabaseCount('wellness_responses', 0);
    }

    public function test_medication_yes_requires_names_and_saves_master_snapshots_and_details(): void
    {
        $payload = $this->answers();
        $this->post(route('wellness.store'), $payload)->assertSessionHasErrors('drowsy_medication_details');
        $this->assertDatabaseCount('wellness_responses', 0);
        $payload['drowsy_medication_details'] = 'Obat A, Obat B';
        $payload['health_complaint'] = 'ya';
        $payload['health_complaint_details'] = 'Sakit kepala';
        $payload['site'] = 'Site palsu dari client';
        $payload['department'] = 'Department palsu';
        $this->post(route('wellness.store'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('wellness.create'));
        $this->assertDatabaseHas('wellness_responses', ['site_id'=>$payload['site_id'], 'department_id'=>$payload['department_id'],
            'site'=>'QA', 'department'=>'SHE', 'drowsy_medication_details'=>'Obat A, Obat B', 'health_complaint_details'=>'Sakit kepala']);
        $this->actingAs(User::factory()->create(['role'=>'super_admin', 'is_active'=>true]))
            ->get(route('admin.wellness.index'))->assertOk()->assertSee('Nama obat')->assertSee('Obat A, Obat B')->assertSee('Departemen');
    }

    public function test_no_medication_discards_stale_names_and_legacy_responses_stay_readable(): void
    {
        $payload = array_replace($this->answers(), ['drowsy_medication'=>'tidak', 'drowsy_medication_details'=>'Stale']);
        $this->post(route('wellness.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('wellness_responses', ['drowsy_medication'=>'tidak', 'drowsy_medication_details'=>null]);
        WellnessResponse::forceCreate(['name'=>'Legacy QA','site'=>'Old site']);
        $this->actingAs(User::factory()->create(['role'=>'super_admin', 'is_active'=>true]))
            ->get(route('admin.wellness.index'))->assertOk()->assertSee('Legacy QA');
    }
}
