<?php

/*
 * Small view helpers carried over from the original PHP site so the page templates keep working unchanged.
 * (btn(), section_head(), asset_url() are the same markup the old includes/helpers.php printed.)
 */

if (!function_exists('iu')) {
    /**
     * Image URL for a value coming from the content editor.
     * Original assets are stored as bare file names ("thumb-1.png", relative to $base);
     * anything uploaded in the admin is a full path ("uploads/…") and is used as is.
     */
    function iu(string $base, ?string $value): string
    {
        $v = (string) $value;
        if ($v === '' || preg_match('#^(https?:)?//#', $v) || str_starts_with($v, 'uploads/') || str_starts_with($v, 'assets/')) {
            return $v;
        }
        return $base . $v;
    }
}

if (!function_exists('asset_url')) {
    /** Relative asset URL with a ?v=<mtime> cache buster, exactly like the old header.php. */
    function asset_url(string $path): string
    {
        $file = public_path($path);
        return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
    }
}

if (!function_exists('btn')) {
    function btn(string $label, string $class = '', ?string $icon = null, string $href = '#'): void
    {
        if (trim((string) $href) === '' || trim((string) $href) === '#') {
            $href = 'contact-us.php';   // a button always leads somewhere
        }
        echo '<a href="' . e($href ?: '#') . '" class="btn ' . e($class) . '">' . $label;
        if ($icon) {
            echo '<img src="assets/icons/' . e($icon) . '" width="24" height="24" alt="">';
        }
        echo '</a>';
    }
}

if (!function_exists('section_head')) {
    function section_head(string $title, string $text = '', string $class = ''): void
    {
        echo '<div class="section-head ' . e($class) . '"><h2 class="h2">' . $title . '</h2>';
        if ($text !== '') {
            echo '<p class="lead">' . $text . '</p>';
        }
        echo '</div>';
    }
}

if (!function_exists('ab_pt')) {
    /** Point on a circle (About page diagrams are generated from the capability list). */
    function ab_pt(float $cx, float $cy, float $r, float $deg): array
    {
        $a = deg2rad($deg);
        return [round($cx + $r * cos($a), 1), round($cy + $r * sin($a), 1)];
    }
}

if (!function_exists('contact_html')) {
    /**
     * Turns phone numbers into tel: links and e-mail addresses into mailto: links inside already-sanitised HTML.
     * Text that is already inside a link is left alone.
     */
    function contact_html(?string $html): string
    {
        $html = (string) $html;
        if ($html === '') {
            return '';
        }

        $parts = preg_split('/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $inLink = 0;
        foreach ($parts as $i => $part) {
            if ($part[0] === '<') {
                if (preg_match('/^<a[\s>]/i', $part)) {
                    $inLink++;
                } elseif (preg_match('/^<\/a\s*>/i', $part)) {
                    $inLink = max(0, $inLink - 1);
                }
                continue;
            }
            if ($inLink) {
                continue;
            }
            $part = preg_replace_callback('/[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(?:\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}/', function ($m) {
                return '<a href="mailto:' . e($m[0]) . '">' . e($m[0]) . '</a>';
            }, $part);
            $parts[$i] = preg_replace_callback('/(?<![\w@.\/-])\+?\d[\d\s().-]{5,}\d(?![\w])/', function ($m) {
                $digits = preg_match_all('/\d/', $m[0]);
                if ($digits < 7 || $digits > 15) {
                    return $m[0];
                }
                $tel = (str_starts_with($m[0], '+') ? '+' : '') . preg_replace('/\D/', '', $m[0]);
                return '<a href="tel:' . $tel . '">' . $m[0] . '</a>';
            }, $part);
        }

        return implode('', $parts);
    }
}

if (!function_exists('map_link')) {
    /** Wraps an address in a link that opens it in Google Maps (or in $href when one is given). */
    function map_link(?string $html, ?string $href = null): string
    {
        $html = (string) $html;
        if (trim(strip_tags($html)) === '') {
            return $html;
        }
        if (!$href || !preg_match('#^https?://#i', $href)) {
            $query = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            $href = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query);
        }
        return '<a class="addr-link" href="' . e($href) . '" target="_blank" rel="noopener">' . $html . '</a>';
    }
}

if (!function_exists('tool_icon')) {
    /**
     * App icon in the "tools" strips. Uses the complete 72px icon (tile, border and logo in one SVG, named *-full.svg)
     * when one exists; otherwise the logo is drawn on a tile of the given colour, as before.
     */
    function tool_icon(string $base, string $file, ?string $bg = null): string
    {
        $full = preg_replace('/\.svg$/', '-full.svg', $file);
        if ($full !== $file && is_file(public_path($base . $full))) {
            return '<img class="tool-icon" src="' . e($base . $full) . '" width="72" height="72" alt="">';
        }
        return '<span class="mg-tool__icon" style="background: ' . e((string) $bg) . '"><img src="' . e(iu($base, $file)) . '" alt=""></span>';
    }
}

if (!function_exists('service_href')) {
    /**
     * Link of a service card (arrow buttons, "Explore" cards): the address typed for it, otherwise the service page
     * with the same name in the header menu, otherwise the contact page.
     */
    function service_href(?string $title, ?string $href = null): string
    {
        $href = trim((string) $href);
        if ($href !== '') {
            return $href;
        }

        static $links = null;
        $norm = fn (string $t) => preg_replace('/[^a-z0-9]+/', '', str_replace('&', 'and', mb_strtolower(html_entity_decode(strip_tags($t)))));
        if ($links === null) {
            $links = [];
            foreach (\App\Support\SiteMenus::get()['header'] as $group) {
                foreach ($group->items as $item) {
                    $to = $item->href();
                    if ($to !== '#' && $to !== '') {
                        $links[$norm((string) $item->label)] ??= $to;
                    }
                }
            }
        }

        $name = $norm((string) $title);
        if (isset($links[$name])) {
            return $links[$name];
        }
        foreach ($links as $key => $to) {   // "3D Visualization (Product / Architectural)" belongs to the page "3D Visualization"
            if (strlen($key) >= 10 && str_starts_with($name, $key)) {
                return $to;
            }
        }
        return 'contact-us.php';
    }
}
