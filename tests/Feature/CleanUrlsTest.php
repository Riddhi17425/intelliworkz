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
}
