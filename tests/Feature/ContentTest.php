<?php

namespace Tests\Feature;

use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\Page;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Tests\CmsTestCase;

class ContentTest extends CmsTestCase
{
    /** Collect what a browser would submit for the content form on $url (files excluded). */
    private function formData(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8"?>' . $html);
        $xp = new DOMXPath($doc);
        $pairs = [];
        foreach ($xp->query('//form[@data-content-form]//input|//form[@data-content-form]//textarea|//form[@data-content-form]//select') as $el) {
            $name = $el->getAttribute('name');
            if ($name === '' || in_array($name, ['_token', '_method'], true) || $el->hasAttribute('disabled')) {
                continue;
            }
            $tag = $el->nodeName;
            $type = strtolower($el->getAttribute('type'));
            if ($tag === 'input') {
                if (in_array($type, ['file', 'submit', 'button'], true)) {
                    continue;
                }
                if (in_array($type, ['checkbox', 'radio'], true) && !$el->hasAttribute('checked')) {
                    continue;
                }
                $pairs[] = rawurlencode($name) . '=' . rawurlencode($el->getAttribute('value'));
            } elseif ($tag === 'textarea') {
                $pairs[] = rawurlencode($name) . '=' . rawurlencode($el->textContent);
            } else {
                $val = '';
                foreach ($xp->query('.//option', $el) as $o) {
                    if ($o->hasAttribute('selected') || $val === '') {
                        $val = $o->getAttribute('value');
                    }
                }
                $pairs[] = rawurlencode($name) . '=' . rawurlencode($val);
            }
        }
        parse_str(implode('&', $pairs), $data);
        return $data;
    }

    public function test_every_page_editor_opens(): void
    {
        $this->actingAs($this->editor());
        foreach (Page::where('is_system', true)->get() as $page) {
            $this->get("/admin/pages/{$page->slug}/content")->assertOk()->assertSee('Save changes');
        }
        $this->get('/admin/shared')->assertOk();
        $this->get('/admin/pages/create')->assertOk();
    }

    public function test_saving_an_untouched_form_changes_nothing_on_any_page(): void
    {
        $this->actingAs($this->editor());
        $before = ContentList::orderBy('id')->get()->map(fn ($l) => $l->scope . '.' . $l->key . ':' . json_encode($l->items))->all();
        $fields = ContentField::orderBy('id')->pluck('value', 'id')->all();

        $targets = Page::where('is_system', true)->pluck('slug')->map(fn ($s) => ["/admin/pages/{$s}/content", $s])->all();
        $targets[] = ['/admin/shared', 'shared'];

        foreach ($targets as [$url, $label]) {
            $data = $this->formData($url);
            $this->assertNotEmpty($data, "{$label}: no form data collected");
            $snapshot = fn () => ContentField::pluck('value', 'id')->map(fn ($v) => 'F:' . $v)->all()
                + ContentList::get()->mapWithKeys(fn ($l) => ['L' . $l->id . ' ' . $l->scope . '.' . $l->key => json_encode($l->items)])->all();
            $was = $snapshot();
            $resp = $this->from($url)->put($url, $data);
            $resp->assertRedirect($url);
            $now = $snapshot();
            $diff = array_keys(array_diff_assoc($now, $was));
            $detail = collect($diff)->map(fn ($k) => "\n  {$k}\n    was: " . mb_substr((string) $was[$k], 0, 700) . "\n    now: " . mb_substr((string) $now[$k], 0, 700))->implode('');
            $this->assertSame([], $diff, "{$label}: an untouched form changed:" . $detail);
            $this->assertSame('Nothing was changed.', session('success'), "{$label}: an untouched form reported changes");
            $resp->assertSessionHasNoErrors();
        }

        $this->assertSame($before, ContentList::orderBy('id')->get()->map(fn ($l) => $l->scope . '.' . $l->key . ':' . json_encode($l->items))->all(), 'lists were altered by a no-op save');
        $this->assertSame($fields, ContentField::orderBy('id')->pluck('value', 'id')->all(), 'fields were altered by a no-op save');
    }

    public function test_editing_a_field_changes_the_live_page_immediately(): void
    {
        $field = ContentField::where('scope', 'about-us')->where('type', 'text')->orderBy('sort')->firstOrFail();
        $this->get('/about-us.php')->assertOk();     // prime any cache

        $this->actingAs($this->editor())->put('/admin/pages/about-us/content', ['fields' => [$field->id => 'Brand New Heading']])
            ->assertSessionHas('success');
        auth()->logout();

        $this->get('/about-us.php')->assertOk()->assertSee('Brand New Heading');
        $this->assertSame('Brand New Heading', $field->fresh()->value);
    }

