<?php

namespace Tests\Feature;

use App\Mail\NewInquiry;
use App\Models\ContactInquiry;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Mail;
use Tests\CmsTestCase;

class InquiryTest extends CmsTestCase
{
    private function payload(array $over = []): array
    {
        return $over + ['name' => 'Asha Patel', 'company' => 'Acme', 'email' => 'asha@example.com', 'phone' => '+91 98765 43210', 'message' => 'Need a brochure design.', 'source' => 'video-editing'];
    }

    /* ---------------- public form */

    public function test_visitor_can_send_an_inquiry_with_ajax(): void
    {
        $this->postJson('/inquiries', $this->payload())->assertOk()->assertJson(['ok' => true]);

        $row = ContactInquiry::sole();
        $this->assertSame('Asha Patel', $row->name);
        $this->assertSame('video-editing', $row->source);
        $this->assertFalse((bool) $row->is_read);
    }

    public function test_inquiry_without_javascript_redirects_back_with_a_message(): void
    {
        $this->from('/contact-us.php')->post('/inquiries', $this->payload())
            ->assertRedirect('/contact-us.php#form-result')->assertSessionHas('inquiry_status');
        $this->get('/contact-us.php')->assertOk(); // page still renders with the flash
    }

    public function test_validation_errors_are_reported_and_nothing_is_saved(): void
    {
        $this->postJson('/inquiries', ['name' => '', 'email' => 'not-an-email', 'phone' => 'abc'])
            ->assertStatus(422)->assertJsonValidationErrors(['name', 'email', 'phone']);
        $this->assertSame(0, ContactInquiry::count());
    }

    public function test_contact_number_is_required_and_must_look_like_a_number(): void
    {
        $this->postJson('/inquiries', $this->payload(['phone' => '']))
            ->assertStatus(422)->assertJsonValidationErrors(['phone'])
            ->assertJsonPath('errors.phone.0', 'Please enter your contact number.');
        foreach (['------', '123', '+91 98765 43210 12345 6789', 'abc12345'] as $bad) {
            $this->postJson('/inquiries', $this->payload(['phone' => $bad]))->assertStatus(422)->assertJsonValidationErrors(['phone']);
        }
        \Illuminate\Support\Facades\Cache::flush();   // the form is rate-limited (5 per minute)
        foreach (['+91 98765 43210', '9876543210', '(022) 2345-6789'] as $good) {
            $this->postJson('/inquiries', $this->payload(['phone' => $good]))->assertOk();
        }
        $this->assertSame(3, ContactInquiry::count());
    }

    public function test_html_in_the_message_is_stripped_before_saving(): void
    {
        $this->postJson('/inquiries', $this->payload(['name' => '<script>alert(1)</script>Eve', 'message' => "Hi <b>there</b><img src=x onerror=alert(1)>\nSecond line"]))->assertOk();
        $row = ContactInquiry::sole();
        $this->assertStringNotContainsString('<', $row->name . $row->message);
        $this->assertStringContainsString("\nSecond line", $row->message);
    }

    public function test_filled_honeypot_is_silently_dropped(): void
    {
        $this->postJson('/inquiries', $this->payload(['website' => 'http://spam.example']))->assertOk()->assertJson(['ok' => true]);
        $this->assertSame(0, ContactInquiry::count());
    }

    public function test_source_cannot_carry_markup(): void
    {
        $this->postJson('/inquiries', $this->payload(['source' => '"><script>']))->assertStatus(422)->assertJsonValidationErrors('source');
    }

