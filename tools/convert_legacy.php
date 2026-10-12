<?php
/**
 * One-off importer: legacy PHP page  ->  Blade view + database seed (JSON).
 *
 *   php tools/convert_legacy.php <legacy-dir> [slug ...]
 *
 * For every page it
 *   1. pulls literal copy out of the markup (headings, paragraphs, button labels, content images)
 *      into "fields" and replaces it in the template by {!! $T('section.n') !!};
 *   2. pulls the data arrays the page really uses (includes/data*.php) into "lists";
 *   3. writes resources/views/site/pages/<slug>.blade.php and database/seeders/content/<slug>.json
 *
 * The page markup is otherwise copied byte for byte, so the public design cannot drift.
 */

const VOID_TAGS = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
const SKIP_TAGS = ['html', 'head', 'script', 'style', 'title', 'svg', 'path', 'textarea', 'select', 'option', 'code', 'pre', 'noscript', 'iframe', 'video', 'audio', 'canvas', 'model-viewer', 'form', 'img', 'input', 'picture', 'ul', 'ol', 'dl', 'table', 'thead', 'tbody', 'tr', 'section', 'main', 'article', 'header', 'footer', 'nav', 'aside', 'figure'];
const INLINE_OK = ['br', 'em', 'i', 'b', 'u', 'sup', 'sub', 'small', 'mark', 'strong', 'span', 'a'];

$legacy = rtrim($argv[1] ?? '', '/\\');
if (!$legacy || !is_dir($legacy)) {
    fwrite(STDERR, "usage: php tools/convert_legacy.php <legacy-dir> [slug ...]\n");
    exit(1);
}
$root = dirname(__DIR__);

/** page slug => [legacy file, primary data file (its arrays belong to the page; others become shared)] */
$pages = [
    'index' => ['index.php', 'data.php'],
    'about-us' => ['about-us.php', 'data-about.php'],
    'contact-us' => ['contact-us.php', 'data-contact.php'],
    'video-editing' => ['video-editing.php', 'data-video.php'],
    'magazine-design' => ['magazine-design.php', 'data-magazine.php'],
    '3d-modeling-rendering' => ['3d-modeling-rendering.php', 'data-3d.php'],
    'seo-services' => ['seo-services.php', 'data-seo.php'],
    'marketing-sales-collaterals' => ['marketing-sales-collaterals.php', 'data-collaterals.php'],
    'packaging-design' => ['packaging-design.php', 'data-packaging.php'],
    'ppt-design' => ['ppt-design.php', 'data-ppt.php'],
    'pre-press-service' => ['pre-press-service.php', 'data-prepress.php'],
    'manufacturing' => ['manufacturing.php', 'data-manufacture.php'],
    // shared includes rendered on several pages (content lives in the "global" scope)
    '_showcase' => ['includes/sections/showcase.php', 'data.php', 'partial' => 'sections/showcase'],
    '_engagement' => ['includes/sections/engagement.php', 'data.php', 'partial' => 'sections/engagement'],
];
$only = array_slice($argv, 2);

/** variables that data files define but which are rendered by hand-written partials (menu, footer, settings) */
const PARTIAL_VARS = ['site', 'sub_nav', 'footer_cols'];

function loadDataFile(string $file): array
{
    return (function () use ($file) {
        include $file;
        $vars = get_defined_vars();
        unset($vars['file']);
        return $vars;
    })();
}

/** Strings assigned at the top of the template: $var = 'literal'; */
function pathVars(string $php): array
{
    preg_match_all('/\$(\w+)\s*=\s*\'([^\']*)\'\s*;/', $php, $m, PREG_SET_ORDER);
    $out = [];
    foreach ($m as $x) {
        $out[$x[1]] = $x[2];
    }
    return $out;
}

class Ctx
{
    public string $scope;
    public array $fields = [];
    public array $counters = [];
    public string $section = 'general';
    public string $sectionLabel = 'General';
    public array $paths = [];
    private array $byValue = [];

    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    public function setSection(string $comment): void
    {
        $label = trim(preg_replace('/[=\s]+/', ' ', $comment));
        $this->sectionLabel = ucwords(strtolower($label));
        $this->section = slug($label);
    }

