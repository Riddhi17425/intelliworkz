<?php
/**
 * Pre-pass: patterns that straddle PHP/HTML boundaries and so cannot be seen inside one inline-HTML chunk.
 * Each match is attributed to the section whose "<!-- === NAME === -->" comment precedes it in the file.
 */

/** offsets of every "<!-- ==== NAME ==== -->" comment in the body, in order */
function sectionMarkers(string $body): array
{
    $out = [];
    if (preg_match_all('~<!--\s*=+\s*(.+?)\s*=+\s*-->~', $body, $m, PREG_OFFSET_CAPTURE)) {
        foreach ($m[1] as $hit) {
            $out[] = [$hit[1], $hit[0]];
        }
    }
    return $out;
}

/** Run $fn with the context switched to the section that applies at $offset. */
function inSection(Ctx $ctx, array $markers, int $offset, callable $fn)
{
    $saveSection = $ctx->section;
    $saveLabel = $ctx->sectionLabel;
    $name = null;
    foreach ($markers as [$pos, $comment]) {
        if ($pos <= $offset) {
            $name = $comment;
        }
    }
    if ($name !== null) {
        $ctx->setSection($name);
    } else {
        $ctx->setSection('General');
    }
    $result = $fn();
    $ctx->section = $saveSection;
    $ctx->sectionLabel = $saveLabel;
    return $result;
}

function prepass(string $body, Ctx $ctx): string
{
    // 0) the enquiry forms post to the CMS: CSRF token, which page it came from, a honeypot, and a line for the result message
    $body = preg_replace(
        '~<form class="(lead-form|ct-card)" action="#" method="post">~',
        '<form class="$1" action="{{ route(\'inquiries.store\') }}" method="post" data-inquiry-form novalidate>' . "\n"
        . '@csrf' . "\n"
        . '<input type="hidden" name="source" value="{{ $page->slug }}">' . "\n"
        . '<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">',
        $body
    );
    $body = preg_replace(
        '~(<form class="(?:lead-form|ct-card)"[^>]*data-inquiry-form.*?)(\s*)(<button type="submit")~s',
        '$1$2<p class="form-status" data-form-status role="status" aria-live="polite"></p>$2$3',
        $body
    );
    // field names come from a stable 4th value in $ve_form_fields, so renaming a label in the admin cannot break the form
    $body = str_replace(
        "strtolower(str_replace(' ', '_', \$f[0]))",
        "(\$f[3] ?? strtolower(str_replace(' ', '_', \$f[0])))",
        $body
    );

    $markers = sectionMarkers($body);

    // 1) content images:  src="(php echo of $si)file.png"   or   src="assets/images/..."
    $body = preg_replace_callback(
        '~<img\b([^>]*?)\bsrc="(?:<\?=\s*\$(\w+)\s*\?>([^"<>?]+)|(assets/images/[^"<>?]+))"([^>]*)>~s',
        function ($m) use ($ctx, $markers) {
            $path = ($m[4][0] ?? '') !== '' ? $m[4][0] : (isset($ctx->paths[$m[2][0]]) ? $ctx->paths[$m[2][0]] . $m[3][0] : null);
            if ($path === null || !str_starts_with($path, 'assets/images/') || !preg_match('/\.(png|jpe?g|webp|gif)$/i', $path)) {
                return $m[0][0];
            }
            $alt = preg_match('/alt="([^"]*)"/', $m[0][0], $a) ? $a[1] : '';
            $key = inSection($ctx, $markers, $m[0][1], fn () => $ctx->add('image', 'Image' . ($alt !== '' ? ': ' . preview($alt, 30) : '') . ' (' . basename($path) . ')', $path));
            return '<img' . $m[1][0] . 'src="{{ $T(\'' . $key . '\') }}"' . $m[5][0] . '>';
        },
        $body,
        -1,
        $n,
        PREG_OFFSET_CAPTURE
    );
    // offsets of later passes refer to the *new* body, so markers must be recomputed
    $markers = sectionMarkers($body);

    // 2) helper calls with a literal label:  btn('Label', ...)   section_head('Title', 'Lead', ...)
    $body = preg_replace_callback(
        "~\\bbtn\\(\\s*'((?:[^'\\\\]|\\\\.)*)'~",
        function ($m) use ($ctx, $markers) {
            $value = stripslashes($m[1][0]);
            // "Explore " . $name  ->  the editable part is trimmed; the separating space is added back in code
            $gap = ($value !== rtrim($value)) ? " . ' '" : '';
            $value = rtrim($value);
            $key = inSection($ctx, $markers, $m[0][1], fn () => $ctx->add('text', 'Button text: ' . preview($value, 30), $value));
            return "btn(\$T('{$key}')" . $gap;
        },
        $body,
        -1,
        $n,
        PREG_OFFSET_CAPTURE
    );
    $markers = sectionMarkers($body);

    $body = preg_replace_callback(
        "~\\bsection_head\\(\\s*'((?:[^'\\\\]|\\\\.)*)'(?:\\s*,\\s*'((?:[^'\\\\]|\\\\.)*)')?~",
        function ($m) use ($ctx, $markers) {
            return inSection($ctx, $markers, $m[0][1], function () use ($m, $ctx) {
                $title = stripslashes($m[1][0]);
                $out = "section_head(\$T('" . $ctx->add(fieldType($title), 'Heading: ' . preview($title, 36), $title) . "')";
                if (isset($m[2]) && $m[2][0] !== '') {
                    $lead = stripslashes($m[2][0]);
                    $out .= ", \$T('" . $ctx->add(fieldType($lead), 'Intro: ' . preview($lead, 36), $lead) . "')";
                }
                return $out;
            });
        },
        $body,
        -1,
        $n,
        PREG_OFFSET_CAPTURE
    );

    // 3a) a literal folder followed by a list value:  assets/images/<echo $m['img']>  ->  iu('assets/images/', $m['img'])
    $body = preg_replace_callback(
        '~(assets/(?:images|icons)/[\w/.-]*)<\?=\s*(\$\w+(?:\[[^\]]+\])+)\s*\?>~',
        fn ($m) => '<?= iu(\'' . $m[1] . '\', ' . $m[2] . ') ?>',
        $body
    );

    // 3b) image paths composed from a base variable and a list value  ->  iu($base, $value)
    $body = preg_replace_callback(
        '~<\?=\s*(\$\w+|\((?:[^()]|\([^()]*\))*\))\s*\.\s*(\$\w+(?:\[[^\]]+\])+)\s*\?>~',
        fn ($m) => '<?= iu(' . $m[1] . ', ' . $m[2] . ') ?>',
        $body
    );
    return $body;
}