    public function test_a_field_can_be_hidden_and_shown_again_without_losing_its_text(): void
    {
        $field = ContentField::where('scope', 'about-us')->where('type', 'text')->orderBy('sort')->firstOrFail();
        $field->update(['value' => 'Unique Heading 4821']);
        \App\Support\Content::forget('about-us');
        $this->get('/about-us.php')->assertOk()->assertSee('Unique Heading 4821');

        $this->actingAs($this->editor())->put('/admin/pages/about-us/content', ['hidden' => [$field->id => '1']])->assertSessionHas('success');
        auth()->logout();
        $this->get('/about-us.php')->assertOk()->assertDontSee('Unique Heading 4821');
        $this->assertSame('Unique Heading 4821', $field->fresh()->value);

        $this->actingAs($this->editor())->put('/admin/pages/about-us/content', ['hidden' => [$field->id => '0']]);
        auth()->logout();
        $this->get('/about-us.php')->assertSee('Unique Heading 4821');
    }

    public function test_paragraphs_can_be_added_after_a_field_and_deleted_again(): void
    {
        $field = ContentField::where('scope', 'about-us')->where('type', 'textarea')->orderBy('sort')->firstOrFail();
        $this->actingAs($this->editor())->put('/admin/pages/about-us/content', ['new_fields' => [$field->id => ['An extra paragraph 7391', '   ']]])
            ->assertSessionHas('success');
        auth()->logout();

        $extra = ContentField::where('scope', 'about-us')->where('key', $field->key . '~1')->firstOrFail();
        $this->assertSame($field->section, $extra->section);
        $this->assertSame(1, ContentField::where('scope', 'about-us')->where('key', 'like', $field->key . '~%')->count(), 'a blank paragraph must not be saved');
        $this->get('/about-us.php')->assertOk()->assertSee('An extra paragraph 7391');

        $this->actingAs($this->editor())->get('/admin/pages/about-us/content')->assertOk()->assertSee('An extra paragraph 7391')->assertSee('Delete this added paragraph');
        $this->actingAs($this->editor())->put('/admin/pages/about-us/content', ['delete_fields' => [$extra->id => '1']]);
        auth()->logout();
        $this->assertNull(ContentField::find($extra->id));
        $this->get('/about-us.php')->assertDontSee('An extra paragraph 7391');
    }

    public function test_original_fields_cannot_be_deleted_only_hidden(): void
    {
        $field = ContentField::where('scope', 'about-us')->where('type', 'text')->orderBy('sort')->firstOrFail();
        $this->actingAs($this->editor())->put('/admin/pages/about-us/content', ['delete_fields' => [$field->id => '1']]);
        $this->assertNotNull(ContentField::find($field->id));
    }
    public function test_script_and_event_handlers_are_removed_from_copy(): void
    {
        $field = ContentField::where('scope', 'index')->whereIn('type', ['text', 'html', 'textarea'])->orderBy('sort')->firstOrFail();
        $this->actingAs($this->editor())->put('/admin/pages/index/content', ['fields' => [$field->id => 'Hello <strong onclick="steal()">world</strong><script>alert(1)</script> <a href="javascript:alert(2)">x</a>']]);
        $stored = $field->fresh()->value;
        foreach (['<script', 'onclick', 'javascript:', 'alert('] as $needle) {
            $this->assertStringNotContainsString($needle, $stored);
        }
        $this->assertStringContainsString('<strong>world</strong>', $stored);
    }

    public function test_plain_text_with_ampersand_and_angle_brackets_is_stored_escaped(): void
    {
        $field = ContentField::where('scope', 'index')->where('type', 'text')->orderBy('sort')->firstOrFail();
        $this->actingAs($this->editor())->put('/admin/pages/index/content', ['fields' => [$field->id => 'R&D < 5 years']]);
        $this->assertSame('R&amp;D &lt; 5 years', $field->fresh()->value);
        $this->get('/')->assertOk()->assertSee('R&amp;D &lt; 5 years', false);
    }