    public function test_form_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/inquiries', $this->payload())->assertOk();
        }
        $this->postJson('/inquiries', $this->payload())->assertStatus(429);
        $this->assertSame(5, ContactInquiry::count());
    }

    public function test_notification_mail_is_sent_only_when_an_address_is_configured(): void
    {
        Mail::fake();
        $this->postJson('/inquiries', $this->payload())->assertOk();
        Mail::assertNothingSent();

        $row = SiteSetting::where('key', 'contact_email')->firstOrFail();
        $row->value = 'sales@example.com';
        $row->save();

        $this->postJson('/inquiries', $this->payload(['name' => 'Second Person']))->assertOk();
        Mail::assertSent(NewInquiry::class, fn ($m) => $m->hasTo('sales@example.com'));
    }

    public function test_every_public_form_posts_to_the_inquiry_endpoint_with_csrf_and_honeypot(): void
    {
        foreach (['video-editing', 'contact-us', 'seo-services', 'ppt-design'] as $slug) {
            $this->get("/{$slug}.php")->assertOk()
                ->assertSee('/inquiries" method="post" data-inquiry-form', false)->assertSee('name="_token"', false)
                ->assertSee('name="website"', false)->assertSee('name="email"', false);
        }
    }

    /* ---------------- admin */

    public function test_inquiry_list_requires_sign_in(): void
    {
        $this->get('/admin/inquiries')->assertRedirect(route('admin.login'));
    }

    public function test_opening_an_inquiry_marks_it_read_and_it_can_be_toggled_back(): void
    {
        $this->postJson('/inquiries', $this->payload())->assertOk();
        $row = ContactInquiry::sole();
        $this->actingAs($this->editor());

        $this->get('/admin/inquiries')->assertOk()->assertSee('Asha Patel');
        $this->get("/admin/inquiries/{$row->id}")->assertOk()->assertSee('Need a brochure design.');
        $this->assertTrue((bool) $row->fresh()->is_read);

        $this->patch("/admin/inquiries/{$row->id}")->assertRedirect();
        $this->assertFalse((bool) $row->fresh()->is_read);
    }

    public function test_message_text_is_escaped_in_the_admin_screen(): void
    {
        // inserted directly, as if a bad value had slipped past the controller
        $row = ContactInquiry::create(['name' => 'X', 'email' => 'x@example.com', 'message' => '<script>alert(1)</script>']);
        $this->actingAs($this->admin())->get("/admin/inquiries/{$row->id}")->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_filters_and_search(): void
    {
        $this->postJson('/inquiries', $this->payload(['name' => 'Alpha One', 'source' => 'video-editing']));
        $this->postJson('/inquiries', $this->payload(['name' => 'Beta Two', 'source' => 'seo-services', 'email' => 'beta@example.com']));
        ContactInquiry::where('name', 'Alpha One')->update(['is_read' => true]);
        $this->actingAs($this->editor());

        $this->get('/admin/inquiries?q=Beta')->assertSee('Beta Two')->assertDontSee('Alpha One');
        $this->get('/admin/inquiries?status=unread')->assertSee('Beta Two')->assertDontSee('Alpha One');
        $this->get('/admin/inquiries?status=read')->assertSee('Alpha One')->assertDontSee('Beta Two');
        $this->get('/admin/inquiries?source=seo-services')->assertSee('Beta Two')->assertDontSee('Alpha One');
        $this->get('/admin/inquiries?from=2999-01-01')->assertDontSee('Alpha One')->assertDontSee('Beta Two');
        $this->get('/admin/inquiries?sort=evil;drop&dir=sideways')->assertOk();   // unknown sort falls back safely
    }

    public function test_editor_may_not_delete_but_admin_may(): void
    {
        $this->postJson('/inquiries', $this->payload());
        $row = ContactInquiry::sole();

        $this->actingAs($this->editor())->delete("/admin/inquiries/{$row->id}")->assertForbidden();
        $this->assertSame(1, ContactInquiry::count());

        $this->actingAs($this->admin())->delete("/admin/inquiries/{$row->id}")->assertRedirect(route('admin.inquiries.index'));
        $this->assertSame(0, ContactInquiry::count());
    }

    public function test_bulk_actions(): void
    {
        foreach (['A One', 'B Two', 'C Three'] as $n) {
            $this->postJson('/inquiries', $this->payload(['name' => $n]));
        }
        $ids = ContactInquiry::pluck('id')->all();

        $this->actingAs($this->editor());
        $this->post('/admin/inquiries/bulk', ['action' => 'read', 'ids' => $ids])->assertRedirect();
        $this->assertSame(3, ContactInquiry::where('is_read', true)->count());
        $this->post('/admin/inquiries/bulk', ['action' => 'unread', 'ids' => [$ids[0]]]);
        $this->assertSame(1, ContactInquiry::where('is_read', false)->count());
        $this->post('/admin/inquiries/bulk', ['action' => 'read', 'ids' => []])->assertSessionHasErrors('ids');
        $this->post('/admin/inquiries/bulk', ['action' => 'delete', 'ids' => $ids])->assertForbidden();
        $this->assertSame(3, ContactInquiry::count());

        $this->actingAs($this->admin())->post('/admin/inquiries/bulk', ['action' => 'delete', 'ids' => $ids])->assertRedirect();
        $this->assertSame(0, ContactInquiry::count());
    }

    public function test_csv_export_neutralises_spreadsheet_formulas(): void
    {
        $this->postJson('/inquiries', $this->payload(['name' => '=HYPERLINK("http://evil")', 'message' => '+cmd']));
        $csv = $this->actingAs($this->editor())->get('/admin/inquiries/export')->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')->streamedContent();

        $this->assertStringContainsString('Received,Name,Company', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+cmd", $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
    }
}
