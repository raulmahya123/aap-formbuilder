<?php

namespace Tests\Feature;

use App\Models\{Site, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SapPageContentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): void
    {
        $this->actingAs(User::factory()->create(['role'=>'super_admin', 'is_active'=>true]));
        Site::create(['code'=>'QA', 'name'=>'Site Form QA']);
    }

    private function mainContent(string $html): \DOMXPath
    {
        $document = new \DOMDocument();
        @$document->loadHTML($html);
        return new \DOMXPath($document);
    }

    public function test_pto_and_hazard_create_pages_render_actual_forms_in_main_content(): void
    {
        $this->admin();
        foreach (['pto'=>'work_activity', 'hazard'=>'hazard_description'] as $module=>$field) {
            $response = $this->get(route('hse.dashboard.sap.'.$module.'.create'))->assertOk();
            $xpath = $this->mainContent($response->getContent());
            $this->assertSame(1, $xpath->query('//main//form[@method="POST"]')->length, $module);
            $this->assertSame(1, $xpath->query('//main//*[@name="'.$field.'"]')->length, $module);
            $this->assertSame(1, $xpath->query('//main//h1')->length, $module);
            $response->assertSee(route('hse.dashboard.sap.'.$module.'.store'), false)->assertSee('Site Form QA');
        }
    }

    public function test_legacy_sap_dashboards_and_lists_render_inside_component_layout(): void
    {
        $this->admin();
        foreach (['pto', 'hazard'] as $module) {
            foreach (['dashboard', 'index'] as $page) {
                $response = $this->get(route('hse.dashboard.sap.'.$module.'.'.$page))->assertOk();
                $xpath = $this->mainContent($response->getContent());
                $this->assertGreaterThan(0, $xpath->query('//main//h2')->length, $module.'.'.$page);
                $this->assertGreaterThan(0, strlen(trim($xpath->query('//main')->item(0)->textContent)));
            }
        }
    }

    public function test_shared_layout_preserves_named_header_and_default_slot(): void
    {
        $this->admin();
        $html = Blade::render('<x-app-layout><x-slot name="header"><h2>Header QA</h2></x-slot><p>Konten slot QA</p></x-app-layout>');
        $xpath = $this->mainContent($html);
        $this->assertStringContainsString('Header QA', $xpath->query('//main')->item(0)->textContent);
        $this->assertStringContainsString('Konten slot QA', $xpath->query('//main')->item(0)->textContent);
    }
}