    public function test_unsafe_link_targets_are_rejected(): void
    {
        $field = ContentField::where('scope', 'contact-us')->where('type', 'url')->orderBy('sort')->firstOrFail();
        $original = $field->value;
        $this->actingAs($this->editor());
        foreach (['javascript:alert(1)', 'data:text/html,<script>1</script>', 'vbscript:x', ' JaVaScRiPt:alert(1)'] as $bad) {
            $this->put('/admin/pages/contact-us/content', ['fields' => [$field->id => $bad]])->assertSessionHasErrors("fields.{$field->id}");
        }
        $this->assertSame($original, $field->fresh()->value);
        foreach (['https://example.com/x', 'contact-us.php', '#', 'mailto:a@b.co', 'tel:+911234567890'] as $ok) {
            $this->put('/admin/pages/contact-us/content', ['fields' => [$field->id => $ok]])->assertSessionHasNoErrors();
            $this->assertSame($ok, $field->fresh()->value);
        }
    }

    public function test_images_can_be_replaced_and_removed(): void
    {
        $field = ContentField::where('scope', 'contact-us')->where('type', 'image')->orderBy('sort')->firstOrFail();
        $legacy = $field->value;
        $this->actingAs($this->editor());

        $this->put('/admin/pages/contact-us/content', ['images' => [$field->id => UploadedFile::fake()->create('team.png', 20, 'image/png')]])->assertSessionHasNoErrors();
        $path = $field->fresh()->value;
        $this->assertStringStartsWith('uploads/images/', $path);
        \Storage::disk('uploads')->assertExists(substr($path, strlen('uploads/')));
        $this->get('/contact-us.php')->assertSee($path, false);

        // wrong type is refused and the current picture stays
        $this->put('/admin/pages/contact-us/content', ['images' => [$field->id => UploadedFile::fake()->create('x.php', 1, 'application/x-php')]])->assertSessionHasErrors("images.{$field->id}");
        $this->assertSame($path, $field->fresh()->value);

        // removing deletes the uploaded file; the original site asset (never uploaded) is not something we delete
        $this->put('/admin/pages/contact-us/content', ['remove_images' => [$field->id => 1]]);
        $this->assertSame('', (string) $field->fresh()->value);
        \Storage::disk('uploads')->assertMissing(substr($path, strlen('uploads/')));
        $this->assertNotSame('', $legacy);
    }

    public function test_shared_content_edit_reaches_every_page(): void
    {
        $field = ContentField::where('scope', 'global')->where('key', 'header.1')->firstOrFail();   // the menu button label, printed on every page
        $this->actingAs($this->editor())->put('/admin/shared', ['fields' => [$field->id => 'Shared Banner Text']])->assertSessionHas('success');
        auth()->logout();
        foreach (['/', '/about-us.php', '/contact-us.php'] as $url) {
            $this->get($url)->assertOk()->assertSee('Shared Banner Text');
        }
    }

    private function faqList(): ContentList
    {
        return ContentList::where('scope', '3d-modeling-rendering')->where('key', 'td_faqs')->firstOrFail();
    }

    public function test_formatted_answer_with_a_list_is_saved_and_shown_with_bullets(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/3d-modeling-rendering/content';
        $list = $this->faqList();

        $data = $this->formData($url);
        $rows = $data['lists'][$list->id]['items'];
        $k = array_key_first(array_filter($rows, fn ($key) => is_int($key), ARRAY_FILTER_USE_KEY));
        $data['lists'][$list->id]['items'][$k]['1'] = 'We cover:<ul><li>Modeling</li><li><strong>Rendering</strong></li></ul><script>alert(1)</script>';
        $this->put($url, $data)->assertSessionHas('success');

        $stored = $list->fresh()->items[$k][1];
        $this->assertStringContainsString('<ul class="rt"><li>Modeling</li><li><strong>Rendering</strong></li></ul>', $stored);
        $this->assertStringNotContainsString('script', $stored);

        auth()->logout();
        $this->get('/3d-modeling-rendering.php')->assertSee('<ul class="rt"><li>Modeling</li>', false);
        $this->actingAs($this->editor())->get($url)->assertOk()->assertSee('data-fmt-act="ul"', false);
    }

