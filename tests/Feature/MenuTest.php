<?php

namespace Tests\Feature;

use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Models\Page;
use Tests\CmsTestCase;

class MenuTest extends CmsTestCase
{
    public function test_menu_screen_lists_header_and_footer_groups(): void
    {
        $this->actingAs($this->editor());
        $this->get('/admin/menu')->assertOk()->assertSee('Branding &amp; Creative', false);
        $this->get('/admin/menu?tab=footer')->assertOk();
        $this->assertGreaterThan(0, MenuGroup::where('location', 'header')->count());
        $this->assertGreaterThan(0, MenuGroup::where('location', 'footer')->count());
    }

    public function test_group_and_link_lifecycle_is_reflected_on_the_public_site(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/menu/groups', ['title' => 'Consulting'])->assertRedirect();
        $group = MenuGroup::where('title', 'Consulting')->firstOrFail();
        $this->assertSame('header', $group->location);

        $this->post("/admin/menu/groups/{$group->id}/items", ['label' => 'Audit Service', 'url' => 'https://example.com/audit', 'is_published' => 1, 'new_tab' => 1])->assertRedirect();
        $item = MenuItem::where('label', 'Audit Service')->firstOrFail();

        auth()->logout();
        $this->get('/')->assertSee('Consulting')->assertSee('Audit Service')->assertSee('https://example.com/audit');

        // unpublish the link -> gone from the site
        $this->actingAs($this->admin())->put("/admin/menu/items/{$item->id}", ['label' => 'Audit Service', 'url' => 'https://example.com/audit', 'is_published' => 0])->assertRedirect();
        auth()->logout();
        $this->get('/')->assertDontSee('Audit Service');

        // delete the group (and the link with it)
        $this->actingAs($this->admin())->delete("/admin/menu/groups/{$group->id}")->assertRedirect();
        $this->assertSame(0, MenuItem::where('label', 'Audit Service')->count());
        auth()->logout();
        $this->get('/')->assertDontSee('Consulting');
    }

    public function test_link_to_a_cms_page_follows_that_pages_publish_state(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Case Studies', 'slug' => 'case-studies', 'status' => 'published', 'content' => '<p>x</p>']);
        $page = Page::where('slug', 'case-studies')->firstOrFail();
        $group = MenuGroup::where('location', 'header')->orderBy('sort')->firstOrFail();
        $this->post("/admin/menu/groups/{$group->id}/items", ['label' => 'Our Cases', 'page_id' => $page->id, 'is_published' => 1])->assertRedirect()->assertSessionHasNoErrors();

        auth()->logout();
        fwrite(STDERR, "
");
        $this->get('/')->assertSee('href="' . url('/case-studies') . '"', false);   // clean address for pages made in the admin

        // taking the page offline must immediately stop the menu pointing at it
        $this->actingAs($admin)->patch("/admin/pages/{$page->slug}/status");
        auth()->logout();
        $this->get('/')->assertSee('Our Cases')->assertDontSee('case-studies');
    }

    public function test_ordering_links_up_and_down(): void
    {
        $this->actingAs($this->editor());
        $group = MenuGroup::where('location', 'header')->with('items')->orderBy('sort')->firstOrFail();
        $ids = $group->items()->pluck('id')->all();
        $this->assertGreaterThan(1, count($ids));

        $this->post("/admin/menu/items/{$ids[0]}/move", ['dir' => 'down'])->assertRedirect();
        $this->assertSame([$ids[1], $ids[0]], array_slice($group->items()->pluck('id')->all(), 0, 2));
        $this->post("/admin/menu/items/{$ids[0]}/move", ['dir' => 'up']);
        $this->assertSame($ids, $group->items()->pluck('id')->all());
        $this->post("/admin/menu/items/{$ids[0]}/move", ['dir' => 'up']);           // already first: no-op, no error
        $this->assertSame($ids, $group->items()->pluck('id')->all());
    }

    public function test_unsafe_link_targets_are_refused(): void
    {
        $this->actingAs($this->editor());
        $group = MenuGroup::where('location', 'header')->firstOrFail();
        foreach (['javascript:alert(1)', 'data:text/html,x', 'vbscript:x'] as $bad) {
            $this->post("/admin/menu/groups/{$group->id}/items", ['label' => 'Bad', 'url' => $bad, 'is_published' => 1])->assertStatus(422);
        }
        $this->assertSame(0, MenuItem::where('label', 'Bad')->count());
        $this->post("/admin/menu/groups/{$group->id}/items", ['label' => '', 'url' => '#'])->assertSessionHasErrors('label');
    }

    public function test_labels_are_stripped_of_markup(): void
    {
        $this->actingAs($this->editor());
        $group = MenuGroup::where('location', 'header')->firstOrFail();
        $this->post("/admin/menu/groups/{$group->id}/items", ['label' => '<b>Bold</b><script>x</script>', 'url' => '#', 'is_published' => 1]);
        $this->assertStringNotContainsString('<', MenuItem::latest('id')->value('label'));
    }

    public function test_footer_columns_are_fixed_but_editable(): void
    {
        $footer = MenuGroup::where('location', 'footer')->firstOrFail();
        $this->actingAs($this->admin())->delete("/admin/menu/groups/{$footer->id}")->assertSessionHas('error');
        $this->assertNotNull($footer->fresh());

        $this->put("/admin/menu/groups/{$footer->id}", ['title' => 'Renamed Column'])->assertSessionHas('success');
        $this->assertSame('Renamed Column', $footer->fresh()->title);
        auth()->logout();
        $this->get('/')->assertSee('Renamed Column');
    }

    public function test_only_admins_may_delete_menu_entries(): void
    {
        $group = MenuGroup::where('location', 'header')->firstOrFail();
        $item = $group->items()->firstOrFail();
        $this->actingAs($this->editor());
        $this->delete("/admin/menu/groups/{$group->id}")->assertForbidden();
        $this->delete("/admin/menu/items/{$item->id}")->assertForbidden();
        $this->assertNotNull($item->fresh());
    }
}
