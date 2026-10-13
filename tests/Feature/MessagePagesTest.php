<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use Tests\CmsTestCase;

class MessagePagesTest extends CmsTestCase
{
    public function test_unknown_public_addresses_show_the_friendly_404_in_the_site_design(): void
    {
        foreach (['/nothing-here', '/nothing-here.php', '/a/b/c', '/.env', '/vendor/autoload.php', '/wp-login.php'] as $url) {
            $this->get($url)->assertNotFound()
                ->assertSee("We can't find that page", false)
                ->assertSee('class="topbar', false)            // the normal site header
                ->assertSee('Back to home')
                ->assertSee('noindex', false);
        }
        // links to real pages are suggested
        $this->get('/nothing-here')->assertSee('Maybe you were looking for')->assertSee('.php"', false);
    }

    public function test_drafts_look_like_missing_pages_to_visitors(): void
    {
        $this->actingAs($this->admin())->post('/admin/pages', ['title' => 'Secret', 'slug' => 'secret', 'status' => 'draft', 'content' => 'x']);
        auth()->logout();
        $this->get('/secret')->assertNotFound()->assertSee("We can't find that page", false);
    }

    public function test_the_admin_and_json_requests_keep_the_plain_404(): void
    {
        $this->actingAs($this->admin())->get('/admin/pages/nope/edit')->assertNotFound()->assertDontSee("We can't find that page", false);
        $this->getJson('/nothing-here')->assertNotFound()->assertJsonMissing(['kind' => '404']);
    }

    public function test_a_sent_enquiry_leads_to_the_thank_you_page(): void
    {
        $payload = ['name' => 'Asha Patel', 'email' => 'asha@example.com', 'phone' => '+91 98765 43210', 'message' => 'Hello', 'source' => 'contact-us'];

        // with JavaScript: the JSON answer tells the script where to go
        $this->postJson('/inquiries', $payload)->assertOk()->assertJson(['ok' => true, 'redirect' => route('thanks')]);

        // without JavaScript: a normal redirect
        $this->post('/inquiries', ['email' => 'b@example.com'] + $payload)->assertRedirect(route('thanks'));
        $this->assertSame(2, ContactInquiry::count());
    }

    public function test_thank_you_page_greets_the_sender_by_first_name(): void
    {
        $this->post('/inquiries', ['name' => 'Asha Patel', 'email' => 'asha@example.com', 'phone' => '+91 98765 43210'])->assertRedirect(route('thanks'));
        $this->get('/thank-you')->assertOk()->assertSee('Thank you, Asha!', false)->assertSee('noindex', false)->assertSee('Back to home');

        // the greeting is shown once; a later visit has no name (and never echoes markup)
        $this->get('/thank-you')->assertOk()->assertSee('Thank you!', false)->assertDontSee('Asha');
        $this->post('/inquiries', ['name' => '<b>Eve</b><script>x</script>', 'email' => 'e@example.com', 'phone' => '+91 98765 43210']);
        $this->get('/thank-you')->assertDontSee('<script>', false);
    }

    public function test_a_failed_validation_does_not_go_to_the_thank_you_page(): void
    {
        $this->postJson('/inquiries', ['name' => '', 'email' => 'bad'])->assertStatus(422)->assertJsonMissing(['redirect' => route('thanks')]);
        $this->from('/contact-us.php')->post('/inquiries', ['name' => '', 'email' => 'bad'])->assertRedirect('/contact-us.php');
    }
}
