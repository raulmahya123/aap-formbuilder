<?php
namespace Tests\Feature;
use App\Models\{User,QaThread};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PersonalChatTest extends TestCase {
 use RefreshDatabase;
 public function test_new_personal_chat_opens_with_no_messages_and_is_reused(): void {
  $sender=User::factory()->create(['is_active'=>true,'role'=>'super_admin']);
  $department=\App\Models\Department::create(['name'=>'Engineering','slug'=>'engineering']);
  $recipient=User::factory()->create(['is_active'=>true,'department_id'=>$department->id]);
  $response=$this->actingAs($sender)->postJson(route('admin.qa.personal.start'),['recipient_id'=>$recipient->id])->assertOk()->assertJsonPath('success',true);
  $id=$response->json('thread_id');
  $this->getJson(route('admin.qa.show',$id))->assertOk()->assertJsonPath('thread.other_user.id',$recipient->id)->assertJsonPath('messages',[]);
  $this->postJson(route('admin.qa.personal.start'),['recipient_id'=>$recipient->id])->assertOk()->assertJsonPath('thread_id',$id);
  $this->assertSame(1,QaThread::count());
  $this->actingAs(User::factory()->create(['is_active'=>true,'role'=>'super_admin']))->getJson(route('admin.qa.show',$id))->assertForbidden();
 }
}
