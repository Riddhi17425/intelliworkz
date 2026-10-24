<?php

namespace Tests\Feature;

use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\Page;
use App\Support\SectionPlan;
use Tests\CmsTestCase;

class PageLayoutTest extends CmsTestCase
{
    private function clonePage(string $slug = '2d-animation', string $title = '2D Animation', string $base = '3d-modeling-rendering'): Page
    {
        $this->actingAs($this->admin())->post('/admin/pages', [
            'title' => $title, 'slug' => $slug, 'status' => 'published', 'base' => $base,
        ])->assertRedirect();

        return Page::where('slug', $slug)->firstOrFail();
    }

    /** rows: section id => shown?, in the order the sections should appear */
    private function layoutPost(array $rows): array
    {
        $layout = [];
        $i = 0;
        foreach ($rows as $id => $show) {
            $layout[$i++] = ['id' => $id] + ($show ? ['show' => 1] : []);
        }
        return ['has_layout_form' => 1, 'layout' => $layout];
    }

    public function test_sections_can_be_hidden_and_reordered_per_page(): void
    {
        $page = $this->clonePage();
        $admin = $this->admin();
        $this->get('/2d-animation')->assertSee('ve-faq')->assertSee('td-tools');

        $rows = array_fill_keys(SectionPlan::KEYS, true);
        $rows['faq'] = false;
        $rows['tools'] = false;
        $this->actingAs($admin)->put('/admin/pages/2d-animation/content', $this->layoutPost($rows))->assertRedirect();

        $hidden = collect($page->fresh()->layout)->where('hidden', true)->pluck('key')->all();
        $this->assertEqualsCanonicalizing(['faq', 'tools'], $hidden);
        auth()->logout();
        $this->get('/2d-animation')->assertOk()->assertDontSee('ve-faq')->assertDontSee('td-tools')->assertSee('ve-stats');
        $this->get('/3d-modeling-rendering.php')->assertSee('ve-faq');          // the original page is unaffected

        // move FAQs to the top of the sections, everything shown again
        $order = ['faq' => true] + array_fill_keys(SectionPlan::KEYS, true);
        $this->actingAs($admin)->put('/admin/pages/2d-animation/content', $this->layoutPost($order))->assertRedirect();
        $this->assertSame('faq', $page->fresh()->layout[0]['key']);
        auth()->logout();
        $html = $this->get('/2d-animation')->assertOk()->getContent();
        $this->assertLessThan(strpos($html, 'td-viewer'), strpos($html, 've-faq'));

        // back to the design's own order = no custom layout stored
        $this->actingAs($admin)->put('/admin/pages/2d-animation/content', $this->layoutPost(array_fill_keys(SectionPlan::KEYS, true)))->assertRedirect();
        $this->assertNull($page->fresh()->layout);
    }

