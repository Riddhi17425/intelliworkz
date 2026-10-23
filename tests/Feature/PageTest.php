<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Tests\CmsTestCase;

class PageTest extends CmsTestCase
{
    private function newPage(array $over = []): array
    {
        return $over + ['title' => 'Our Process', 'slug' => 'our-process', 'status' => 'draft', 'meta_title' => '', 'meta_description' => 'How we work.', 'content' => '<h2>Step one</h2><p>We listen.</p>'];
    }

    public function test_all_original_pages_are_published_and_render(): void
    {
        $this->assertSame(14, Page::where('is_system', true)->count());
        foreach (Page::where('is_system', true)->get() as $page) {
            $url = $page->slug === 'index' ? '/' : "/{$page->slug}.php";
            $this->get($url)->assertOk()->assertSee('<title>', false);
        }
        $this->get('/index.php')->assertOk();
        $this->get('/about-us')->assertOk();       // clean URL works too
        $this->get('/does-not-exist.php')->assertNotFound();
    }

    public function test_custom_page_lifecycle_draft_publish_unpublish_delete(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/pages', $this->newPage())->assertRedirect();
        $page = Page::where('slug', 'our-process')->firstOrFail();
        $this->assertSame('draft', $page->status);
        $this->assertFalse($page->is_system);

        // a draft is invisible to visitors but previewable by a signed-in admin
        auth()->logout();
        $this->get('/our-process')->assertNotFound();
        $this->get('/our-process.php')->assertNotFound();
        $this->actingAs($admin)->get('/our-process')->assertOk()->assertSee('Draft preview')->assertSee('noindex', false);

        // publish -> public, with SEO tags
        $this->patch("/admin/pages/{$page->slug}/status")->assertRedirect();
        $this->assertSame('published', $page->fresh()->status);
        auth()->logout();
        $this->get('/our-process')->assertOk()->assertSee('<h2>Step one</h2>', false)
            ->assertSee('<meta name="description" content="How we work.">', false)
            ->assertSee('<title>Our Process', false)->assertDontSee('Draft preview');

        // unpublish -> gone again
        $this->actingAs($admin)->patch("/admin/pages/{$page->slug}/status");
        auth()->logout();
        $this->get('/our-process')->assertNotFound();

        // delete
        $this->actingAs($admin)->delete("/admin/pages/{$page->slug}")->assertRedirect(route('admin.pages.index'));
        $this->assertNull(Page::where('slug', 'our-process')->first());
    }

    public function test_published_custom_page_appears_in_the_sitemap_free_navigation_of_admin_list_only(): void
    {
        $this->actingAs($this->editor())->post('/admin/pages', $this->newPage(['status' => 'published']))->assertRedirect();
        $this->get('/admin/pages?q=process')->assertOk()->assertSee('Our Process');
        $this->get('/admin/pages?q=zzzz')->assertOk()->assertDontSee('Our Process');
        $this->get('/admin/pages?status=draft')->assertOk()->assertDontSee('Our Process');
    }

    public function test_slug_rules(): void
    {
        $this->actingAs($this->admin());
        $before = Page::where('is_system', false)->count();   // starter pages (industries, legal pages …) already exist
        $this->post('/admin/pages', $this->newPage())->assertRedirect();

        $this->post('/admin/pages', $this->newPage(['title' => 'Dup']))->assertSessionHasErrors('slug');                    // taken
        $this->post('/admin/pages', $this->newPage(['slug' => 'about-us']))->assertSessionHasErrors('slug');                // an original page
        foreach (['admin', 'assets', 'uploads', 'login', 'inquiries', 'Has Space', 'UPPER', 'a/b', '-lead', 'trail-', 'x..y'] as $bad) {
            $this->post('/admin/pages', $this->newPage(['slug' => $bad]))->assertSessionHasErrors('slug');
        }
        $this->assertSame($before + 1, Page::where('is_system', false)->count());
    }

    public function test_original_pages_are_protected(): void
    {
        $this->actingAs($this->admin());
        $this->delete('/admin/pages/about-us')->assertRedirect()->assertSessionHas('error');
        $this->assertNotNull(Page::where('slug', 'about-us')->first());

        // the home page can never be taken offline
        $this->patch('/admin/pages/index/status')->assertSessionHas('error');
        $this->put('/admin/pages/index', ['title' => 'Home', 'status' => 'draft'])->assertSessionHasErrors('status');
        $this->assertSame('published', Page::where('slug', 'index')->value('status'));

        // an original page can be unpublished (and then 404s) but its address cannot be changed
        $this->put('/admin/pages/seo-services', ['title' => 'SEO Services', 'status' => 'draft', 'slug' => 'renamed']);
        $this->assertSame('draft', Page::where('slug', 'seo-services')->value('status'));
        $this->assertNull(Page::where('slug', 'renamed')->first());
        auth()->logout();
        $this->get('/seo-services.php')->assertNotFound();
    }

