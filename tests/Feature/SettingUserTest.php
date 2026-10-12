<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\CmsTestCase;

class SettingUserTest extends CmsTestCase
{
    private function settingsPayload(array $over = []): array
    {
        $current = SiteSetting::where('type', '!=', 'image')->pluck('value', 'key')->all();
        return ['s' => $over + $current];
    }

    /* ---------------- company settings */

    public function test_settings_screen_shows_every_group(): void
    {
        $this->actingAs($this->admin())->get('/admin/settings')->assertOk()
            ->assertSee('Company name')->assertSee('Header logo')->assertSee('Favicon')->assertSee('Facebook')->assertSee('Default meta description');
    }

    public function test_company_details_appear_on_every_public_page(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', $this->settingsPayload([
            'company_name' => 'Acme Creative Pvt Ltd',
            'copyright' => 'Copyright © 2030 Acme. All rights reserved.',
            'footer_about' => "First line about us.\nSecond line.",
            'social_facebook' => 'https://facebook.com/acme',
            'social_linkedin' => 'https://linkedin.com/company/acme',
            'seo_description' => 'Default description for pages without their own.',
        ]))->assertRedirect()->assertSessionHas('success');
        auth()->logout();

        foreach (['/', '/about-us.php', '/contact-us.php'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Copyright © 2030 Acme. All rights reserved.')
                ->assertSee('First line about us.<br />', false)
                ->assertSee('href="https://facebook.com/acme"', false)
                ->assertSee('href="https://linkedin.com/company/acme"', false)
                ->assertSee('alt="Acme Creative Pvt Ltd"', false)
                ->assertSee('content="Default description for pages without their own."', false);
        }
    }

    public function test_page_description_wins_over_the_default_one(): void
    {
        SiteSetting::where('key', 'seo_description')->update(['value' => 'Default one.']);
        SiteSetting::flush();
        \App\Models\Page::where('slug', 'about-us')->update(['meta_description' => 'Own description.']);
        $this->get('/about-us.php')->assertSee('content="Own description."', false)->assertDontSee('Default one.');
        $this->get('/contact-us.php')->assertSee('content="Default one."', false);
    }

    public function test_empty_social_link_hides_the_icon(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', $this->settingsPayload(['social_instagram' => '']))->assertSessionHasNoErrors();
        auth()->logout();
        $this->get('/')->assertDontSee('aria-label="Instagram"', false)->assertSee('aria-label="LinkedIn"', false);
    }

    public function test_logo_and_favicon_upload_replace_and_remove(): void
    {
        $this->actingAs($this->admin());
        $svg = UploadedFile::fake()->createWithContent('brand.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><script>alert(1)</script><rect width="10" height="10" onclick="x()"/></svg>');
        $this->put('/admin/settings', ['s' => $this->settingsPayload()['s'] + ['logo_header' => $svg, 'favicon' => UploadedFile::fake()->create('fav.png', 8, 'image/png')]])
            ->assertSessionHasNoErrors();

        $logo = SiteSetting::get('logo_header');
        $fav = SiteSetting::get('favicon');
        $this->assertStringStartsWith('uploads/branding/', $logo);
        $this->assertStringStartsWith('uploads/branding/', $fav);

        // the SVG on disk was sanitised
        $stored = \Storage::disk('uploads')->get(substr($logo, strlen('uploads/')));
        $this->assertStringNotContainsString('script', $stored);
        $this->assertStringNotContainsString('onclick', $stored);
        $this->assertStringContainsString('<rect', $stored);

        auth()->logout();
        $this->get('/')->assertSee('src="' . $logo . '"', false)->assertSee('href="' . $fav . '"', false);

        // remove -> falls back to the original asset in the templates
        $this->actingAs($this->admin())->put('/admin/settings', $this->settingsPayload() + ['remove_logo_header' => 1])->assertSessionHasNoErrors();
        $this->assertNull(SiteSetting::get('logo_header'));
        \Storage::disk('uploads')->assertMissing(substr($logo, strlen('uploads/')));
        auth()->logout();
        $this->get('/')->assertSee('assets/icons/logo.svg');
    }

    public function test_favicon_may_not_be_an_svg_and_bad_files_are_rejected(): void
    {
        $this->actingAs($this->admin());
        $base = $this->settingsPayload()['s'];
        $this->put('/admin/settings', ['s' => $base + ['favicon' => UploadedFile::fake()->create('f.svg', 1, 'image/svg+xml')]])->assertSessionHasErrors('s.favicon');
        $this->put('/admin/settings', ['s' => $base + ['logo_header' => UploadedFile::fake()->create('x.php', 1, 'application/x-php')]])->assertSessionHasErrors('s.logo_header');
        $this->put('/admin/settings', ['s' => $base + ['seo_image' => UploadedFile::fake()->create('big.png', 6000, 'image/png')]])->assertSessionHasErrors('s.seo_image');
    }

    public function test_svg_with_entities_or_garbage_is_refused_cleanly(): void
    {
        $this->actingAs($this->admin());
        $base = $this->settingsPayload()['s'];
        $xxe = UploadedFile::fake()->createWithContent('x.svg', '<!DOCTYPE svg [<!ENTITY a SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg">&a;</svg>');
        $this->put('/admin/settings', ['s' => $base + ['logo_header' => $xxe]])->assertSessionHasErrors('s.logo_header');
        $junk = UploadedFile::fake()->createWithContent('y.svg', 'this is not xml');
        $this->put('/admin/settings', ['s' => $base + ['logo_header' => $junk]])->assertSessionHasErrors('s.logo_header');
        $this->assertStringNotContainsString('uploads/', (string) SiteSetting::get('logo_header'));
    }

    public function test_setting_validation(): void
    {
        $this->actingAs($this->admin());
        $this->put('/admin/settings', $this->settingsPayload(['contact_email' => 'nope']))->assertSessionHasErrors('s.contact_email');
        foreach (['javascript:alert(1)', 'ftp://x', 'facebook.com/x'] as $bad) {
            $this->put('/admin/settings', $this->settingsPayload(['social_facebook' => $bad]))->assertSessionHasErrors('s.social_facebook');
        }
        $this->put('/admin/settings', $this->settingsPayload(['company_name' => str_repeat('a', 400)]))->assertSessionHasErrors('s.company_name');
    }

    public function test_unknown_setting_keys_cannot_be_created(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', $this->settingsPayload(['evil_key' => 'x', 'mail_password' => 'x']))->assertSessionHasNoErrors();
        $this->assertNull(SiteSetting::where('key', 'evil_key')->first());
        $this->assertNull(SiteSetting::where('key', 'mail_password')->first());
    }

    public function test_markup_in_settings_is_neutralised(): void
    {
        $this->actingAs($this->admin())->put('/admin/settings', $this->settingsPayload([
            'company_name' => '<script>alert(1)</script>Acme', 'footer_about' => '<img src=x onerror=alert(1)>About',
        ]));
        auth()->logout();
        $this->get('/')->assertOk()->assertDontSee('<script>alert(1)</script>Acme', false)->assertDontSee('onerror=alert', false);
    }

    /* ---------------- users */

    public function test_admin_can_create_edit_and_delete_users(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/users', ['name' => 'New Editor', 'email' => 'new@example.com', 'role' => 'editor', 'password' => 'a-long-enough-pass', 'password_confirmation' => 'a-long-enough-pass', 'is_active' => 1])
            ->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('editor', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotSame('a-long-enough-pass', $user->password);        // hashed
        $this->assertTrue(\Hash::check('a-long-enough-pass', $user->password));

        $this->get('/admin/users?q=new@')->assertSee('New Editor');
        $this->put("/admin/users/{$user->id}", ['name' => 'Renamed', 'email' => 'new@example.com', 'role' => 'editor', 'is_active' => 1])->assertRedirect();
        $this->assertSame('Renamed', $user->fresh()->name);
        $this->assertTrue(\Hash::check('a-long-enough-pass', $user->fresh()->password), 'a blank password field must keep the old password');

        $this->delete("/admin/users/{$user->id}")->assertRedirect();
        $this->assertNull(User::find($user->id));
    }

    public function test_user_validation(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $ok = ['name' => 'X Y', 'email' => 'xy@example.com', 'role' => 'editor', 'password' => 'a-long-enough-pass', 'password_confirmation' => 'a-long-enough-pass'];
        $this->post('/admin/users', ['email' => $admin->email] + $ok)->assertSessionHasErrors('email');                    // duplicate
        $this->post('/admin/users', ['password' => 'short', 'password_confirmation' => 'short'] + $ok)->assertSessionHasErrors('password');
        $this->post('/admin/users', ['password_confirmation' => 'different-different'] + $ok)->assertSessionHasErrors('password');
        $this->post('/admin/users', ['role' => 'superuser'] + $ok)->assertSessionHasErrors('role');
        $this->post('/admin/users', ['email' => 'not-an-email'] + $ok)->assertSessionHasErrors('email');
        $this->assertSame(1, User::count());
    }

    public function test_the_last_active_administrator_is_protected(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $this->delete("/admin/users/{$admin->id}")->assertSessionHas('error');                    // own account
        $this->put("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'editor', 'is_active' => 1])->assertSessionHas('error');
        $this->put("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'admin', 'is_active' => 0])->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertTrue($admin->fresh()->is_active);

        // with a second administrator the first may be demoted
        $second = $this->admin();
        $this->put("/admin/users/{$second->id}", ['name' => $second->name, 'email' => $second->email, 'role' => 'editor', 'is_active' => 1])->assertSessionMissing('error');
        $this->assertSame('editor', $second->fresh()->role);
    }

    public function test_profile_password_change_needs_the_current_password(): void
    {
        $user = $this->editor();
        $this->actingAs($user);
        $base = ['name' => $user->name, 'email' => $user->email];
        $this->put('/admin/profile', $base + ['password' => 'brand-new-password', 'password_confirmation' => 'brand-new-password'])->assertSessionHasErrors('current_password');
        $this->put('/admin/profile', $base + ['current_password' => 'wrong', 'password' => 'brand-new-password', 'password_confirmation' => 'brand-new-password'])->assertSessionHasErrors('current_password');
        $this->put('/admin/profile', $base + ['current_password' => 'Correct-Horse-9', 'password' => 'brand-new-password', 'password_confirmation' => 'brand-new-password'])->assertSessionHasNoErrors();
        $this->assertTrue(\Hash::check('brand-new-password', $user->fresh()->password));
    }

    public function test_activity_log_records_actions_and_is_viewable(): void
    {
        $this->actingAs($this->admin());
        $this->put('/admin/settings', $this->settingsPayload(['company_name' => 'Logged Co']));
        $this->get('/admin/activity')->assertOk()->assertSee('Changed site settings');
    }

    public function test_dashboard_shows_real_numbers(): void
    {
        \App\Models\ContactInquiry::create(['name' => 'Dash Person', 'email' => 'd@example.com']);
        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertSee('Dash Person');
    }
}
