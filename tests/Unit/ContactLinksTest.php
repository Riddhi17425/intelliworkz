<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ContactLinksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once dirname(__DIR__, 2) . '/app/helpers.php';
        if (!function_exists('e')) {
            $this->markTestSkipped('Laravel helpers not loaded');
        }
    }

    public function test_phone_numbers_become_tel_links(): void
    {
        $out = contact_html('+91 92740 02997, +91 9427801299');
        $this->assertStringContainsString('<a href="tel:+919274002997">+91 92740 02997</a>', $out);
        $this->assertStringContainsString('<a href="tel:+919427801299">+91 9427801299</a>', $out);
        $this->assertStringContainsString('<a href="tel:+16149992286">+1 614 999 2286</a>', contact_html('+1 614 999 2286'));
    }

    public function test_emails_become_mailto_links(): void
    {
        $this->assertSame('<a href="mailto:hello@intelliworkz.tech">hello@intelliworkz.tech</a>', contact_html('hello@intelliworkz.tech'));
    }

    public function test_existing_links_markup_and_short_numbers_are_left_alone(): void
    {
        $linked = '<a href="tel:+911234567890">+91 12345 67890</a>';
        $this->assertSame($linked, contact_html($linked));
        $this->assertSame('Since 2009 · 380051', contact_html('Since 2009 · 380051'));
        $this->assertSame('', contact_html(null));
    }

    public function test_address_links_to_maps_and_keeps_line_breaks(): void
    {
        $out = map_link('5511, Mercier Street,<br>Lewis Center');
        $this->assertStringContainsString('https://www.google.com/maps/search/?api=1&amp;query=5511%2C%20Mercier%20Street%2C%20Lewis%20Center', $out);
        $this->assertStringContainsString('<br>', $out);
        $this->assertStringContainsString('rel="noopener"', $out);
        $this->assertStringContainsString('href="https://maps.example/x"', map_link('Somewhere', 'https://maps.example/x'));
        $this->assertStringNotContainsString('javascript:', map_link('Somewhere', 'javascript:alert(1)'));
        $this->assertSame('', map_link(''));
    }
}
