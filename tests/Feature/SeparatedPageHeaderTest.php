<?php
namespace Tests\Feature;

use App\Models\{Site, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SeparatedPageHeaderTest extends TestCase
{
    use RefreshDatabase;

    private function xpath(string $html): \DOMXPath
    {
        $document = new \DOMDocument();
        @$document->loadHTML($html);
        return new \DOMXPath($document);
    }

    public function test_shared_header_places_filter_panel_outside_heading(): void
    {
        $html = Blade::render('<x-ui.page-header><div class="ui-page-header__copy"><x-ui.page-title>Contoh</x-ui.page-title></div><x-slot:filters><x-ui.table-toolbar><input name="q" /><x-slot:actions><x-ui.button type="submit">Terapkan</x-ui.button></x-slot:actions></x-ui.table-toolbar></x-slot:filters></x-ui.page-header>');
        $xpath=$this->xpath($html);
        $this->assertSame(0,$xpath->query('//header//form')->length);
        $this->assertSame(1,$xpath->query('//header/following-sibling::section[contains(@class,"ui-filter-panel")]//form')->length);
        $this->assertSame(1,$xpath->query('//section//div[contains(@class,"ui-filter-actions")]/button')->length);
    }

    public function test_admin_and_hse_pages_share_separate_headers_and_filter_panels(): void
    {
        $this->actingAs(User::factory()->create(['role'=>'super_admin','is_active'=>true]));
        Site::create(['code'=>'HDR','name'=>'Header Site']);
        foreach ([route('admin.indicators.index'),route('admin.dashboard'),route('hse.dashboard.sap.achievement','inspection')] as $url) {
            $response=$this->get($url)->assertOk();
            $xpath=$this->xpath($response->getContent());
            $this->assertSame(1,$xpath->query('//main//header[contains(@class,"ui-page-header")]')->length,$url);
            $this->assertSame(0,$xpath->query('//main//header[contains(@class,"ui-page-header")]//form[@method="GET"]')->length,$url);
            $this->assertGreaterThan(0,$xpath->query('//main//section[contains(@class,"ui-filter-panel")]//form')->length,$url);
        }
    }

    public function test_secondary_filters_keep_current_reporting_period_without_carrying_page(): void
    {
        $this->app['request']=\Illuminate\Http\Request::create('/example?period=quarterly&year=2026&quarter=3&site_id=2&page=4');
        $html=Blade::render('<x-ui.table-toolbar><input name="q" /><x-slot:actions><x-ui.button type="submit">Terapkan</x-ui.button></x-slot:actions></x-ui.table-toolbar>');
        $xpath=$this->xpath($html);
        foreach (['period'=>'quarterly','year'=>'2026','quarter'=>'3','site_id'=>'2'] as $name=>$value) {
            $this->assertSame($value,$xpath->query('//input[@name="'.$name.'"]')->item(0)->getAttribute('value'));
        }
        $this->assertSame(0,$xpath->query('//input[@name="page"]')->length);
    }
}
