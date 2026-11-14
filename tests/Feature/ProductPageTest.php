<?php

namespace Tests\Feature;

use App\Models\ContentField;
use App\Models\Page;
use App\Support\Content;
use Tests\CmsTestCase;

/** The product pages (DataNote, Formezy, Docezy): one "product" design, content per page from the admin. */
class ProductPageTest extends CmsTestCase
{
    private const PRODUCTS = ['datanote' => 'DataNote', 'formezy' => 'Formezy', 'docezy' => 'Docezy'];

    public function test_the_three_product_pages_render_with_their_own_content(): void
    {
        foreach (self::PRODUCTS as $slug => $name) {
            $page = Page::where('slug', $slug)->firstOrFail();
            $this->assertSame('product', $page->template);
            $this->assertSame($name, $page->title);

            $html = $this->get('/' . $slug)->assertOk()->getContent();
            $this->assertStringContainsString('class="h1 pd-hero__title"', $html);
            $this->assertStringContainsString('<b>' . $name . '</b>', $html);                     // breadcrumb
            $this->assertStringContainsString('assets/images/products/' . $slug . '/hero.webp', $html);
            $this->assertStringContainsString('href="/contact-us#contact-form" class="btn btn--cta"', $html);   // demo button lands on the form

            // the other products are offered at the bottom, never the page itself
            foreach (array_diff(array_keys(self::PRODUCTS), [$slug]) as $other) {
                $this->assertMatchesRegularExpression('~<a class="pd-other" href="/' . $other . '"~', $html, $slug);
            }
            $this->assertDoesNotMatchRegularExpression('~<a class="pd-other" href="/' . $slug . '"~', $html);
        }
    }

    public function test_every_picture_the_product_pages_use_exists(): void
    {
        foreach (array_keys(self::PRODUCTS) as $slug) {
            preg_match_all('~src="/(assets/images/products/[^"?]+)"~', $this->get('/' . $slug)->getContent(), $m);
            $this->assertNotEmpty($m[1], $slug);
            foreach (array_unique($m[1]) as $file) {
                $this->assertFileExists(public_path($file), $slug);
            }
        }
    }

    public function test_the_header_has_a_software_and_erp_menu(): void
    {
        $html = $this->get('/about-us')->assertOk()->getContent();
        $this->assertStringContainsString('>Software &amp; ERP<img', $html);
        foreach (array_keys(self::PRODUCTS) as $slug) {
            $this->assertStringContainsString('href="/' . $slug . '"', $html);
        }
    }

    public function test_a_section_switched_off_or_left_empty_is_not_shown(): void
    {
        $this->assertStringContainsString('class="container pd-modules"', $this->get('/datanote')->getContent());
        $this->assertStringNotContainsString('class="container pd-modules"', $this->get('/docezy')->getContent());   // Docezy has no module cards

        ContentField::where('scope', 'datanote')->where('key', 'modules.show')->update(['value' => '0']);
        Content::forget('datanote');
        $this->assertStringNotContainsString('class="container pd-modules"', $this->get('/datanote')->getContent());
    }

    public function test_a_draft_product_is_left_out_of_the_other_product_cards(): void
    {
        Page::where('slug', 'formezy')->update(['status' => Page::STATUS_DRAFT]);
        $this->get('/formezy')->assertNotFound();
        $this->assertDoesNotMatchRegularExpression('~<a class="pd-other" href="/formezy"~', $this->get('/datanote')->getContent());
    }

    public function test_the_admin_can_edit_a_product_page(): void
    {
        $page = Page::where('slug', 'docezy')->firstOrFail();
        $this->actingAs($this->admin())->get(route('admin.pages.content', $page))
            ->assertOk()->assertSee('Main heading (H1)')->assertSee('Feature tiles')->assertSee('Design Colours');
    }
}
