<?php

namespace Tests\Feature;

use Tests\CmsTestCase;

class AuthTest extends CmsTestCase
{
    public function test_guests_are_sent_to_the_login_page(): void
    {
        foreach (['admin', 'admin/pages', 'admin/inquiries', 'admin/settings', 'admin/users', 'admin/menu', 'admin/shared'] as $url) {
            $this->get("/{$url}")->assertRedirect(route('admin.login'));
        }
    }

    public function test_admin_can_sign_in_and_see_the_dashboard(): void
    {
        $user = $this->admin();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Correct-Horse-9'])->assertRedirect(route('admin.dashboard'));
        $this->get('/admin')->assertOk()->assertSee('Dashboard');
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_wrong_password_is_rejected_without_revealing_which_part_was_wrong(): void
    {
        $user = $this->admin();
        $this->from('/admin/login')->post('/admin/login', ['email' => $user->email, 'password' => 'nope-nope-nope'])
            ->assertRedirect('/admin/login')->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
        $this->post('/admin/login', ['email' => 'nobody@example.test', 'password' => 'nope-nope-nope'])
            ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_failures(): void
    {
        $user = $this->admin();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong-wrong-1']);
        }
        // even the right password is refused while locked out
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Correct-Horse-9'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_disabled_account_cannot_sign_in(): void
    {
        $user = $this->editor(['is_active' => false]);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Correct-Horse-9'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_account_disabled_during_a_session_is_signed_out(): void
    {
        $user = $this->editor();
        $this->actingAs($user)->get('/admin')->assertOk();
        $user->forceFill(['is_active' => false])->save();
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs($this->admin())->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_responses_are_not_cacheable_or_indexable(): void
    {
        $r = $this->actingAs($this->admin())->get('/admin');
        $r->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('no-store', $r->headers->get('Cache-Control'));
    }

    public function test_editor_cannot_reach_administrator_areas(): void
    {
        $this->actingAs($this->editor());
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->put('/admin/settings', [])->assertForbidden();
        $this->post('/admin/users', [])->assertForbidden();
        // but the day-to-day areas are open
        $this->get('/admin/pages')->assertOk();
        $this->get('/admin/inquiries')->assertOk();
        $this->get('/admin/menu')->assertOk();
    }

    public function test_profile_cannot_be_used_to_become_administrator(): void
    {
        $editor = $this->editor();
        $this->actingAs($editor)->put('/admin/profile', [
            'name' => 'Edith Editor', 'email' => $editor->email, 'role' => 'admin', 'is_active' => 1,
        ]);
        $this->assertSame('editor', $editor->fresh()->role);
    }
}