    public function test_list_rows_can_be_added_edited_reordered_and_removed(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/3d-modeling-rendering/content';
        $list = $this->faqList();
        $count = count($list->items);
        $first = $list->items[0];

        // add a row
        $data = $this->formData($url);
        $data['lists'][$list->id]['items']['n48213'] = ['0' => 'Do you sign NDAs?', '1' => 'Yes, always.'];   // the admin JavaScript names new rows like this
        $this->put($url, $data)->assertSessionHas('success')->assertSessionHasNoErrors();
        $items = $list->fresh()->items;
        $this->assertCount($count + 1, $items);
        $this->assertSame(['Do you sign NDAs?', 'Yes, always.'], $items[$count]);
        auth()->logout();
        $this->get('/3d-modeling-rendering.php')->assertSee('Do you sign NDAs?')->assertSee('Yes, always.');
        $this->actingAs($this->editor());

        // edit the first row and move it to the end
        $data = $this->formData($url);
        $rows = $data['lists'][$list->id]['items'];
        $firstRow = array_shift($rows);
        $firstRow['0'] = 'Edited question?';
        $rows[] = $firstRow;
        $data['lists'][$list->id]['items'] = $rows;
        $this->put($url, $data)->assertSessionHas('success');
        $items = $list->fresh()->items;
        $this->assertCount($count + 1, $items);
        $this->assertSame('Edited question?', $items[$count][0]);
        $this->assertSame($first[1], $items[$count][1]);       // the answer travelled with its question

        // remove one
        $data = $this->formData($url);
        unset($data['lists'][$list->id]['items'][array_key_last(array_filter($data['lists'][$list->id]['items'], fn ($k) => is_int($k), ARRAY_FILTER_USE_KEY))]);   // last real row, not the __N__ prototype
        $this->put($url, $data)->assertSessionHas('success');
        $this->assertCount($count, $list->fresh()->items);
    }

    public function test_blank_new_rows_and_the_prototype_row_are_ignored(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/3d-modeling-rendering/content';
        $list = $this->faqList();
        $data = $this->formData($url);
        $data['lists'][$list->id]['items'][] = ['0' => '', '1' => ''];
        $data['lists'][$list->id]['items']['__N__'] = ['0' => 'Ghost', '1' => 'Row'];
        $this->put($url, $data)->assertSessionHas('success', 'Nothing was changed.');
        $this->assertCount(count($list->items), $list->fresh()->items);
    }

    public function test_fixed_lists_cannot_grow_or_shrink(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/video-editing/content';
        $fields = ContentList::where('scope', 'video-editing')->where('key', 've_form_fields')->firstOrFail();
        $before = $fields->items;

        $data = $this->formData($url);
        $data['lists'][$fields->id]['items'][] = ['0' => 'Extra field'];                                        // try to grow
        $data['lists'][$fields->id]['items'] = array_slice($data['lists'][$fields->id]['items'], 1, null, true);   // and drop the first row
        $this->put($url, $data);

        $this->assertSame($before, $fields->fresh()->items);
    }

    public function test_lists_that_used_to_be_fixed_can_now_grow_and_shrink(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/index/content';
        $reasons = ContentList::where('scope', 'index')->where('key', 'reasons')->firstOrFail();
        $count = count($reasons->items);

        $data = $this->formData($url);
        $data['lists'][$reasons->id]['items']['n101'] = ['0' => 'Secure by Default', '1' => 'Your files stay with us.'];
        $this->put($url, $data)->assertSessionHas('success')->assertSessionHasNoErrors();
        $this->assertCount($count + 1, $reasons->fresh()->items);
        $this->assertSame(['Secure by Default', 'Your files stay with us.'], $reasons->fresh()->items[$count]);

        $data = $this->formData($url);
        unset($data['lists'][$reasons->id]['items'][0]);
        $this->put($url, $data)->assertSessionHas('success');
        $this->assertCount($count, $reasons->fresh()->items);
        $this->assertSame('Secure by Default', $reasons->fresh()->items[$count - 1][0]);
    }

    public function test_columns_and_their_items_can_be_added_and_removed(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/seo-services/content';
        $tools = ContentList::where('scope', 'seo-services')->where('key', 'seo_tools')->firstOrFail();
        $before = $tools->items;

        // a new tool in the first column copies that column's hidden design values, and a new column is added
        $data = $this->formData($url);
        $data['lists'][$tools->id]['groups'][0]['n7'] = ['_orig' => '', '1' => 'Semrush', '2' => 'Audit | Track'];
        $data['lists'][$tools->id]['groups']['n8'] = ['0' => ['_orig' => '', '1' => 'Screaming Frog', '2' => 'Crawl | Fix']];
        $this->put($url, $data)->assertSessionHas('success')->assertSessionHasNoErrors();
        $items = $tools->fresh()->items;
        $this->assertCount(count($before) + 1, $items);
        $this->assertCount(count($before[0]) + 1, $items[0]);
        $this->assertSame(['Semrush', 'Audit | Track'], [Arr::last($items[0])[1], Arr::last($items[0])[2]]);
        $this->assertSame('Screaming Frog', Arr::last($items)[0][1]);
        $this->assertSame($before[1], $items[1]);                    // the other columns are untouched

        // removing the new column and the new tool brings back exactly what was there
        $data = $this->formData($url);
        $groups = $data['lists'][$tools->id]['groups'];
        $lastReal = fn (array $a) => array_key_last(array_filter($a, fn ($k) => is_int($k), ARRAY_FILTER_USE_KEY));   // not the __N__ / __M__ prototypes
        unset($groups[$lastReal($groups)]);
        unset($groups[0][$lastReal($groups[0])]);
        $data['lists'][$tools->id]['groups'] = $groups;
        $this->put($url, $data)->assertSessionHas('success');
        $this->assertSame($before, $tools->fresh()->items);
    }

