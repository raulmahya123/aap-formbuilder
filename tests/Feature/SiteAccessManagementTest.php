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
 public function test_site_and_role_assignment_is_atomic_and_supports_department_admin(): void {
  $admin=User::factory()->create(['role'=>'super_admin','is_active'=>true]);
  $user=User::factory()->create(['role'=>'user','is_active'=>true]);
  $site=Site::create(['code'=>'QA','name'=>'QA Site']);
  $department=\App\Models\Department::create(['name'=>'Operations','slug'=>'operations']);
  $this->actingAs($admin)->post(route('admin.site_access.store'),['email'=>$user->email,'site_id'=>$site->id,'role'=>'user','department_id'=>$department->id,'dept_role'=>'dept_admin'])->assertSessionHasNoErrors()->assertRedirect();
  $this->assertTrue($user->fresh()->hasSiteAccess($site->id));
  $this->assertTrue($user->fresh()->isDeptAdminOf($department->id));
  $this->post(route('admin.site_access.store'),['email'=>$user->email,'site_id'=>$site->id,'role'=>'super_admin'])->assertSessionHasNoErrors();
  $this->assertTrue($user->fresh()->isSuperAdmin());
  $this->post(route('admin.site_access.store'),['email'=>$admin->email,'site_id'=>$site->id,'role'=>'user'])->assertSessionHasErrors('role');
  $this->assertTrue($admin->fresh()->isSuperAdmin());
  $this->assertDatabaseMissing('user_site_access',['user_id'=>$admin->id,'site_id'=>$site->id]);
 }
 public function test_invalid_department_role_does_not_assign_site(): void {
  $admin=User::factory()->create(['role'=>'super_admin','is_active'=>true]);
  $user=User::factory()->create(['role'=>'user']);
  $site=Site::create(['code'=>'QA','name'=>'QA Site']);
  $this->actingAs($admin)->post(route('admin.site_access.store'),['email'=>$user->email,'site_id'=>$site->id,'dept_role'=>'dept_admin'])->assertSessionHasErrors('department_id');
  $this->assertDatabaseCount('user_site_access',0);
 }
}
