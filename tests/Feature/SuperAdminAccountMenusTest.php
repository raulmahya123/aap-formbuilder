<?php

namespace Tests\Feature;

use App\Models\{User, UserSiteAccess};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SuperAdminAccountMenusTest extends TestCase
{
    use RefreshDatabase;

    public function test_other_roles_cannot_open_or_mutate_account_management_even_with_feature_grants(): void
    {
        foreach (['admin_ho', 'admin_site', 'user'] as $role) {
            $user = User::factory()->create(['role'=>$role, 'is_active'=>true, 'feature_access'=>['users','site_access','dashboard']]);
            $this->actingAs($user);
            $this->get(route('admin.users.active.index'))->assertForbidden();
            $this->get(route('admin.site_access.index'))->assertForbidden();
            $this->post(route('admin.site_access.store'), ['email'=>$user->email])->assertForbidden();
            $this->patch(route('admin.users.active.toggle', $user))->assertForbidden();
            $this->patch(route('admin.users.active.password', $user), [])->assertForbidden();
            $this->put(route('admin.users.active.update', $user), [])->assertForbidden();
            $this->assertFalse(Gate::forUser($user)->allows('manageSiteAccess', UserSiteAccess::class));
            if ($role === 'admin_ho') {
                $this->get(route('admin.dashboard'))->assertOk()
                    ->assertDontSee('title="Manage Users"', false)->assertDontSee('title="Input Access"', false);
            }
        }
    }

    public function test_super_admin_can_open_both_menus(): void
    {
        $this->actingAs(User::factory()->create(['role'=>'super_admin', 'is_active'=>true]));
        $this->get(route('admin.users.active.index'))->assertOk();
        $this->get(route('admin.site_access.index'))->assertOk();
    }
}
