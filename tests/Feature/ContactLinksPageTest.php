<?php

namespace Tests\Feature;

use Tests\CmsTestCase;

class ContactLinksPageTest extends CmsTestCase
{
    public function test_footer_offices_link_phone_numbers_and_addresses_on_every_page(): void
    {
        foreach (['/', '/about-us.php', '/seo-services.php'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('href="tel:+16149992286"', $html, $url);
            $this->assertStringContainsString('href="tel:+971563435362"', $html, $url);
            $this->assertStringContainsString('https://www.google.com/maps/search/?api=1&amp;query=5511%2C%20Mercier%20Street', $html, $url);
        }
    }

    public function test_contact_page_links_numbers_email_and_addresses(): void
    {
        $html = $this->get('/contact-us.php')->assertOk()->getContent();
        $this->assertStringContainsString('href="tel:+919274002997"', $html);
        $this->assertStringContainsString('href="tel:+919427801299"', $html);
        $this->assertStringContainsString('href="mailto:hello@intelliworkz.tech"', $html);
        $this->assertStringContainsString('class="addr-link"', $html);
    }

    public function test_every_showcase_card_has_the_red_case_study_button(): void
    {
        $html = $this->get('/seo-services.php')->assertOk()->getContent();
        $buttons = substr_count($html, 'class="text-link text-link--filled"');
        $this->assertGreaterThan(1, $buttons);
        $this->assertSame($buttons, substr_count($html, 'View Case Study'));
    }
}
