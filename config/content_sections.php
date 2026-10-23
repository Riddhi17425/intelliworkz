<?php

/*
|--------------------------------------------------------------------------
| Where a list is edited in the admin panel
|--------------------------------------------------------------------------
| A page's texts and pictures are grouped by section ("Hero", "Editing Tools" ...) but its lists carry a
| generic label ("Tools", "Numbers", "Gallery" ...). Without help the admin showed those lists in separate
| groups at the end of the page. This file tells the editor which section a list belongs to, so everything
| of one visual section is edited in one place, in the order the section appears on the website.
|
|   join     list label  => section names (first one that exists on the page wins)
|   per      design      => same map, only for that design (checked before "join")
|   merge    section name => section it is folded into (same visual section, two admin groups)
|   place    design => list label => ['name' => new section name, 'after' => section it follows]
|            for a section that has lists but no texts of its own (e.g. "03 Numbers" on the About page)
*/

return [
    'join' => [
        'Enquiry form' => ['Hero'],
        'Gallery' => ['Portfolio', 'Selected Work', '360 Viewer + Selected Work', 'Our Examples'],
        'Numbers' => ['Stats'],
        'Why Intelliworkz' => ['What You Get'],
        'Tools' => ['Editing Tools', 'Tools Strip'],
        'Services' => ['Services + Workflow'],
        'Process' => ['Services + Workflow'],
        'What We Offer' => ['What We Offer + CTA + Engagement'],
    ],

    'per' => [
        'manufacturing' => [
            'Services' => ['Dark: Built For Manufacturers'],
            'What We Offer' => ['What We Bring + CTA'],
        ],
        'contact-us' => [
            'Offices' => ['Offices (dark)'],
        ],
    ],

    'merge' => [
        'Dark Showcase (shared)' => 'Dark Showcase',
    ],

    'place' => [
        'about-us' => [
            'Numbers' => ['name' => '03 Numbers', 'after' => '02 Who We Are'],
        ],
        'magazine-design' => [
            'Numbers' => ['name' => 'Stats', 'after' => 'Our Examples'],
            'Tools' => ['name' => 'Tools Strip', 'after' => 'Why Intelliworkz'],
        ],
        'marketing-sales-collaterals' => [
            'Numbers' => ['name' => 'Stats', 'after' => 'Selected Work'],
            'Tools' => ['name' => 'Tools Strip', 'after' => 'Why Intelliworkz'],
        ],
        'seo-services' => [
            'Numbers' => ['name' => 'Stats', 'after' => 'Selected Work'],
        ],
    ],
];
