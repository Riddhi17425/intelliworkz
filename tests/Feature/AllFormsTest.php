<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\Page;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\CmsTestCase;

/** Every enquiry form on every published page is complete and really saves what a visitor sends. */
class AllFormsTest extends CmsTestCase
{
    /** @return array<string, array<int, \DOMElement>> url => its enquiry forms */
    private function formsOnEveryPage(): array
    {
        $out = [];
        foreach (Page::published()->get() as $page) {
            $url = $page->slug === 'index' ? '/' : '/' . $page->slug;
            $res = $this->get($url);
            if (!$res->isSuccessful()) {
                continue;
            }
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8"?>' . $res->getContent());
            foreach ((new \DOMXPath($dom))->query('//form[@data-inquiry-form]') as $form) {
                $out[$url][] = $form;
            }
        }
        return $out;
    }

    public function test_every_enquiry_form_is_complete(): void
    {
        $all = $this->formsOnEveryPage();
        $this->assertGreaterThan(20, count($all), 'pages with a form');

        foreach ($all as $url => $forms) {
            foreach ($forms as $form) {
                $where = $url . ' form.' . $form->getAttribute('class');
                $xp = new \DOMXPath($form->ownerDocument);
                $field = fn (string $name) => $xp->query('.//*[@name="' . $name . '"]', $form)->item(0);

                $this->assertSame('post', strtolower($form->getAttribute('method')), $where);
                $this->assertStringEndsWith('/inquiries', $form->getAttribute('action'), $where);
                $this->assertNotEmpty($field('_token')?->getAttribute('value'), $where . ' has no CSRF token');
                $this->assertMatchesRegularExpression('/^[a-z0-9-]{1,120}$/', (string) $field('source')?->getAttribute('value'), $where . ' source');
                $this->assertNotNull($field('website'), $where . ' has no spam trap');
                foreach (['name', 'email', 'phone', 'message'] as $n) {
                    $this->assertNotNull($field($n), "$where has no $n field");
                }
                $this->assertSame('email', $field('email')->getAttribute('type'), $where . ' email field type');

                $button = $xp->query('.//button[@type="submit"]', $form)->item(0);
                $this->assertNotNull($button, $where . ' has no send button');
                $this->assertNotSame('', trim($button->textContent), $where . ' send button has no text');

                // every visible field has a label text a visitor can read
                foreach ($xp->query('.//label', $form) as $label) {
                    $this->assertNotSame('', trim($xp->query('./span', $label)->item(0)?->textContent ?? ''), $where . ' has a field without a label');
                }
            }
        }
    }

    public function test_every_enquiry_form_saves_what_is_sent(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $sent = 0;
        foreach ($this->formsOnEveryPage() as $url => $forms) {
            foreach ($forms as $i => $form) {
                $xp = new \DOMXPath($form->ownerDocument);
                $data = [];
                foreach ($xp->query('.//input[@name] | .//textarea[@name] | .//select[@name]', $form) as $el) {
                    $data[$el->getAttribute('name')] = $el->getAttribute('value');
                }
                $email = 'form' . (++$sent) . '@example.com';
                $data = array_merge($data, [
                    'name' => 'Form Test', 'email' => $email, 'phone' => '+91 98765 43210',
                    'company' => 'Test Co', 'subject' => 'Testing', 'message' => "Line one\nLine two", 'website' => '',
                ]);
                $this->withSession(['_token' => $data['_token'] ?? ''])
                    ->postJson('/inquiries', $data)->assertOk()->assertJson(['ok' => true]);

                $saved = ContactInquiry::where('email', $email)->first();
                $this->assertNotNull($saved, "$url form $i did not save");
                $this->assertSame($data['source'], $saved->source, "$url form $i source");
                $this->assertSame("Line one\nLine two", $saved->message);
            }
        }
        $this->assertGreaterThan(30, $sent);
    }

    public function test_a_spam_bot_filling_the_hidden_field_is_thanked_but_not_saved(): void
    {
        $this->postJson('/inquiries', ['name' => 'Bot', 'email' => 'bot@example.com', 'phone' => '+1 555 010 0100', 'website' => 'http://spam.example'])
            ->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseMissing((new ContactInquiry())->getTable(), ['email' => 'bot@example.com']);
    }
}