    public function test_filter_tabs_can_be_added_and_removed_including_all(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/seo-services/content';
        $filters = ContentList::where('scope', 'seo-services')->where('key', 'seo_filters')->firstOrFail();
        $before = $filters->items;

        $data = $this->formData($url);
        $data['lists'][$filters->id]['new']['n5'] = 'Local SEO';
        unset($data['lists'][$filters->id]['map']['all'], $data['lists'][$filters->id]['map']['multi']);
        $this->put($url, $data)->assertSessionHas('success');

        $items = $filters->fresh()->items;
        $this->assertArrayNotHasKey('all', $items);                  // the "All" tab can be deleted too
        $this->assertArrayNotHasKey('multi', $items);
        $this->assertSame('Local SEO', $items['local-seo']);
        $this->assertCount(count($before) - 1, $items);

        // with every tab deleted the page shows no empty tab bar
        $data = $this->formData($url);
        unset($data['lists'][$filters->id]['map']);
        $this->put($url, $data)->assertSessionHas('success');
        $this->assertSame([], $filters->fresh()->items);
        auth()->logout();
        $this->get('/seo-services.php')->assertOk()->assertDontSee('class="filter-tabs', false);
    }

    public function test_office_tabs_can_be_added_and_removed(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/contact-us/content';
        $offices = ContentList::where('scope', 'contact-us')->where('key', 'ct_offices')->firstOrFail();
        $before = $offices->items;

        $data = $this->formData($url);
        $data['lists'][$offices->id]['new']['n3'] = ['tab' => 'UAE', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'phone' => '+971 4 000 0000', 'email' => 'uae@example.com'];
        $this->put($url, $data)->assertSessionHas('success')->assertSessionHasNoErrors();
        $items = $offices->fresh()->items;
        $this->assertCount(count($before) + 1, $items);
        $this->assertSame('Dubai', $items['united-arab-emirates']['city']);
        auth()->logout();
        $this->get('/contact-us.php')->assertOk()->assertSee('United Arab Emirates');
        $this->actingAs($this->editor());

        $data = $this->formData($url);
        unset($data['lists'][$offices->id]['map']['united-arab-emirates']);
        $this->put($url, $data)->assertSessionHas('success');
        $this->assertSame($before, $offices->fresh()->items);
    }

    public function test_tampered_list_ids_and_hidden_values_are_ignored(): void
    {
        $this->actingAs($this->editor());
        $url = '/admin/pages/3d-modeling-rendering/content';
        $workflow = ContentList::where('scope', '3d-modeling-rendering')->where('key', 'td_workflow')->firstOrFail();
        $other = ContentList::where('scope', 'about-us')->firstOrFail();
        $before = [$workflow->items, $other->items];

        $data = $this->formData($url);
        $data['lists'][$workflow->id]['items'][0]['color'] = 'red; background:url(javascript:alert(1))';   // design value that is not on the form
        $data['lists'][$other->id] = ['items' => [['x' => 'hacked']]];                                          // list that belongs to another page
        $this->put($url, $data);

        $this->assertSame($before[0][0]['color'], $workflow->fresh()->items[0]['color']);
        $this->assertSame($before[1], $other->fresh()->items);
    }

    public function test_custom_pages_have_no_section_editor(): void
    {
        $this->actingAs($this->admin())->post('/admin/pages', ['title' => 'Plain', 'slug' => 'plain', 'status' => 'published']);
        $this->get('/admin/pages/plain/content')->assertNotFound();
    }
    public function test_content_editor_has_a_search_box_for_the_page(): void
    {
        $this->actingAs($this->editor())->get('/admin/pages/about-us/content')->assertOk()
            ->assertSee('data-content-search', false)->assertSee('admin-assets/js/content-search.js', false);
    }
}