    public function add(string $type, string $label, string $value, string $hint = ''): string
    {
        $n = $this->counters[$this->section] = ($this->counters[$this->section] ?? 0) + 1;
        $key = $this->section . '.' . $n;
        $this->fields[] = [
            'section' => $this->section, 'section_label' => $this->sectionLabel,
            'key' => $key, 'label' => $label, 'type' => $type, 'value' => $value, 'sort' => count($this->fields) + 1,
        ];
        return $key;
    }
}

function slug(string $s): string
{
    $s = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $s), '-'));
    return $s === '' ? 'section' : $s;
}

function kindOf(string $tag, string $attrs): string
{
    return match (true) {
        $tag === 'h1' => 'Main heading',
        $tag === 'h2' => 'Heading',
        $tag === 'h3' => 'Sub-heading',
        $tag === 'h4', $tag === 'h5' => 'Small heading',
        $tag === 'p' => 'Paragraph',
        $tag === 'li' => 'List item',
        $tag === 'label' => 'Form label',
        $tag === 'button' => 'Button text',
        $tag === 'a' && preg_match('/class="[^"]*(btn|pill|text-link)/', $attrs) => 'Button text',
        $tag === 'a' => 'Link text',
        $tag === 'strong', $tag === 'b' => 'Bold label',
        default => 'Text',
    };
}

function preview(string $html, int $len = 46): string
{
    $t = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    return mb_strlen($t) > $len ? mb_substr($t, 0, $len - 1) . '…' : $t;
}

function fieldType(string $value): string
{
    if (preg_match('/<\/?[a-z][a-z0-9]*\b[^>]*>/i', $value)) {
        return 'html';
    }
    return (mb_strlen($value) > 110 || substr_count($value, '. ') >= 2) ? 'textarea' : 'text';
}

