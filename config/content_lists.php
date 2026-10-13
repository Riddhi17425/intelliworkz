<?php

/*
|--------------------------------------------------------------------------
| Editor description of every repeatable list
|--------------------------------------------------------------------------
| The page templates read these lists as plain PHP arrays (see content_lists in the database). This file
| only tells the admin editor how to present them:
|
|   label    heading shown above the list
|   section  tab it appears under (page-specific names are matched by the importer's section labels)
|   title    which field names the item in the collapsed card
|   kind     rows (default) | strings | map | maprows | groups
|   add / remove / sort   allow adding, deleting, re-ordering (default true; false where the design needs a fixed count)
|   fields   editable fields as  key => [label, type = text, extra]. Keys that are not listed (colours, pixel
|            positions, CSS, icons …) are kept untouched, so the design cannot be broken from the admin.
|
| Field types: text, textarea, html, url, image, lines (one entry per line), repeater (sub-rows, needs 'fields').
| Numeric keys address positional rows such as ['Question', 'Answer'].
*/

$faq = ['label' => 'Questions & answers', 'section' => 'FAQs', 'title' => 0, 'sort' => 90, 'fields' => [0 => ['Question'], 1 => ['Answer', 'textarea']]];
$offer = ['label' => '"What we offer" cards', 'section' => 'What We Offer', 'title' => 'title', 'sort' => 80, 'add' => false, 'remove' => false,
    'fields' => ['title' => ['Title'], 'text' => ['Description', 'textarea'], 'img' => ['Picture', 'image'], 'href' => ['Link of the arrow (page address)', 'url']]];
$services = ['label' => 'Services accordion', 'section' => 'Services', 'title' => 'title', 'sort' => 60, 'fields' => ['title' => ['Service name'], 'points' => ['Points (one per line)', 'lines']]];
$benefits = ['label' => 'Benefit chips under the intro', 'section' => 'Hero', 'kind' => 'strings', 'sort' => 5];
$statsPair = ['label' => 'Statistic', 'section' => 'Numbers', 'title' => 1, 'sort' => 30, 'add' => false, 'remove' => false, 'fields' => [0 => ['Number'], 1 => ['Label']]];
$metricsTriple = ['label' => 'Dark number strip', 'section' => 'Numbers', 'title' => 2, 'sort' => 30, 'add' => false, 'remove' => false, 'fields' => [1 => ['Number'], 2 => ['Label']]];
$toolsPlain = ['label' => 'Tools', 'section' => 'Tools', 'title' => 1, 'sort' => 50, 'fields' => [0 => ['Logo', 'image'], 1 => ['Tool name'], 2 => ['Tagline (e.g. Edit | Assemble)']]];
$toolsColour = ['label' => 'Tools', 'section' => 'Tools', 'title' => 2, 'sort' => 50, 'fields' => [0 => ['Logo', 'image'], 2 => ['Tool name'], 3 => ['Tagline (e.g. Layout | Publish)']]];
$toolsNested = ['label' => 'Tools', 'section' => 'Tools', 'kind' => 'groups', 'group' => 'Column', 'title' => 1, 'sort' => 50, 'add' => false, 'remove' => false,
    'fields' => [0 => ['Logo', 'image'], 1 => ['Tool name'], 2 => ['Tagline']]];
$gallery = ['label' => 'Gallery images', 'section' => 'Gallery', 'title' => 'cats', 'sort' => 20,
    'fields' => ['img' => ['Image', 'image'], 'cats' => ['Filter categories (keys, space separated)', 'text', 'help' => 'Must match the filter tabs above, e.g. "strategy leads".']]];
$filters = ['label' => 'Filter tab names', 'section' => 'Gallery', 'kind' => 'map', 'sort' => 19, 'add' => false, 'remove' => false];
$why = ['label' => '"Why Intelliworkz" cards', 'section' => 'Why Intelliworkz', 'title' => 'title', 'sort' => 40, 'add' => false, 'remove' => false,
    'fields' => ['title' => ['Heading'], 'text' => ['Description', 'textarea']]];