    public function test_seo_fields_reach_the_public_page(): void
    {
        $this->actingAs($this->editor())->put('/admin/pages/about-us', [
            'title' => 'About us', 'status' => 'published',
            'meta_title' => 'Custom About Title', 'meta_description' => 'A custom description.', 'meta_keywords' => 'design, print',
        ])->assertRedirect();
        auth()->logout();
        $this->get('/about-us.php')->assertOk()
            ->assertSee('<title>Custom About Title</title>', false)
            ->assertSee('content="A custom description."', false)
            ->assertSee('content="design, print"', false)
            ->assertSee('<link rel="canonical"', false);
    }

    public function test_page_body_is_sanitised(): void
    {
        $this->actingAs($this->admin())->post('/admin/pages', $this->newPage([
            'status' => 'published',
            'content' => '<p onclick="x()">Hi</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a><img src="x.png" onerror="alert(1)"><iframe src="//evil"></iframe><style>*{display:none}</style>',
        ]));
        auth()->logout();
        $html = $this->get('/our-process')->assertOk()->getContent();
        $body = substr($html, strpos($html, 'gp-prose'));
        foreach (['<script>alert', 'onclick', 'onerror', 'javascript:', '<iframe', '<style>*'] as $needle) {
            $this->assertStringNotContainsString($needle, $body, "{$needle} survived sanitising");
        }
        $this->assertStringContainsString('Hi', $body);
    }

    public function test_title_is_escaped_everywhere(): void
    {
        $this->actingAs($this->admin())->post('/admin/pages', $this->newPage(['title' => '<img src=x onerror=alert(1)>Tricky', 'status' => 'published']));
        $this->get('/admin/pages')->assertOk()->assertDontSee('<img src=x onerror', false);
        auth()->logout();
        $this->get('/our-process')->assertOk()->assertDontSee('<img src=x onerror', false);
    }

    public function test_images_can_be_uploaded_replaced_and_removed(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/pages', $this->newPage(['status' => 'published', 'featured_image' => UploadedFile::fake()->create('hero.png', 10, 'image/png')]))->assertRedirect();
        $page = Page::where('slug', 'our-process')->firstOrFail();
        $first = $page->featured_image;
        $this->assertStringStartsWith('uploads/pages/', $first);
        \Storage::disk('uploads')->assertExists(substr($first, strlen('uploads/')));

        $this->put("/admin/pages/{$page->slug}", $this->newPage(['status' => 'published', 'featured_image' => UploadedFile::fake()->create('new.jpg', 10, 'image/jpeg')]));
        $second = $page->fresh()->featured_image;
        $this->assertNotSame($first, $second);
        \Storage::disk('uploads')->assertMissing(substr($first, strlen('uploads/')));   // old file cleaned up

        $this->put("/admin/pages/{$page->slug}", $this->newPage(['status' => 'published', 'remove_featured_image' => 1]));
        $this->assertNull($page->fresh()->featured_image);
        \Storage::disk('uploads')->assertMissing(substr($second, strlen('uploads/')));
    }

    public function test_dangerous_uploads_are_refused(): void
    {
        $this->actingAs($this->admin());
        $before = Page::where('is_system', false)->count();
        $this->post('/admin/pages', $this->newPage(['featured_image' => UploadedFile::fake()->create('shell.php', 5, 'application/x-php')]))->assertSessionHasErrors('featured_image');
        $this->post('/admin/pages', $this->newPage(['featured_image' => UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml')]))->assertSessionHasErrors('featured_image');   // svg not allowed for pages
        $this->post('/admin/pages', $this->newPage(['featured_image' => UploadedFile::fake()->create('big.png', 6000, 'image/png')]))->assertSessionHasErrors('featured_image');       // > 5 MB
        $this->assertSame($before, Page::where('is_system', false)->count());
    }

    public function test_a_deleted_pages_address_can_be_used_again(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/pages', $this->newPage(['status' => 'published']))->assertRedirect();
        $this->delete('/admin/pages/our-process')->assertRedirect(route('admin.pages.index'));
        $this->post('/admin/pages', $this->newPage(['title' => 'Our Process v2', 'status' => 'published']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Our Process v2', Page::where('slug', 'our-process')->value('title'));
        auth()->logout();
        $this->get('/our-process')->assertOk()->assertSee('Our Process v2');
    }

    public function test_only_admins_can_delete(): void
    {
        $this->actingAs($this->admin())->post('/admin/pages', $this->newPage());
        $this->actingAs($this->editor())->delete('/admin/pages/our-process')->assertForbidden();
        $this->assertNotNull(Page::where('slug', 'our-process')->first());
    }
}
