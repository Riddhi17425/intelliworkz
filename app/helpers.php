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
