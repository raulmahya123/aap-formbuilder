<?php

namespace Tests\Feature;

use App\Models\{User, Site, UserSiteAccess};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeatureAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_features_hide_navigation_and_deny_direct_read_and_write(): void
    {
        $user = User::factory()->create(['is_active'=>true, 'role'=>'user']);
        $user->forceFill(['feature_access'=>['contracts']])->save();
        $this->actingAs($user)->get(route('user.contracts.index'))->assertOk()->assertSee('Kontrak');
        $this->get(route('daily.index'))->assertForbidden();
        $this->post(route('daily.store'), [])->assertForbidden();
        $this->get(route('profile.edit'))->assertOk()->assertDontSee('title="Input Harian"', false);
        $this->get(route('admin.dashboard'))->assertRedirect(route('profile.edit'));
    }

    public function test_feature_grant_does_not_grant_admin_role(): void
    {
        $user = User::factory()->create(['is_active'=>true, 'role'=>'user']);
        $user->forceFill(['feature_access'=>['users', 'site_access']])->save();
        $this->actingAs($user)->get(route('admin.users.active.index'))->assertForbidden();
        $this->post(route('admin.site_access.store'), [])->assertForbidden();
    }

    public function test_admin_assigns_and_resets_feature_allowlist_without_duplicating_sites(): void
    {
        $admin = User::factory()->create(['role'=>'super_admin', 'is_active'=>true]);
        $user = User::factory()->create(['role'=>'user', 'is_active'=>true]);
        $site = Site::create(['code'=>'QA', 'name'=>'QA Site']);
        $payload = ['email'=>$user->email, 'site_id'=>$site->id, 'feature_mode'=>'custom', 'feature_access'=>['hipo','daily']];
        $this->actingAs($admin)->post(route('admin.site_access.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(['hipo','daily'], $user->fresh()->feature_access);
        $this->assertSame('user', $user->fresh()->role);
        $this->post(route('admin.site_access.store'), array_merge($payload, ['feature_mode'=>'default']))->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->feature_access);
        $this->assertSame(1, UserSiteAccess::count());
        $this->post(route('admin.site_access.store'), array_merge($payload, ['feature_access'=>['not_a_feature']]))->assertSessionHasErrors('feature_access.0');
        $this->assertNull($user->fresh()->feature_access);
    }

    public function test_empty_allowlist_denies_modules_but_keeps_account_access(): void
    {
        $user = User::factory()->create(['role'=>'user', 'is_active'=>true]);
        $user->forceFill(['feature_access'=>[]])->save();
        $this->actingAs($user)->get(route('user.contracts.index'))->assertForbidden();
        $this->get(route('profile.edit'))->assertOk();
        $this->assertFalse($user->canUseFeature('mandala'));
    }

    public function test_existing_users_and_super_admin_keep_existing_feature_scope(): void
    {
        $user = User::factory()->create(['role'=>'user', 'is_active'=>true]);
        $this->assertTrue($user->canUseFeature('daily'));
        $user->forceFill(['role'=>'super_admin', 'feature_access'=>[]])->save();
        $this->assertTrue($user->canUseFeature('site_access'));
        $this->assertTrue($user->canUseFeatureRoute('admin.sites.switch'));
    }
}
