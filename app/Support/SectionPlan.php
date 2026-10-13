<?php

namespace App\Support;

use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\Page;
use Illuminate\Support\Str;

/**
 * The sections of a service-page design (resources/views/site/parts/<design>/<section>.blade.php) and the order,
 * visibility and origin of the sections one particular page shows.
 *
 * A page's layout is a list of items {id, key, tpl, hidden}:
 *   tpl = null       the section of the page's own design, content stored under the page's own address
 *   tpl = "<design>" a section copied in from another design; its text and pictures are a private copy stored under "<slug>~<id>"
 */
class SectionPlan
{
    /** Order in which the designs draw their sections (after the hero, which is always first). */
    public const KEYS = ['work', 'stats', 'why', 'tools', 'services', 'showcase', 'offers', 'faq'];

    public const LABELS = [
        'work' => 'Selected work / portfolio',
        'stats' => 'Stats',
        'why' => 'Why Intelliworkz / what you get',
        'tools' => 'Tools strip',
        'services' => 'Services & workflow',
        'showcase' => 'Dark showcase slider',
        'offers' => 'What we offer, call-to-action & engagement',
        'faq' => 'FAQs',
    ];

    /** Extra classes of each design's main element; the CSS of a design is scoped by them. */
    public const MAIN_CLASS = [
        '3d-modeling-rendering' => 'td', 'magazine-design' => 'mg', 'marketing-sales-collaterals' => 'cl', 'packaging-design' => 'pk',
        'ppt-design' => 'ppt', 'pre-press-service' => 'pp', 'seo-services' => 'seo', 'video-editing' => '',
    ];

    /** Designs that are built from sections. */
    public static function supports(?string $viewKey): bool
    {
        return $viewKey !== null && array_key_exists($viewKey, self::MAIN_CLASS);
    }

    /** design => page title, in the order of the page list */
    public static function designs(): array
    {
        $titles = Page::where('is_system', true)->whereIn('slug', array_keys(self::MAIN_CLASS))->orderBy('sort')->pluck('title', 'slug')->all();
        return array_intersect_key($titles, self::MAIN_CLASS);
    }

    public static function label(string $key): string
    {
        return self::LABELS[$key] ?? Str::headline($key);
    }

    public static function defaultLayout(): array
    {
        return array_map(fn ($k) => ['id' => $k, 'key' => $k, 'tpl' => null, 'hidden' => false], self::KEYS);
    }

    /** The layout of a page, complete and normalised. */
    public static function layout(Page $page): array
    {
        $saved = is_array($page->layout) ? $page->layout : null;
        if ($saved === null) {
            return self::defaultLayout();
        }
        $out = [];
        foreach ($saved as $item) {
            if (!is_array($item) || !in_array($item['key'] ?? null, self::KEYS, true) || empty($item['id'])) {
                continue;
            }
            $out[] = ['id' => (string) $item['id'], 'key' => $item['key'], 'tpl' => $item['tpl'] ?? null, 'hidden' => (bool) ($item['hidden'] ?? false)];
        }
        return $out;
    }

    /** Where the texts and lists of one layout item are stored. */
    public static function scope(Page $page, array $item): string
    {
        return $item['tpl'] ? $page->slug . '~' . $item['id'] : $page->slug;
    }

    /** Scopes (besides the page's own) that hold copied-in sections: item id => scope. */
    public static function extraScopes(Page $page): array
    {
        $scopes = [];
        foreach (self::layout($page) as $item) {
            if ($item['tpl']) {
                $scopes[$item['id']] = self::scope($page, $item);
            }
        }
        return $scopes;
    }

    /**
     * What the public template loops over: a view and its data for every visible item. Own sections inherit the
     * page's variables; copied-in sections get their own $T and lists. Also returns the CSS files and main-element
     * classes that the copied-in designs need.
     *
     * @return array{plan: list<array{view:string,data:array}>, css: list<string>, main: string}
     */
    public static function plan(Page $page, Content $content): array
    {
        $own = $page->viewKey();
        $plan = [];
        $css = [];
        $main = [];

        foreach (self::layout($page) as $item) {
            if ($item['hidden']) {
                continue;
            }
            $tpl = $item['tpl'] ?: $own;
            $view = "site.parts.$tpl.{$item['key']}";
            if (!view()->exists($view)) {
                continue;
            }
            $data = [];
            if ($item['tpl']) {
                $scope = self::scope($page, $item);
                $fields = $content->fields($scope);
                $data = $content->lists($scope) + ['T' => fn (string $key, string $default = '') => $fields[$key] ?? $default];
                if ($item['tpl'] !== $own) {
                    foreach ((array) config('site_pages.' . $item['tpl'] . '.extra_css', []) as $file) {
                        $css[] = $file;
                    }
                    if (self::MAIN_CLASS[$item['tpl']] ?? '') {
                        $main[] = self::MAIN_CLASS[$item['tpl']];
                    }
                }
            }
            $plan[] = ['view' => $view, 'data' => $data];
        }

        return ['plan' => $plan, 'css' => array_values(array_unique($css)), 'main' => implode(' ', array_unique($main))];
    }

    /* ------------------------------------------------------------------ editing the layout */

    /** Copy section $key of design $tpl into the page (content taken from the original page of that design). */
    public static function add(Page $page, string $tpl, string $key): array
    {
        $item = ['id' => $key . '-' . Str::lower(Str::random(4)), 'key' => $key, 'tpl' => $tpl, 'hidden' => false];
        self::copyContent($tpl, $key, self::scope($page, $item));

        $layout = self::layout($page);
        $layout[] = $item;
        $page->layout = $layout;
        $page->save();

        return $item;
    }

    public static function remove(Page $page, string $id): bool
    {
        $layout = self::layout($page);
        foreach ($layout as $i => $item) {
            if ($item['id'] === $id && $item['tpl']) {
                app(PageCloner::class)->purge(self::scope($page, $item));
                unset($layout[$i]);
                $page->layout = array_values($layout);
                $page->save();
                return true;
            }
        }
        return false;
    }

    /** Copy only the texts and lists that this section of the design actually prints, from one scope to another. */
    public static function copyContent(string $fromScope, string $key, string $toScope, ?string $design = null): void
    {
        $design ??= $fromScope;
        $file = resource_path("views/site/parts/$design/$key.blade.php");
        $source = is_file($file) ? (string) file_get_contents($file) : '';

        preg_match_all('/\$T\(\s*[\'"]([^\'"]+)[\'"]/', $source, $m);
        $fieldKeys = array_unique($m[1]);
        preg_match_all('/\$([A-Za-z_][A-Za-z0-9_]*)/', $source, $m);
        $listKeys = ContentList::where('scope', $fromScope)->whereIn('key', array_unique($m[1]))->pluck('key')->all();

        foreach (ContentField::where('scope', $fromScope)->whereIn('key', $fieldKeys)->orderBy('id')->get() as $f) {
            $copy = $f->replicate();
            $copy->scope = $toScope;
            if ($f->type === 'image') {
                $copy->value = Media::duplicate($f->value);
            }
            $copy->save();
        }
        foreach (ContentList::where('scope', $fromScope)->whereIn('key', $listKeys)->orderBy('id')->get() as $l) {
            $copy = $l->replicate();
            $copy->scope = $toScope;
            $copy->items = self::duplicateUploads($l->items ?? []);
            $copy->save();
        }
        Content::forget($toScope);
    }

    private static function duplicateUploads(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => self::duplicateUploads($v), $value);
        }
        return is_string($value) && Media::isUpload($value) ? Media::duplicate($value) : $value;
    }
}