    public function test_a_section_of_another_design_can_be_added_edited_and_removed(): void
    {
        $page = $this->clonePage('jadav', 'Jadav', 'packaging-design');
        $this->actingAs($this->admin());

        $this->post('/admin/pages/jadav/sections', ['tpl' => 'seo-services', 'key' => 'faq'])->assertRedirect()->assertSessionHas('success');
        $item = collect($page->fresh()->layout)->firstWhere('tpl', 'seo-services');
        $this->assertSame('faq', $item['key']);
        $scope = 'jadav~' . $item['id'];
        $this->assertGreaterThan(0, ContentField::where('scope', $scope)->count() + ContentList::where('scope', $scope)->count());

        // drawn on the public page (two FAQ blocks now), with the CSS of the design it came from
        auth()->logout();
        $html = $this->get('/jadav')->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'class="container ve-faq"'));
        $this->assertStringContainsString('assets/css/seo.css', $html);

        // the editor lists it with its own cards
        $this->actingAs($this->admin())->get('/admin/pages/jadav/content')->assertOk()->assertSee('added from', false)->assertSee('Sections on this page');

        // its text is private: changing it does not touch the page it was copied from
        $field = ContentField::where('scope', $scope)->firstOrFail();
        $this->put('/admin/pages/jadav/content', ['fields' => [$field->id => 'Copied-in heading']])->assertRedirect();
        $this->assertSame('Copied-in heading', $field->fresh()->value);
        $this->assertNotSame('Copied-in heading', ContentField::where('scope', 'seo-services')->where('key', $field->key)->value('value'));

        // removing it deletes its content and the block disappears
        $this->delete('/admin/pages/jadav/sections/' . $item['id'])->assertRedirect();
        $this->assertSame(0, ContentField::where('scope', $scope)->count());
        $this->assertSame(0, ContentList::where('scope', $scope)->count());
        auth()->logout();
        $this->assertSame(1, substr_count($this->get('/jadav')->getContent(), 'class="container ve-faq"'));
    }

    public function test_a_section_can_be_copied_from_a_page_the_editor_made(): void
    {
        $source = $this->clonePage('2d-animation', '2D Animation', '3d-modeling-rendering');
        $faqs = ContentList::where('scope', '2d-animation')->where('key', 'td_faqs')->firstOrFail();
        $items = $faqs->items;
        $items[0][0] = 'Our own question?';
        $faqs->items = $items;
        $faqs->save();

        $page = $this->clonePage('jadav', 'Jadav', 'packaging-design');
        $this->actingAs($this->admin())->get('/admin/pages/jadav/content')->assertOk()
            ->assertSee('Your pages')->assertSee('2D Animation (3D Modeling and Rendering design)');

        $this->post('/admin/pages/jadav/sections', ['tpl' => '2d-animation', 'key' => 'faq'])->assertRedirect()->assertSessionHas('success');
        $item = collect($page->fresh()->layout)->firstWhere('tpl', '3d-modeling-rendering');
        $this->assertSame('faq', $item['key']);
        $copy = ContentList::where('scope', 'jadav~' . $item['id'])->where('key', 'td_faqs')->firstOrFail();
        $this->assertSame('Our own question?', $copy->items[0][0]);      // the text of the page it came from, not the original design's

        auth()->logout();
        $this->get('/jadav')->assertOk()->assertSee('Our own question?');
        $this->assertSame($items, $faqs->fresh()->items);                // the source page is unchanged
        $this->assertSame($source->layout, $source->fresh()->layout);
    }

    public function test_every_section_of_every_design_can_be_added_to_a_page(): void
    {
        $this->clonePage('mixer', 'Mixer', 'video-editing');
        $this->actingAs($this->admin());
        foreach (array_keys(SectionPlan::designs()) as $tpl) {
            foreach (SectionPlan::KEYS as $key) {
                $this->post('/admin/pages/mixer/sections', ['tpl' => $tpl, 'key' => $key])->assertRedirect();
            }
        }
        $this->assertCount(8 + 8 * 8, Page::where('slug', 'mixer')->firstOrFail()->layout);
        auth()->logout();
        $this->get('/mixer')->assertOk();
        $this->actingAs($this->admin())->get('/admin/pages/mixer/content')->assertOk();
    }

    public function test_added_sections_follow_the_page_when_it_is_copied_renamed_or_deleted(): void
    {
        $this->clonePage('jadav', 'Jadav', 'packaging-design');
        $admin = $this->actingAs($this->admin());
        $admin->post('/admin/pages/jadav/sections', ['tpl' => 'seo-services', 'key' => 'stats'])->assertRedirect();
        $id = collect(Page::where('slug', 'jadav')->firstOrFail()->layout)->firstWhere('tpl', 'seo-services')['id'];
        $count = ContentField::where('scope', "jadav~$id")->count() + ContentList::where('scope', "jadav~$id")->count();
        $this->assertGreaterThan(0, $count);

        // copying the page copies the added section too
        $admin->post('/admin/pages', ['title' => 'Second', 'slug' => 'second', 'status' => 'published', 'base' => 'jadav'])->assertRedirect();
        $this->assertSame($count, ContentField::where('scope', "second~$id")->count() + ContentList::where('scope', "second~$id")->count());

        // renaming moves it
        $admin->put('/admin/pages/jadav', ['title' => 'Jadav', 'slug' => 'jadav-new', 'status' => 'published'])->assertRedirect();
        $this->assertSame($count, ContentField::where('scope', "jadav-new~$id")->count() + ContentList::where('scope', "jadav-new~$id")->count());
        $this->assertSame(0, ContentField::where('scope', "jadav~$id")->count() + ContentList::where('scope', "jadav~$id")->count());

        // deleting removes it, and only that page's copy
        $admin->delete('/admin/pages/jadav-new')->assertRedirect();
        $this->assertSame(0, ContentField::where('scope', "jadav-new~$id")->count() + ContentList::where('scope', "jadav-new~$id")->count());
        $this->assertSame($count, ContentField::where('scope', "second~$id")->count() + ContentList::where('scope', "second~$id")->count());
    }

    public function test_invalid_sections_are_refused(): void
    {
        $this->clonePage();
        $admin = $this->actingAs($this->admin());
        $admin->post('/admin/pages/2d-animation/sections', ['tpl' => 'about-us', 'key' => 'faq'])->assertSessionHasErrors('tpl');
        $admin->post('/admin/pages/2d-animation/sections', ['tpl' => 'seo-services', 'key' => 'nope'])->assertSessionHasErrors('key');
        $admin->delete('/admin/pages/2d-animation/sections/faq')->assertNotFound();      // own sections cannot be removed, only hidden
    }
}
