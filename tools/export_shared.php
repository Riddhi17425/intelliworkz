<?php
/**
 * Dumps the shared pieces of the legacy site (header menu, footer columns, offices, copyright)
 * into database/seeders/content/_shared.json so the seeder can load them.
 *
 *   php tools/export_shared.php <legacy-dir>
 */
$legacy = rtrim($argv[1] ?? '', '/\\');
if (!is_file("$legacy/includes/data.php")) {
    fwrite(STDERR, "usage: php tools/export_shared.php <legacy-dir>\n");
    exit(1);
}
require "$legacy/includes/data.php";

$header = [];
foreach ($sub_nav as $title => $links) {
    $items = [];
    foreach ($links as $label => $url) {
        $items[] = ['label' => $label, 'url' => $url];
    }
    $header[] = ['title' => $title, 'items' => $items];
}

$footer = [];
foreach ($footer_cols as $handle => [$title, $links]) {
    $footer[] = ['handle' => $handle, 'title' => $title, 'items' => array_map(fn ($l) => ['label' => $l, 'url' => '#'], $links)];
}

$out = [
    'site' => $site,
    'menus' => ['header' => $header, 'footer' => $footer],
    'offices' => $offices,
];
$root = dirname(__DIR__);
@mkdir("$root/database/seeders/content", 0777, true);
file_put_contents("$root/database/seeders/content/_shared.json", json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
printf("header groups: %d, footer groups: %d, offices: %d\n", count($header), count($footer), count($offices));
