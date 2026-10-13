<?php

namespace Tests\Feature;

use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\Page;
use Tests\CmsTestCase;

class PageCloneTest extends CmsTestCase
{
    private function clonePage(string $slug = '2d-animation', string $title = '2D Animation', string $base = '3d-modeling-rendering'): Page
    {
        $this->actingAs($this->admin())->post('/admin/pages', [
            'title' => $title, 'slug' => $slug, 'status' => 'published', 'base' => $base,
        ])->assertRedirect();

        return Page::where('slug', $slug)->firstOrFail();
    }

    public function test_new_page_can_copy_the_design_and_content_of_an_existing_page(): void
    {
        $page = $this->clonePage();

        $this->assertFalse($page->is_system);
        $this->assertSame('3d-modeling-rendering', $page->viewKey());
        $this->assertSame(
            ContentField::where('scope', '3d-modeling-rendering')->count(),
            ContentField::where('scope', '2d-animation')->count()
        );
        $this->assertSame(
            ContentList::where('scope', '3d-modeling-rendering')->count(),
            ContentList::where('scope', '2d-animation')->count()
        );

        // rendered with the same markup as the original, at its own address
        $html = $this->get('/2d-animation')->assertOk()->getContent();
        $this->assertStringContainsString('td-viewer', $html);
        $this->assertStringContainsString('ve-faq', $html);
    }

    public function test_copy_is_independent_of_the_original(): void
    {
        $page = $this->clonePage();
        $field = ContentField::where('scope', '2d-animation')->where('key', 'faqs.1')->firstOrFail();
        $field->update(['value' => 'Questions about 2D animation']);
        \App\Support\Content::forget('2d-animation');

        $this->get('/2d-animation')->assertSee('Questions about 2D animation');
        $this->get('/3d-modeling-rendering.php')->assertDontSee('Questions about 2D animation');
        $this->assertNotSame('Questions about 2D animation', ContentField::where('scope', '3d-modeling-rendering')->where('key', 'faqs.1')->value('value'));
    }

    public function test_content_editor_works_for_a_copied_page_and_saves_to_its_own_content(): void
    {
        $this->clonePage();
        $admin = $this->admin();
        $field = ContentField::where('scope', '2d-animation')->where('key', 'faqs.1')->firstOrFail();

        $this->actingAs($admin)->get('/admin/pages/2d-animation/content')->assertOk()->assertSee('Sections on this page');
        $this->actingAs($admin)->put('/admin/pages/2d-animation/content', ['fields' => [$field->id => 'My own FAQ title']])->assertRedirect();

        $this->assertSame('My own FAQ title', $field->fresh()->value);
        $this->assertNotSame('My own FAQ title', ContentField::where('scope', '3d-modeling-rendering')->where('key', 'faqs.1')->value('value'));
    }

    public function test_changing_the_address_keeps_the_content(): void
    {
        $page = $this->clonePage();
        $count = ContentField::where('scope', '2d-animation')->count();

        $this->actingAs($this->admin())->put('/admin/pages/2d-animation', [
            'title' => '2D Animation', 'slug' => 'animation-2d', 'status' => 'published',
        ])->assertRedirect();

        $this->assertSame($count, ContentField::where('scope', 'animation-2d')->count());
        $this->assertSame(0, ContentField::where('scope', '2d-animation')->count());
        $this->get('/animation-2d')->assertOk()->assertSee('td-viewer');
    }

    public function test_deleting_a_copied_page_removes_its_content_but_not_the_original(): void
    {
        $this->clonePage();
        $before = ContentField::where('scope', '3d-modeling-rendering')->count();

        $this->actingAs($this->admin())->delete('/admin/pages/2d-animation')->assertRedirect();

        $this->assertSame(0, ContentField::where('scope', '2d-animation')->count());
        $this->assertSame(0, ContentList::where('scope', '2d-animation')->count());
        $this->assertSame($before, ContentField::where('scope', '3d-modeling-rendering')->count());
        $this->get('/3d-modeling-rendering.php')->assertOk();
    }

    public function test_only_pages_with_their_own_design_can_be_a_base(): void
    {
        $admin = $this->actingAs($this->admin());
        $admin->post('/admin/pages', ['title' => 'X', 'slug' => 'x-page', 'status' => 'draft', 'base' => 'index'])->assertSessionHasErrors('base');
        $admin->post('/admin/pages', ['title' => 'X', 'slug' => 'x-page', 'status' => 'draft', 'base' => 'nope'])->assertSessionHasErrors('base');
        $admin->get('/admin/pages/create?from=seo-services')->assertOk()->assertSee('Same design as');
    }

    public function test_any_designed_page_can_be_copied_and_renders(): void
    {
        foreach (['video-editing', 'seo-services', 'magazine-design', 'manufacturing'] as $i => $base) {
            $page = $this->clonePage("copy-$i", "Copy $i", $base);
            $this->get("/copy-$i")->assertOk();
        }
    }
}
