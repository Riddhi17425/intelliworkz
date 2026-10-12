<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * All editable copy is stored as a ready-to-print HTML fragment. Whatever an editor types goes through here:
 *  - plain text (no tags)  -> escaped
 *  - text with tags        -> only a small allow-list of inline tags/attributes survives
 *  - rich text (pages)     -> a wider allow-list of block tags
 * Scripts, event handlers, style attributes and javascript: links never survive.
 */
class Html
{
    private const INLINE = ['br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 'small' => [], 'sup' => [], 'sub' => [],
        'span' => ['class'], 'a' => ['href', 'target', 'rel']];

    private const RICH = ['p' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'ul' => [], 'ol' => [], 'li' => [], 'blockquote' => [], 'hr' => [],
        'img' => ['src', 'alt'], 'figure' => [], 'figcaption' => [], 'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => []];

    /** Does the string contain at least one HTML tag (as opposed to a bare "<" in prose)? */
    public static function hasTags(?string $value): bool
    {
        return (bool) preg_match('/<\/?[a-z][a-z0-9]*\b[^>]*>/i', (string) $value);
    }

    /** Editor input -> stored fragment, for one-line / paragraph copy. */
    public static function clean(?string $value): string
    {
        $value = trim(str_replace("\r\n", "\n", (string) $value));
        if ($value === '') {
            return '';
        }
        return self::hasTags($value) ? self::sanitize($value, self::INLINE) : e($value);
    }

    /** Editor input -> stored fragment, for long-form rich text. */
    public static function rich(?string $value): string
    {
        $value = trim((string) $value);
        return $value === '' ? '' : self::sanitize($value, self::INLINE + self::RICH);
    }

    /** Stored fragment -> what an input box should show (entities decoded unless real markup is present). */
    public static function forInput(?string $value): string
    {
        $value = (string) $value;
        return self::hasTags($value) ? $value : html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Plain text, e.g. for meta descriptions or list previews. */
    public static function plain(?string $value): string
    {
        $text = html_entity_decode(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', (string) $value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private static function sanitize(string $html, array $allowed): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $prev = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $root = $doc->getElementById('__root');
        if (!$root) {
            return e(strip_tags($html));
        }
        self::walk($root, $allowed);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $out .= $doc->saveHTML($child);
        }
        return trim($out);
    }

    private static function walk(DOMNode $node, array $allowed): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'textarea', 'select', 'button', 'svg', 'math', 'template'], true)) {
                    $node->removeChild($child);
                    continue;
                }
                if (!array_key_exists($tag, $allowed)) {
                    // unknown tag: keep its text, drop the tag itself
                    self::walk($child, $allowed);
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $ok = in_array($name, $allowed[$tag], true);
                    if ($ok && in_array($name, ['href', 'src'], true) && !self::safeUrl($attr->value)) {
                        $ok = false;
                    }
                    if (!$ok) {
                        $child->removeAttribute($attr->name);
                    }
                }
                if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
                self::walk($child, $allowed);
            } elseif ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);
            }
        }
    }

    public static function safeUrl(?string $url): bool
    {
        $url = trim(preg_replace('/[\x00-\x20]+/', '', html_entity_decode((string) $url)));
        if ($url === '' || $url[0] === '#' || $url[0] === '/' || str_starts_with($url, './') || str_starts_with($url, '../')) {
            return true;
        }
        if (!preg_match('/^([a-z][a-z0-9+.-]*):/i', $url, $m)) {
            return true;   // relative path like "contact-us.php"
        }
        return in_array(strtolower($m[1]), ['http', 'https', 'mailto', 'tel'], true);
    }
}
