<?php

namespace Tests\Feature;

use Tests\CmsTestCase;

class CleanUrlsTest extends CmsTestCase
{
    private function live(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://intelliworkz.com']);
    }

    public function test_http_and_www_go_to_the_one_real_address(): void
    {
        $this->live();
        $this->get('http://intelliworkz.com/about-us?x=1')->assertRedirect('https://intelliworkz.com/about-us?x=1')->assertStatus(301);
        $this->get('https://www.intelliworkz.com/about-us')->assertRedirect('https://intelliworkz.com/about-us')->assertStatus(301);
        $this->get('http://www.intelliworkz.com/blog')->assertRedirect('https://intelliworkz.com/blog');
        $this->get('https://intelliworkz.com/about-us')->assertOk();
    }

    public function test_proxy_that_already_did_https_is_not_redirected_in_a_loop(): void
    {
        $this->live();
        $this->get('http://intelliworkz.com/about-us', ['X-Forwarded-Proto' => 'https'])->assertOk();
    }

    public function test_localhost_ip_and_other_domains_are_never_redirected(): void
    {
        $this->live();
        $this->get('http://127.0.0.1:8000/about-us')->assertOk();
        $this->get('http://localhost/about-us')->assertOk();
        $this->get('http://staging.example.test/about-us')->assertOk();
    }

    public function test_links_to_the_same_site_are_root_based_and_there_is_no_base_tag(): void
    {
        $html = $this->get('/blog')->assertOk()->getContent();
        $this->assertStringNotContainsString('<base ', $html);
        $this->assertDoesNotMatchRegularExpression('~<(?:a|img|form)\b[^>]*\b(?:href|src|action)="https?://(?:localhost|127\.0\.0\.1)~', $html);
        $this->assertMatchesRegularExpression('~<form[^>]*action="/inquiries"~', $this->get('/contact-us')->getContent());
        $this->assertMatchesRegularExpression('~<link rel="canonical" href="' . preg_quote(url('/blog'), '~') . '"~', $html);   // canonical stays absolute
    }

    public function test_capital_letters_in_an_address_go_to_the_lower_case_page(): void
    {
        $this->get('/About-Us')->assertRedirect(url('/about-us'))->assertStatus(301);
        $this->get('/BLOG')->assertRedirect(url('/blog'))->assertStatus(301);
        $this->get('/Nothing-Here-Xyz')->assertNotFound();
    }

    public function test_every_response_carries_the_basic_security_headers(): void
    {
        $r = $this->get('/about-us');
        $r->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->get('/nothing-here-xyz')->assertNotFound()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertFalse($r->headers->has('X-Powered-By'));
    }

    public function test_buttons_to_the_contact_page_land_on_its_form_but_plain_links_do_not(): void
    {
        $home = $this->get('/')->assertOk()->getContent();
        // the top bar "Start a Project" button and the call-to-action buttons go straight to the enquiry form
        $this->assertMatchesRegularExpression('~<a href="/contact-us#contact-form" class="btn btn--header~', $home);
        $this->assertMatchesRegularExpression('~<a[^>]*class="btn btn--cta[^"]*"[^>]*href="/contact-us#contact-form"|<a[^>]*href="/contact-us#contact-form"[^>]*class="btn btn--cta~', $home);
        // no button is left pointing at the top of the contact page
        $this->assertDoesNotMatchRegularExpression('~<a(?=[^>]*class="[^"]*btn)[^>]*href="/contact-us"~', $home);
        // text links in the menu / footer stay as they were
        $this->assertMatchesRegularExpression('~<a href="/contact-us">~', $home);
        // the target exists on the contact page
        $this->get('/contact-us')->assertOk()->assertSee('id="contact-form"', false);
    }
}
