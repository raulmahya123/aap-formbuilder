<?php
namespace Tests\Feature;
use App\Models\{User,Site,UserSiteAccess};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class SiteAccessManagementTest extends TestCase {
 use RefreshDatabase;
 public function test_assign_search_and_revoke_preserve_role_and_avoid_duplicates(): void {
  $admin=User::factory()->create(['role'=>'super_admin','is_active'=>true]);
  $user=User::factory()->create(['name'=>'Operator Contoh','role'=>'user','is_active'=>true]);
  $site=Site::create(['code'=>'QA','name'=>'QA Site']);
  $payload=['email'=>$user->email,'site_id'=>$site->id];
  $this->actingAs($admin)->get(route('admin.site_access.index'))->assertOk()->assertSee('Role dan cakupan site');
  $this->post(route('admin.site_access.store'),$payload)->assertRedirect();
  $this->post(route('admin.site_access.store'),$payload)->assertRedirect()->assertSessionHas('info');
  $this->assertSame(1,UserSiteAccess::count());
  $this->assertTrue($user->fresh()->hasSiteAccess($site->id));
  $this->assertSame('user',$user->fresh()->role);
  $this->get(route('admin.site_access.index',['q'=>'Operator Contoh']))->assertOk()->assertViewHas('accesses',fn($rows)=>$rows->total()===1);
  $this->get(route('admin.site_access.index',['q'=>'Tidak ditemukan']))->assertViewHas('accesses',fn($rows)=>$rows->total()===0);
  $access=UserSiteAccess::firstOrFail();
  $this->delete(route('admin.site_access.destroy',$access))->assertRedirect();
  $this->assertFalse($user->fresh()->hasSiteAccess($site->id));
  $this->assertSame('user',$user->fresh()->role);
 }
 public function test_regular_user_cannot_assign_or_revoke_site_access(): void {
  $user=User::factory()->create(['role'=>'user','is_active'=>true]);
  $site=Site::create(['code'=>'QA','name'=>'QA Site']);
  $access=UserSiteAccess::create(['site_id'=>$site->id,'user_id'=>$user->id]);
  $this->actingAs($user)->get(route('admin.site_access.index'))->assertForbidden();
  $this->post(route('admin.site_access.store'),['site_id'=>$site->id,'email'=>$user->email])->assertForbidden();
  $this->delete(route('admin.site_access.destroy',$access))->assertForbidden();
 }
}
