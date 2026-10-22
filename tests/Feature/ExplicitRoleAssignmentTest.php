<?php
namespace Tests\Feature;
use App\Models\{User,Site,Department,Form,QaThread,Contract};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
class ExplicitRoleAssignmentTest extends TestCase {
 use RefreshDatabase;
 public function test_super_admin_assigns_site_admin_multiple_sites_and_department_member(): void {
  $ho=User::factory()->create(['role'=>'super_admin','is_active'=>true]);
  $user=User::factory()->create(['role'=>'user','is_active'=>true]);
  $a=Site::create(['code'=>'A','name'=>'Site A']);$b=Site::create(['code'=>'B','name'=>'Site B']);$c=Site::create(['code'=>'C','name'=>'Site C']);
  $department=Department::create(['name'=>'SHE','slug'=>'she']);
  $this->actingAs($ho)->post(route('admin.site_access.store'),['email'=>$user->email,'role'=>'admin_site','site_ids'=>[$a->id,$b->id],'site_selection'=>'replace','department_id'=>$department->id])->assertSessionHasNoErrors()->assertRedirect();
  $user=$user->fresh();$this->assertTrue($user->isAdmin());$this->assertTrue($user->isSiteAdmin());$this->assertFalse($user->isSuperAdmin());
  $this->assertEqualsCanonicalizing([$a->id,$b->id],$user->allowedSiteIds());
  $this->assertSame('member',$user->departments()->first()->pivot->dept_role);
  $this->actingAs($user)->get(route('admin.sites.index'))->assertOk()->assertViewHas('sites',fn($rows)=>$rows->total()===2);
  $this->get(route('admin.sites.edit',$c))->assertForbidden();
  $this->post(route('admin.site_access.store'),['email'=>$user->email,'site_id'=>$c->id,'role'=>'admin_ho'])->assertForbidden();
  $form=new Form(['department_id'=>$department->id,'site_id'=>$a->id]);
  $this->assertTrue(Gate::forUser($user)->allows('update',$form));
  $form->site_id=$c->id;$this->assertFalse(Gate::forUser($user)->allows('update',$form));
  $this->actingAs($ho)->post(route('admin.site_access.store'),['email'=>$user->email,'role'=>'user','site_ids'=>[$b->id],'site_selection'=>'replace'])->assertSessionHasNoErrors();
  $this->assertFalse($user->fresh()->isAdmin());$this->assertSame([$b->id],$user->fresh()->allowedSiteIds());
 }
 public function test_ho_does_not_need_sites_and_user_role_needs_one(): void {
  $ho=User::factory()->create(['role'=>'super_admin','is_active'=>true]);$user=User::factory()->create(['role'=>'user']);
  $this->actingAs($ho)->post(route('admin.site_access.store'),['email'=>$user->email,'role'=>'admin_ho','site_selection'=>'replace'])->assertSessionHasNoErrors();
  $this->assertNull($user->fresh()->allowedSiteIds());
  $this->post(route('admin.site_access.store'),['email'=>$user->email,'role'=>'admin_site','site_selection'=>'replace'])->assertSessionHasErrors('site_ids');
  $this->assertSame('admin_ho',$user->fresh()->role);
 }
 public function test_department_admin_membership_alone_does_not_grant_site_admin_role(): void {
  $user=User::factory()->create(['role'=>'user']);$dept=Department::create(['name'=>'SHE','slug'=>'she']);$user->departments()->attach($dept->id,['dept_role'=>'dept_admin']);
  $this->assertFalse($user->isAdmin());
 }
 public function test_ho_respects_personal_chat_and_contract_viewer_permissions(): void {
  $ho=User::factory()->create(['role'=>'admin_ho','is_active'=>true]);$owner=User::factory()->create();
  $thread=QaThread::create(['subject'=>'Private','type'=>'personal','scope'=>'private','created_by'=>$owner->id]);$thread->participants()->attach($owner->id,['role_label'=>'user']);
  $this->assertFalse(Gate::forUser($ho)->allows('reply',$thread));
  $contract=new Contract(['owner_id'=>$owner->id]);$contract->id=999;
  $this->assertFalse(Gate::forUser($ho)->allows('view',$contract));
  $this->assertTrue($ho->hasSiteAccess(999));
  $this->assertSame('admin',(new User(['role'=>'admin_site']))->chatRoleLabel());
 }
}
