<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WellnessSleepQualityTest extends TestCase
{
    use RefreshDatabase;

    private function answers(): array
    {
        return ['department_id'=>\App\Models\Department::firstOrCreate(['slug'=>'she'], ['name'=>'SHE'])->id, 'site_id'=>\App\Models\Site::firstOrCreate(['code'=>'QA'], ['name'=>'QA Site'])->id, 'name'=>'Test', 'nik'=>'QA', 'position'=>'Operator', 'site'=>'QA', 'shift'=>'pagi',
            'sleep_before_shift'=>'6_jam', 'health_complaint'=>'tidak', 'drowsy_medication'=>'tidak',
            'work_readiness'=>'ya', 'sleep_quality'=>'buruk', 'heavy_drowsiness'=>'ya'];
    }

    public function test_poor_sleep_requires_reason_and_saves_both_new_answers(): void
    {
        $this->post(route('wellness.store'), $this->answers())->assertSessionHasErrors('sleep_quality_reason');
        $this->assertDatabaseCount('wellness_responses', 0);
        $this->post(route('wellness.store'), $this->answers()+['sleep_quality_reason'=>'Sering terbangun'])
            ->assertSessionHasNoErrors()->assertRedirect(route('wellness.create'));
        $this->assertDatabaseHas('wellness_responses', ['sleep_quality'=>'buruk', 'sleep_quality_reason'=>'Sering terbangun', 'heavy_drowsiness'=>'ya']);
    }

    public function test_good_sleep_does_not_save_stale_reason_and_invalid_answers_are_rejected(): void
    {
        $payload = array_replace($this->answers(), ['sleep_quality'=>'baik', 'heavy_drowsiness'=>'tidak', 'sleep_quality_reason'=>'Stale']);
        $this->post(route('wellness.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('wellness_responses', ['sleep_quality'=>'baik', 'sleep_quality_reason'=>null, 'heavy_drowsiness'=>'tidak']);
        $this->post(route('wellness.store'), array_replace($payload, ['sleep_quality'=>'invalid', 'heavy_drowsiness'=>'invalid']))
            ->assertSessionHasErrors(['sleep_quality', 'heavy_drowsiness']);
    }

    public function test_health_complaint_requires_symptoms_and_no_complaint_discards_details(): void
    {
        $payload = array_replace($this->answers(), ['sleep_quality'=>'cukup', 'health_complaint'=>'ya']);
        $this->post(route('wellness.store'), $payload)->assertSessionHasErrors('health_complaint_details');
        $this->assertDatabaseCount('wellness_responses', 0);
        $payload['health_complaint_details'] = 'Sakit kepala sejak pagi';
        $this->post(route('wellness.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('wellness_responses', ['health_complaint'=>'ya', 'health_complaint_details'=>'Sakit kepala sejak pagi']);
        $payload['health_complaint'] = 'tidak';
        $this->post(route('wellness.store'), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('wellness_responses', ['health_complaint'=>'tidak', 'health_complaint_details'=>null]);
    }
}
