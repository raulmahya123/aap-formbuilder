<?php

namespace Tests\Feature;

use App\Models\{Site, User, WellnessResponse};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSiteFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_site_switch_and_reset_override_session_and_preserve_drilldown(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $one = Site::create(['code' => 'ONE', 'name' => 'Site One']);
        $two = Site::create(['code' => 'TWO', 'name' => 'Site Two']);
        foreach ([$one, $two, $two] as $site) {
            WellnessResponse::forceCreate(['name' => 'Test', 'site' => $site->code]);
        }
        $this->actingAs($admin)->withSession(['active_site_id' => $one->id]);
        $this->get(route('admin.dashboard'))->assertOk()
            ->assertViewHas('metrics', fn ($metrics) => $metrics['wellness'] === 1);
        $response = $this->get(route('admin.dashboard', ['site_id' => $two->id]));
        $response->assertOk()->assertViewHas('selectedSiteId', $two->id)
            ->assertViewHas('metrics', fn ($metrics) => $metrics['wellness'] === 2)
            ->assertSee('data-submit-on-change', false)
            ->assertSee(route('admin.dashboard').'?site_id=', false);
        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//form[@data-listing-filters]//*[@name="site_id"]')->length);
        $this->get(route('admin.dashboard').'?site_id=')->assertOk()
            ->assertViewHas('selectedSiteId', null)
            ->assertViewHas('metrics', fn ($metrics) => $metrics['wellness'] === 3)
            ->assertSee(route('admin.wellness.index').'?site_id=', false);
    }

    public function test_all_sites_means_only_assigned_sites_and_other_site_is_forbidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin_site', 'is_active' => true]);
        $one = Site::create(['code' => 'ONE', 'name' => 'Site One']);
        $two = Site::create(['code' => 'TWO', 'name' => 'Site Two']);
        $admin->sites()->attach($one->id);
        foreach ([$one, $two] as $site) WellnessResponse::forceCreate(['name' => 'Test', 'site' => $site->code]);
        $this->actingAs($admin)->get(route('admin.dashboard').'?site_id=')->assertOk()
            ->assertViewHas('metrics', fn ($metrics) => $metrics['wellness'] === 1)
            ->assertViewHas('sites', fn ($sites) => $sites->modelKeys() === [$one->id]);
        $this->get(route('admin.dashboard', ['site_id' => $two->id]))->assertForbidden();
    }
}