$whyBg = ['label' => '"Why Intelliworkz" cards', 'section' => 'Why Intelliworkz', 'title' => 'title', 'sort' => 40, 'add' => false, 'remove' => false,
    'fields' => ['title' => ['Heading'], 'text' => ['Description', 'textarea'], 'bg' => ['Background picture', 'image']]];
$workflow = ['label' => 'Process steps', 'section' => 'Process', 'title' => 'title', 'sort' => 70, 'fields' => ['title' => ['Step title'], 'text' => ['Description', 'textarea'], 'img' => ['Picture', 'image']]];
$workflowTuple = ['label' => 'Process steps', 'section' => 'Process', 'title' => 9, 'sort' => 70, 'add' => false, 'remove' => false,
    'fields' => [9 => ['Step title'], 10 => ['Description', 'textarea'], 4 => ['Picture', 'image']]];

return [
    // ---------- shared by every page (scope "global") ----------
    'offices' => ['label' => 'Office cards in the footer', 'section' => 'Offices', 'title' => 'name', 'sort' => 1,
        'fields' => ['name' => ['Country / office'], 'address' => ['Address', 'html'], 'phone' => ['Phone number(s)'], 'flag' => ['Flag', 'image'], 'map' => ['Map picture', 'image']]],
    've_form_fields' => ['label' => 'Enquiry form fields', 'section' => 'Enquiry form', 'title' => 0, 'sort' => 2, 'add' => false, 'remove' => false, 'reorder' => false,
        'fields' => [0 => ['Field label'], 1 => ['Placeholder']]],
    'showcase_cards' => ['label' => 'Dark showcase slides', 'section' => 'Dark Showcase', 'title' => 'tag', 'sort' => 3, 'add' => false, 'remove' => false,
        'fields' => ['img' => ['Picture', 'image'], 'tag' => ['Tag'], 'title' => ['Heading (leave empty for the shared one)'], 'text' => ['Text (leave empty for the shared one)', 'textarea'], 'href' => ['Button link (page address)', 'url'],
            'stats' => ['Three numbers', 'repeater', 'fields' => [0 => ['Number'], 1 => ['Label']]]]],
    'engagement_models' => ['label' => 'Engagement models', 'section' => 'Engagement Model', 'title' => 'title', 'sort' => 4, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Title', 'html'], 'tag' => ['Tag', 'html'], 'img' => ['Picture', 'image'], 'bullets' => ['Points (one per line)', 'lines']]],

    // ---------- home page ----------
    'metrics' => ['label' => 'Number strip', 'section' => 'Hero', 'title' => 'label', 'sort' => 10, 'add' => false, 'remove' => false, 'fields' => ['value' => ['Number'], 'label' => ['Label']]],
    'domains' => ['label' => 'Service cards', 'section' => 'Services', 'title' => 'title', 'sort' => 20, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Card title'], 'intro' => ['Introduction', 'textarea'], 'items' => ['Links (one per line; add  | page-address  to link one)', 'lines'], 'cta' => ['Button text'], 'href' => ['Button link', 'url']]],
    'outsourcing_steps' => ['label' => '4-step engagement process', 'section' => 'Outsourcing', 'title' => 'title', 'sort' => 30, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Step title'], 'text' => ['Description', 'textarea']]],
    'outsourcing_cards' => ['label' => 'Outsourcing cards', 'section' => 'Outsourcing', 'title' => 'title', 'sort' => 31, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Title'], 'lead' => ['Introduction', 'textarea'], 'items' => ['Bullet points (one per line)', 'lines'], 'text' => ['Text', 'textarea'], 'img' => ['Picture', 'image']]],
    'industries' => ['label' => 'Industry cards', 'section' => 'Industries', 'title' => 'title', 'sort' => 40,
        'fields' => ['title' => ['Industry'], 'text' => ['Hover text', 'textarea'], 'img' => ['Photo', 'image']]],
    'reasons' => ['label' => 'Reasons to choose us', 'section' => 'Why Intelliworkz', 'title' => 0, 'sort' => 50, 'add' => false, 'remove' => false, 'fields' => [0 => ['Heading'], 1 => ['Description', 'textarea']]],
    'products_data' => ['label' => 'Products', 'section' => 'Products', 'title' => 'name', 'sort' => 60, 'add' => false, 'remove' => false,
        'fields' => ['name' => ['Product name'], 'tag' => ['Tagline'], 'text' => ['Description', 'textarea'], 'img' => ['Screenshot', 'image'],
            'features' => ['Key capabilities', 'repeater', 'fields' => [0 => ['Capability'], 1 => ['What it means', 'textarea']]]]],
    'process_row_1' => ['label' => 'Process cards (top row)', 'section' => 'Process', 'title' => 'title', 'sort' => 70, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Title'], 'text' => ['Description', 'textarea'], 'img' => ['Photo', 'image']]],
    'process_row_2' => ['label' => 'Process cards (bottom row)', 'section' => 'Process', 'title' => 'title', 'sort' => 71, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Title'], 'text' => ['Description', 'textarea'], 'img' => ['Photo', 'image']]],
    'blog_posts' => ['label' => 'Insight posts', 'section' => 'Insights', 'title' => 'title', 'sort' => 80,
        'fields' => ['img' => ['Image', 'image'], 'date' => ['Date'], 'title' => ['Title'], 'href' => ['Read More link (page address)', 'url']]],

    // ---------- about ----------
    'ab_metrics' => ['label' => 'Number strip', 'section' => 'Numbers', 'title' => 'label', 'sort' => 5, 'add' => false, 'remove' => false, 'fields' => ['value' => ['Number'], 'label' => ['Label']]],
    'ab_purpose' => ['label' => 'Purpose points', 'section' => '04 Our Purpose', 'title' => 'title', 'sort' => 10, 'add' => false, 'remove' => false, 'fields' => ['title' => ['Title'], 'text' => ['Short text', 'textarea']]],
    'ab_nodes' => ['label' => 'Capability circles around the centre', 'section' => '05 Our Approach', 'title' => 'label', 'sort' => 12, 'add' => false, 'remove' => false, 'fields' => ['label' => ['Name']]],
    'ab_reasons' => ['label' => 'Reasons to choose us', 'section' => '07 Why Choose Us', 'title' => 'title', 'sort' => 16, 'add' => false, 'remove' => false, 'fields' => ['title' => ['Title'], 'text' => ['Short text', 'textarea']]],
    // ---------- contact ----------
    'ct_offices' => ['label' => 'Office details (tabs)', 'section' => 'Offices', 'kind' => 'maprows', 'title' => 'country', 'sort' => 10, 'add' => false, 'remove' => false,
        'fields' => ['tab' => ['Tab label'], 'country' => ['Country'], 'city' => ['City / region'], 'phone' => ['Phone number(s)'], 'email' => ['E-mail'], 'call' => ['Phone number for the "Call" button (digits only)'],
            'map' => ['Google Maps link', 'url'],
            'blocks' => ['Main address', 'repeater', 'wrap' => true, 'fields' => [0 => ['Label'], 1 => ['Address', 'textarea']]],
            'blocks2' => ['Other divisions', 'repeater', 'fields' => [0 => ['Division'], 1 => ['Address', 'textarea']]]]],
    'ct_pins' => ['label' => 'Map labels', 'section' => 'Offices', 'title' => 0, 'sort' => 11, 'add' => false, 'remove' => false, 'fields' => [0 => ['Label']]],
    'ct_faqs' => $faq,

    // ---------- video editing ----------
    've_benefits' => $benefits,
    've_filters' => $filters,
    've_cards' => ['label' => 'Portfolio videos', 'section' => 'Portfolio', 'title' => 'title', 'sort' => 20,
        'fields' => ['title' => ['Title'], 'meta' => ['Categories shown under the title', 'html'], 'cats' => ['Filter categories (keys, space separated)'], 'video' => ['Video file or link', 'url']]],
    've_stats_top' => $statsPair, 've_stats_bottom' => $statsPair,
    've_pillars' => ['label' => 'Feature cards', 'section' => 'Why Intelliworkz', 'title' => 'title', 'sort' => 40, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Heading'], 'text' => ['Description', 'textarea'], 'img' => ['Background picture', 'image']]],
    've_tools' => $toolsPlain,
    've_services' => $services,
    've_workflow' => $workflowTuple,
    've_offers' => $offer,
    've_faqs' => $faq,

    // ---------- magazine ----------
    'mg_benefits' => $benefits,
    'mg_examples' => ['label' => 'Example strip', 'section' => 'Our Examples', 'title' => 'type', 'sort' => 20, 'add' => false, 'remove' => false, 'fields' => ['img' => ['Picture', 'image']]],
    'mg_metrics' => $metricsTriple,
    'mg_why' => $why,
    'mg_tools' => $toolsColour,
    'mg_services' => $services,
    'mg_flow' => ['label' => 'Workflow steps', 'section' => 'Process', 'title' => 'title', 'sort' => 70, 'fields' => ['title' => ['Step title'], 'text' => ['Description', 'textarea']]],
    'mg_offers' => $offer,
    'mg_faqs' => $faq,

    // ---------- 3D ----------
    'td_benefits' => $benefits,
    'td_filters' => $filters,
    'td_gallery' => $gallery,
    'td_stats_top' => $statsPair, 'td_stats_bottom' => $statsPair,
    'td_benefit_cards' => $why,
    'td_tools' => $toolsPlain,
    'td_tool_formats_label' => ['label' => 'Formats label', 'section' => 'Tools', 'kind' => 'strings', 'add' => false, 'remove' => false, 'sort' => 51],
    'td_services' => $services,
    'td_workflow' => $workflow,
    'td_offers' => $offer,
    'td_faqs' => $faq,

    // ---------- SEO ----------
    'seo_benefits' => $benefits,
    'seo_filters' => $filters,
    'seo_gallery' => $gallery,
    'seo_metrics' => $metricsTriple,
    'seo_why' => $why,
    'seo_tools' => $toolsNested,
    'seo_services' => $services,
    'seo_workflow' => $workflow,
    'seo_offers' => $offer,
    'seo_faqs' => $faq,

    // ---------- collaterals ----------
    'cl_benefits' => $benefits,
    'cl_filters' => $filters,
    'cl_strip' => $gallery,
    'cl_metrics' => $metricsTriple,
    'cl_why' => $whyBg,
    'cl_tools' => $toolsColour,
    'cl_services' => $services,
    'cl_flow' => ['label' => 'Workflow steps', 'section' => 'Process', 'title' => 'title', 'sort' => 70, 'add' => false, 'remove' => false, 'fields' => ['title' => ['Step title'], 'text' => ['Description', 'textarea']]],
    'cl_flow_next' => ['label' => 'Arrow positions', 'section' => 'Process', 'hidden' => true],
    'cl_related' => ['label' => 'Related service cards', 'section' => 'What We Offer + Cta + Engagement', 'title' => 'title', 'sort' => 80,
        'fields' => ['title' => ['Service name'], 'text' => ['Description', 'textarea'], 'href' => ['Card link (page address)', 'url']]],
    'cl_faqs' => $faq,

    // ---------- packaging ----------
    'pk_benefits' => $benefits,
    'pk_filters' => $filters,
    'pk_tiles' => ['label' => 'Work grid (one card per position; each tab has its own picture)', 'section' => 'Gallery', 'title' => 'img', 'sort' => 20, 'add' => false, 'remove' => false,
        'fields' => ['img' => ['Picture on the "All" tab', 'image'], 'rollout' => ['Picture on the "Packaging Rollout" tab', 'image'], 'beauty' => ['Picture on the "Beauty & Skincare" tab', 'image'],
            'food' => ['Picture on the "Food & Beverage" tab', 'image'], 'appliance' => ['Picture on the "Appliance & Electronics" tab', 'image']]],
    'pk_stats' => ['label' => 'Statistics', 'section' => 'Numbers', 'kind' => 'groups', 'group' => 'Row', 'title' => 2, 'sort' => 30, 'add' => false, 'remove' => false,
        'fields' => [0 => ['Number'], 1 => ['Suffix (e.g. +)'], 2 => ['Label', 'html']]],
    'pk_why' => $whyBg,
    'pk_tools' => $toolsColour,
    'pk_services' => $services,
    'pk_flow' => $workflow,
    'pk_offers' => $offer,
    'pk_faqs' => $faq,

    // ---------- PPT ----------
    'ppt_benefits' => $benefits,
    'ppt_filters' => $filters,
    'ppt_thumbs' => $gallery,
    'ppt_stats_top' => $statsPair, 'ppt_stats_bottom' => $statsPair,
    'ppt_why' => $why,
    'ppt_tools' => ['label' => 'Tools', 'section' => 'Tools', 'kind' => 'groups', 'group' => 'Column', 'title' => 3, 'sort' => 50, 'add' => false, 'remove' => false,
        'fields' => [1 => ['Logo', 'image'], 3 => ['Tool name'], 4 => ['Tagline']]],
    'ppt_services' => $services,
    'ppt_workflow' => $workflowTuple,
    'ppt_offers' => $offer,
    'ppt_faqs' => $faq,

    // ---------- pre-press ----------
    'pp_benefits' => $benefits,
    'pp_filters' => $filters,
    'pp_strip' => $gallery,
    'pp_stats_top' => $statsPair, 'pp_stats_bottom' => $statsPair,
    'pp_why' => $why,
    'pp_tools' => ['label' => 'Tools', 'section' => 'Tools', 'kind' => 'groups', 'group' => 'Column', 'title' => 3, 'sort' => 50, 'add' => false, 'remove' => false,
        'fields' => [1 => ['Logo', 'image'], 3 => ['Tool name'], 4 => ['Tagline']]],
    'pp_services' => $services,
    'pp_flow' => ['label' => 'Process cards', 'section' => 'Process', 'title' => 'title', 'sort' => 70, 'add' => false, 'remove' => false, 'fields' => ['title' => ['Step title'], 'text' => ['Description', 'textarea']]],
    'pp_offers' => $offer,
    'pp_faqs' => $faq,

    // ---------- manufacturing ----------
    'mf_lose' => ['label' => '"Where manufacturers lose ground" cards', 'section' => 'Where Manufacturers Lose Ground', 'title' => 'title', 'sort' => 30, 'add' => false, 'remove' => false,
        'fields' => ['title' => ['Title'], 'text' => ['Description', 'textarea'], 'img' => ['Picture', 'image']]],
    'mf_services' => ['label' => 'Services accordion', 'section' => 'Services', 'title' => 'title', 'sort' => 60, 'fields' => ['title' => ['Service name'], 'text' => ['Description', 'textarea']]],
    'svc_sections' => ['label' => 'Service groups', 'section' => 'Service Groups', 'title' => 'title', 'sort' => 20, 'add' => false, 'remove' => false,
        'fields' => ['tab' => ['Name in the tab bar'], 'tag' => ['Small coloured label'], 'title' => ['Heading'], 'text' => ['Description', 'textarea'], 'btn' => ['Button text'], 'href' => ['Button link', 'url'], 'img' => ['Banner picture', 'image'],
            'items' => ['Services (one per line; add  | page-address  to link one)', 'lines']]],
    'mf_offers' => $offer,
    'mf_faqs' => $faq,
];
