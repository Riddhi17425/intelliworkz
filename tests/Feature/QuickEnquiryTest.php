<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\ContentField;
use App\Support\Content;
use Tests\CmsTestCase;

/** The "Enquire Now" tab on the right edge of every page and the short form it slides in. */
class QuickEnquiryTest extends CmsTestCase
{
    public function test_every_page_has_the_side_button_and_its_form(): void
    {
        foreach (['/', '/about-us', '/datanote'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('class="qe-tab"', $html, $url);
            $this->assertStringContainsString('id="quick-enquiry"', $html, $url);
            $this->assertMatchesRegularExpression('~<form class="qe-form" action="[^"]*/inquiries" method="post" data-inquiry-form~', $html, $url);
        }
        $this->assertStringContainsString('name="source" value="side-about-us"', $this->get('/about-us')->getContent());
    }

    public function test_the_contact_page_keeps_only_its_own_form(): void
    {
        $this->assertStringNotContainsString('class="qe-tab"', $this->get('/contact-us')->getContent());
    }

    public function test_the_admin_can_switch_the_button_off(): void
    {
        ContentField::where('scope', 'global')->where('key', 'enquiry.show')->update(['value' => '0']);
        Content::forget('global');
        $this->assertStringNotContainsString('class="qe-tab"', $this->get('/about-us')->getContent());
    }

    public function test_an_enquiry_sent_from_the_side_form_is_saved(): void
    {
        $this->postJson('/inquiries', [
            'name' => 'Asha Patel', 'email' => 'asha@example.com', 'phone' => '+91 98765 43210',
            'subject' => 'Branding & Creative', 'message' => 'Need a new logo', 'source' => 'side-about-us',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas((new ContactInquiry())->getTable(), ['email' => 'asha@example.com', 'source' => 'side-about-us', 'subject' => 'Branding & Creative']);
    }
}
