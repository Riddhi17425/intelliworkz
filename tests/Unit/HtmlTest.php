<?php

namespace Tests\Unit;

use App\Support\Html;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{
    public function test_plain_text_is_escaped(): void
    {
        $this->assertSame('R&amp;D &lt; 5', Html::clean('R&D < 5'));
        $this->assertSame('', Html::clean("  \n "));
    }

    public function test_allowed_inline_markup_survives(): void
    {
        $this->assertSame('Hello<br>world', Html::clean('Hello<br>world'));
        $this->assertStringContainsString('<strong>bold</strong>', Html::clean('a <strong>bold</strong>'));
        $this->assertStringContainsString('<a href="https://example.com"', Html::clean('<a href="https://example.com">x</a>'));
    }

    #[DataProvider('attacks')]
    public function test_attacks_are_neutralised(string $input, string $forbidden): void
    {
        $this->assertStringNotContainsStringIgnoringCase($forbidden, Html::clean($input));
        $this->assertStringNotContainsStringIgnoringCase($forbidden, Html::rich($input));
    }

    public static function attacks(): array
    {
        return [
            'script tag' => ['<p>a</p><script>alert(1)</script>', '<script'],
            'inline handler' => ['<strong onclick="x()">a</strong>', 'onclick'],
            'img onerror' => ['<img src=x onerror=alert(1)>', 'onerror'],
            'javascript href' => ['<a href="javascript:alert(1)">a</a>', 'javascript:'],
            'mixed case javascript' => ['<a href="JaVa&#09;ScRiPt:alert(1)">a</a>', 'script:'],
            'data href' => ['<a href="data:text/html;base64,PHNjcmlwdD4=">a</a>', 'data:text'],
            'iframe' => ['<iframe src="//evil"></iframe>', '<iframe'],
            'style attr' => ['<span style="background:url(javascript:1)">a</span>', 'style='],
            'svg onload' => ['<svg onload=alert(1)>', 'onload'],
            'object' => ['<object data="x"></object>', '<object'],
            'form' => ['<form action="//evil"><input></form>', '<form'],
        ];
    }

    public function test_rich_text_keeps_block_tags(): void
    {
        $out = Html::rich('<h2>Title</h2><ul><li>One</li></ul><p>Para</p>');
        $this->assertStringContainsString('<h2>Title</h2>', $out);
        $this->assertStringContainsString('<li>One</li>', $out);
    }

    public function test_plain_strips_all_tags(): void
    {
        $this->assertSame('Hello world', Html::plain('<b>Hello</b>   <i>world</i>'));
    }

    public function test_safe_url(): void
    {
        foreach (['https://a.co', 'http://a.co/x?y=1', 'contact-us.php', '#', '#top', 'mailto:a@b.co', 'tel:+911234567890', '/about'] as $ok) {
            $this->assertTrue((bool) Html::safeUrl($ok), "{$ok} should be allowed");
        }
        foreach (['javascript:alert(1)', ' javascript:alert(1)', 'JAVASCRIPT:x', 'data:text/html,x', 'vbscript:x', "java\tscript:x"] as $bad) {
            $this->assertFalse((bool) Html::safeUrl($bad), "{$bad} must be refused");
        }
    }
}
