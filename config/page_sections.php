<?php

/**
 * Sections of a page design that an editor may switch on/off (key => label), per design (template).
 * The key is what the Blade template asks for with $show('key'); the hero is always shown.
 * A design that is not listed here simply has no switches.
 */
return [
    '3d-modeling-rendering' => [
        'viewer' => '360° viewer & selected work',
        'stats' => 'Stats',
        'why' => 'Why Intelliworkz',
        'tools' => 'Tools strip',
        'services' => 'Services & workflow',
        'showcase' => 'Dark showcase slider (shared)',
        'offers' => 'What we offer',
        'cta' => 'Call-to-action band',
        'engagement' => 'Engagement models (shared)',
        'faq' => 'FAQs',
    ],
];