/** Is this inner HTML "text + inline markup" only, with real words of its own? */
function leafInner(string $inner, string $tag, string $attrs): bool
{
    if ($inner === '' || str_contains($inner, '{{') || str_contains($inner, '{!!') || str_contains($inner, '<?') || str_contains($inner, '@include') || str_contains($inner, '@php')) {
        return false;
    }
    // every tag inside must be an allowed inline tag
    if (preg_match_all('~</?([a-zA-Z][a-zA-Z0-9-]*)\b~', $inner, $t)) {
        foreach ($t[1] as $name) {
            if (!in_array(strtolower($name), INLINE_OK, true)) {
                return false;
            }
        }
    }
    $breadcrumb = (bool) preg_match('/class="[^"]*breadcrumb/', $attrs);
    if (!$breadcrumb) {
        // text that sits directly in this element (not only inside child tags) is what makes it a "text element"
        $direct = trim(html_entity_decode(strip_tags(preg_replace('~<(\w+)\b[^>]*>.*?</\1>~s', ' ', $inner)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $direct = trim(preg_replace('/<br\s*\/?>/i', ' ', $direct));
        if (!preg_match('/\p{L}{2,}/u', $direct)) {
            return false;
        }
    }
    $plain = html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return (bool) preg_match('/\p{L}{2,}/u', $plain);
}

/** Extract leaf text elements from one inline-HTML chunk. */
function extractChunk(string $html, Ctx $ctx): string
{
    $re = '~<!--(.*?)-->|<(/?)([a-zA-Z][a-zA-Z0-9-]*)\b([^>]*?)(/?)>~s';
    if (!preg_match_all($re, $html, $m, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        return $html;
    }
    $stack = [];
    $cands = [];          // [innerStart, innerEnd, tag, attrs, section, sectionLabel, openEnd, openStart]
    $sectionAt = [];      // position => comment (to resolve the section of every candidate)

    foreach ($m as $tok) {
        $full = $tok[0][0];
        $pos = $tok[0][1];
        if (str_starts_with($full, '<!--')) {
            if (preg_match('/^\s*=+\s*(.+?)\s*=+\s*$/', $tok[1][0], $c)) {
                $sectionAt[$pos] = $c[1];
            }
            continue;
        }
        $closing = $tok[2][0] === '/';
        $name = strtolower($tok[3][0]);
        $attrs = $tok[4][0];
        $self = $tok[5][0] === '/' || in_array($name, VOID_TAGS, true);

        if ($closing) {
            for ($i = count($stack) - 1; $i >= 0; $i--) {
                if ($stack[$i]['name'] === $name) {
                    $open = $stack[$i];
                    array_splice($stack, $i);
                    if (!in_array($name, SKIP_TAGS, true) && !$open['dead']) {
                        $inner = substr($html, $open['end'], $pos - $open['end']);
                        if (leafInner($inner, $name, $open['attrs'])) {
                            $cands[] = ['s' => $open['end'], 'e' => $pos, 'tag' => $name, 'attrs' => $open['attrs'], 'at' => $open['start'], 'inner' => $inner];
                        }
                    }
                    break;
                }
            }
        } elseif (!$self) {
            $stack[] = ['name' => $name, 'attrs' => $attrs, 'start' => $pos, 'end' => $pos + strlen($full), 'dead' => in_array($name, ['script', 'style'], true)];
        }
    }

    // keep the outermost candidate of any nested group
    usort($cands, fn ($a, $b) => $a['s'] <=> $b['s'] ?: $b['e'] <=> $a['e']);
    $chosen = [];
    $lastEnd = -1;
    foreach ($cands as $c) {
        if ($c['s'] >= $lastEnd) {
            $chosen[] = $c;
            $lastEnd = $c['e'];
        }
    }

    // walk the chunk once, switching section at comments and substituting chosen elements
    $events = [];
    foreach ($sectionAt as $p => $name) {
        $events[] = [$p, 'section', $name];
    }
    foreach ($chosen as $c) {
        $events[] = [$c['at'], 'field', $c];
    }
    usort($events, fn ($a, $b) => $a[0] <=> $b[0]);

    $subs = [];
    foreach ($events as [$p, $kind, $payload]) {
        if ($kind === 'section') {
            $ctx->setSection($payload);
            continue;
        }
        $c = $payload;
        $lead = strlen($c['inner']) - strlen(ltrim($c['inner']));
        $trail = strlen($c['inner']) - strlen(rtrim($c['inner']));
        $value = trim($c['inner']);
        $label = kindOf($c['tag'], $c['attrs']) . ': ' . preview($value);
        $key = $ctx->add(fieldType($value), $label, $value);
        $subs[] = [$c['s'], $c['e'], substr($c['inner'], 0, $lead) . "{!! \$T('{$key}') !!}" . ($trail ? substr($c['inner'], -$trail) : '')];

        // button / link targets
        if ($c['tag'] === 'a' && preg_match('/class="[^"]*\b(btn|pill|text-link|mf-btn)/', $c['attrs']) && preg_match('/href="([^"{<]*)"/', $c['attrs'], $h)) {
            $urlKey = $ctx->add('url', 'Button link: ' . preview($value, 30), $h[1]);
            // the href lives in the opening tag; it is rewritten after the inner replacements
            $subs[] = ['href', $c['at'], $urlKey];
        }
    }

    // apply: href rewrites first (they live in the opening tag, before the inner range), then inner replacements from the end
    $hrefs = array_filter($subs, fn ($s) => $s[0] === 'href');
    $inner = array_filter($subs, fn ($s) => $s[0] !== 'href');
    usort($inner, fn ($a, $b) => $b[0] <=> $a[0]);
    foreach ($inner as [$s, $e, $new]) {
        $html = substr($html, 0, $s) . $new . substr($html, $e);
    }
    // hrefs: positions are of the opening tag start; apply from the end as well, re-locating after inner edits
    usort($hrefs, fn ($a, $b) => $b[1] <=> $a[1]);
    foreach ($hrefs as [, $at, $urlKey]) {
        // inner edits happened after $at, so the opening tag text is unchanged at that offset
        if (preg_match('/\G<a\b[^>]*?href="([^"{<]*)"/s', $html, $mm, PREG_OFFSET_CAPTURE, $at)) {
            $valStart = $mm[1][1];
            $html = substr($html, 0, $valStart) . "{{ \$T('{$urlKey}') ?: '#' }}" . substr($html, $valStart + strlen($mm[1][0]));
        }
    }
    return $html;
}

require __DIR__ . '/prepass.php';

function convertPage(string $slug, string $legacy, string $root, array $pages): void
{
    [$file, $primary] = $pages[$slug];
    $partial = $pages[$slug]['partial'] ?? null;     // shared include (showcase, engagement): scope "global"
    $src = file_get_contents("$legacy/$file");

    if ($partial) {
        $topPhp = '';
        $rest = $src;
    } else {
        if (!preg_match('~^<\?php\s*(.*?)\?>\s*~s', $src, $top)) {
            throw new RuntimeException("no top block in $file");
        }
        $topPhp = $top[1];
        $rest = substr($src, strlen($top[0]));
    }

    // tail: the final PHP block that configures + requires the footer
    $tail = '';
    $lastOpen = strrpos($rest, '<?php');
    if ($lastOpen !== false && str_contains(substr($rest, $lastOpen), 'includes/footer.php')) {
        $tail = substr($rest, $lastOpen);
        $rest = substr($rest, 0, $lastOpen);
    }

    // ---- page configuration (kept in config/site_pages.php, not in the view)
    $cfg = ['title' => '', 'body_class' => '', 'extra_css' => [], 'no_zoom' => false, 'show_prefooter' => true, 'footer_class' => '', 'phone_icon' => null];
    if (preg_match('/\$page_title\s*=\s*\'((?:[^\'\\\\]|\\\\.)*)\'/', $topPhp, $x)) $cfg['title'] = stripslashes($x[1]);
    if (preg_match('/\$body_class\s*=\s*\'([^\']*)\'/', $topPhp, $x)) $cfg['body_class'] = $x[1];
    if (preg_match('/\$extra_css\s*=\s*\[(.*?)\]/s', $topPhp, $x)) preg_match_all('/\'([^\']+)\'/', $x[1], $y) && $cfg['extra_css'] = $y[1];
    if (preg_match('/\$no_zoom\s*=\s*true/', $topPhp)) $cfg['no_zoom'] = true;
    if (preg_match('/\$show_prefooter\s*=\s*false/', $tail)) $cfg['show_prefooter'] = false;
    if (preg_match('/\$footer_class\s*=\s*\'([^\']*)\'/', $tail, $x)) $cfg['footer_class'] = $x[1];
    if (preg_match('/\$phone_icon\s*=\s*\'([^\']*)\'/', $tail, $x)) $cfg['phone_icon'] = $x[1];

    // ---- the top block minus the lines the framework now owns
    $keep = [];
    foreach (preg_split('/\R/', $topPhp) as $line) {
        if (preg_match('/^\s*require\b/', $line) || preg_match('/^\s*\$(page_title|body_class|extra_css|no_zoom)\b/', $line)) {
            continue;
        }
        $keep[] = $line;
    }
    $keepPhp = trim(implode("\n", $keep));

    $ctx = new Ctx($partial ? 'global' : $slug);
    $ctx->paths = pathVars($keepPhp);

    // ---- data arrays this page really uses
    $usedVars = [];
    foreach (token_get_all('<?php ' . $keepPhp . ' ?>' . $rest) as $tk) {
        if (is_array($tk) && $tk[0] === T_VARIABLE) {
            $usedVars[ltrim($tk[1], '$')] = true;
        }
    }
    $requires = [];
    preg_match_all('~require __DIR__ \. \'/includes/(data[\w-]*\.php)\'~', $topPhp, $rq);
    foreach ($rq[1] as $f) {
        $requires[] = $f;
    }
    $requires[] = $primary;   // e.g. index.php gets data.php through header.php, not through its own require lines
    $lists = [];
    $strings = [];
    foreach (array_unique($requires) as $df) {
        $vars = loadDataFile("$legacy/includes/$df");
        foreach ($vars as $name => $value) {
            if (!isset($usedVars[$name]) || in_array($name, PARTIAL_VARS, true)) {
                continue;
            }
            $shared = $partial || ($df !== $primary);
            if (is_array($value)) {
                $lists[$name] = ['scope' => $shared ? 'global' : $slug, 'items' => $value];
            } elseif (is_string($value)) {
                $strings[$name] = $value;
            }
        }
    }

    // ---- section partials / header include
    $rest = preg_replace('~<\?php\s+require __DIR__ \. "/includes/site-header\.php";\s*\?>|<\?php\s+require __DIR__ \. \'/includes/site-header\.php\';\s*\?>~', "@include('site.partials.site-header')", $rest);
    // the shared "dark showcase" block: a PHP block that sets $showcase_title (and sometimes $showcase_decor), then requires the section
    $rest = preg_replace_callback(
        '~<\?php\s*((?:(?!\?>).)*?)require __DIR__ \. \'/includes/sections/showcase\.php\';\s*\?>~s',
        function ($m) use ($ctx) {
            $block = $m[1];
            if (preg_match('~\$showcase_title\s*=\s*\'((?:[^\'\\\\]|\\\\.)*)\';[^\n]*~', $block, $st)) {
                $ctx->setSection('Dark Showcase');
                $title = stripslashes($st[1]);
                $key = $ctx->add('text', 'Heading: ' . preview($title, 36), $title);
                $block = str_replace($st[0], "\$showcase_title = \$T('{$key}');", $block);
            }
            return "@php\n" . trim($block) . "\n@endphp\n    @include('site.sections.showcase')";
        },
        $rest
    );
    $rest = preg_replace('~<\?php\s+require __DIR__ \. \'/includes/sections/showcase\.php\';\s*\?>~', "@include('site.sections.showcase')", $rest);
    $rest = preg_replace('~<\?php\s+require __DIR__ \. \'/includes/sections/engagement\.php\';\s*\?>~', "@include('site.sections.engagement')", $rest);
    $rest = preg_replace('~<\?php\s+require __DIR__ \. \'/price-badge\.php\';\s*\?>~', "@include('site.sections.price-badge')", $rest);

    // ---- string data vars (e.g. $industry_text) become fields
    $stringPhp = '';
    foreach ($strings as $name => $value) {
        $ctx->setSection('Shared copy');
        $key = $ctx->add(fieldType($value), ucfirst(str_replace('_', ' ', $name)) . ': ' . preview($value, 30), $value);
        $stringPhp .= "\$$name = \$T('$key');\n";
    }

    // ---- literal copy -> fields.  Pre-pass on the whole body, then per inline-HTML chunk.
    $ctx->setSection('General');
    $rest = prepass($rest, $ctx);

    $tokens = token_get_all('?>' . $rest);
    $out = '';
    foreach ($tokens as $i => $tk) {
        if (!is_array($tk)) {
            $out .= $tk;
            continue;
        }
        if ($tk[0] === T_INLINE_HTML) {
            $out .= extractChunk($tk[1], $ctx);
        } else {
            $out .= $tk[1];
        }
    }
    $out = preg_replace('/^\?>\R?/', '', $out, 1);

    // ---- write the view
    $php = trim($stringPhp . $keepPhp);
    if ($partial) {
        // shared include: reads the "global" scope through $g
        $blade = ltrim(str_replace('$T(', '$G(', $out)) . "\n";
        $viewFile = "$root/resources/views/site/$partial.blade.php";
    } else {
        $blade = "@extends('site.layout')\n\n@section('content')\n";
        if ($php !== '') {
            $blade .= "@php\n" . $php . "\n@endphp\n";
        }
        $blade .= ltrim($out) . "\n@endsection\n";
        $viewFile = "$root/resources/views/site/pages/$slug.blade.php";
    }
    @mkdir(dirname($viewFile), 0777, true);
    file_put_contents($viewFile, $blade);

    // ---- write the seed
    @mkdir("$root/database/seeders/content", 0777, true);
    $seed = [
        'page' => $partial ? null : ['slug' => $slug, 'config' => $cfg],
        'fields' => $ctx->fields,
        'lists' => array_map(fn ($l, $k) => ['key' => $k, 'scope' => $l['scope'], 'items' => $l['items']], $lists, array_keys($lists)),
    ];
    file_put_contents("$root/database/seeders/content/$slug.json", json_encode($seed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    printf("%-30s fields=%-4d lists=%-3d (%s)\n", $slug, count($ctx->fields), count($lists), implode(',', array_keys($lists)));
}

foreach ($pages as $slug => $_) {
    if ($only && !in_array($slug, $only, true)) {
        continue;
    }
    convertPage($slug, $legacy, $root, $pages);
}

// ---- config/site_pages.php: per-page settings (body class, extra CSS, footer options) collected from every seed on disk
$config = [];
foreach ($pages as $slug => $_) {
    $f = $root . '/database/seeders/content/' . $slug . '.json';
    $page = is_file($f) ? (json_decode(file_get_contents($f), true)['page'] ?? null) : null;
    if ($page) {
        $config[$slug] = $page['config'];
    }
}
file_put_contents(
    $root . '/config/site_pages.php',
    "<?php\n\n/** Generated by tools/convert_legacy.php. Per-page frontend settings (what the old PHP header/footer variables held). */\nreturn " . var_export($config, true) . ";\n"
);

