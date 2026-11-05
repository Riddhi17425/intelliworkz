<?php

namespace Tests\Feature;

use App\Models\Page;
use Tests\CmsTestCase;

class BlogAdminTest extends CmsTestCase
{
    public function test_blog_posts_have_their_own_admin_list_and_form(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/pages?type=blog')->assertOk()->assertSee('New blog post')->assertSee('Blog posts');
        $this->get('/admin/pages/create?blog=1')->assertOk()->assertSee('New blog post')
            ->assertSee('id="f_is_blog"', false)->assertDontSee('id="f_base"', false);
    }

    public function test_a_new_blog_post_shows_on_the_blog_page_and_the_home_page_by_itself(): void
    {
        $this->actingAs($this->admin());
        $this->post('/admin/pages', [
            'title' => 'Brand New Insight 5521', 'slug' => 'brand-new-insight-5521', 'status' => 'published',
            'is_blog' => 1, 'category' => 'Publishing', 'published_on' => now()->format('Y-m-d'),
            'content' => '<p>Hello readers</p>',
        ])->assertSessionHasNoErrors();

        $page = Page::where('slug', 'brand-new-insight-5521')->firstOrFail();
        $this->assertTrue($page->isBlog());
        auth()->logout();
        $this->get('/blog')->assertOk()->assertSee('Brand New Insight 5521');
        $this->get('/blog/brand-new-insight-5521')->assertOk()->assertSee('Hello readers');
        $this->get('/')->assertOk()->assertSee('Brand New Insight 5521');
        $this->get('/sitemap.xml')->assertSee('/blog/brand-new-insight-5521');
    }
}
