<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ContractTableLayoutTest extends TestCase {
 use RefreshDatabase;
 public function test_contract_pagination_is_inside_the_same_table_panel(): void {
  $this->actingAs(User::factory()->create(['role'=>'super_admin','is_active'=>true]));
  foreach (['admin.contracts.index','user.contracts.index'] as $route) {
   $response=$this->get(route($route))->assertOk();
   $dom=new \DOMDocument();@$dom->loadHTML($response->getContent());$xpath=new \DOMXPath($dom);
   $this->assertSame(1,$xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," ui-data-table ")]/div[contains(concat(" ",normalize-space(@class)," ")," ui-table-pagination ")]')->length);
   $this->assertSame(1,$xpath->query('//div[contains(concat(" ",normalize-space(@class)," ")," ui-table-pagination ")]')->length);
   $response->assertSee('ui-table-empty-row',false);
  }
 }
}
