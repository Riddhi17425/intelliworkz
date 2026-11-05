{{-- Search-engine data for the <head>: social-card tags and schema.org JSON-LD (organisation, website, breadcrumb, article, FAQ). Nothing here is visible on the page. --}}
@php
    $seoCompany = $settings['company_name'] ?? 'Intelliworkz';
    $seoHome = url('/');
    $seoLogo = \App\Support\Media::url($settings['logo_header'] ?? 'assets/icons/logo-new.svg');
    $seoLogoPng = \App\Support\Media::url($settings['favicon'] ?? 'assets/images/favicon.png');
    $seoUrl = $meta['canonical'];
    $seoDesc = $meta['description'] ?? null;
    $seoIsHome = isset($page) && $page->slug === 'index';
    $seoIsBlog = isset($page) && $page->isBlog();
    $seoIndexable = empty($meta['robots']) || !str_contains($meta['robots'], 'noindex');
    $seoSocial = array_values(array_filter([$settings['social_facebook'] ?? null, $settings['social_instagram'] ?? null, $settings['social_linkedin'] ?? null]));

    $seoOrg = ['@type' => 'Organization', '@id' => $seoHome . '#organization', 'name' => $seoCompany . ' Business Solutions', 'alternateName' => $seoCompany, 'url' => $seoHome,
        'logo' => ['@type' => 'ImageObject', 'url' => $seoLogoPng]];
    if ($seoSocial) { $seoOrg['sameAs'] = $seoSocial; }
    if (!empty($settings['contact_email']) || !empty($settings['contact_phone'])) {
        $seoOrg['contactPoint'] = array_filter(['@type' => 'ContactPoint', 'contactType' => 'sales', 'email' => $settings['contact_email'] ?? null, 'telephone' => $settings['contact_phone'] ?? null]);
    }
    $seoGraph = [$seoOrg];

    if ($seoIsHome) {
        $seoGraph[] = ['@type' => 'WebSite', '@id' => $seoHome . '#website', 'url' => $seoHome, 'name' => $seoCompany, 'publisher' => ['@id' => $seoHome . '#organization'], 'inLanguage' => 'en'];
    }

    // breadcrumb: Home > page (blog posts: Home > Blog > post)
    if (isset($page) && !$seoIsHome && $seoIndexable) {
        $crumbs = [['Home', $seoHome]];
        if ($seoIsBlog) { $crumbs[] = ['Blog', url('blog')]; }
        $crumbs[] = [$page->title, $seoUrl];
        $seoGraph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn ($c, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => strip_tags((string) $c[0]), 'item' => $c[1]], $crumbs, array_keys($crumbs))];
    }

    if ($seoIsBlog) {
        $seoGraph[] = array_filter(['@type' => 'BlogPosting', 'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $seoUrl], 'headline' => \Illuminate\Support\Str::limit(strip_tags((string) $page->title), 108, ''),
            'description' => $seoDesc, 'image' => $meta['image'] ?? null, 'datePublished' => optional($page->published_at)->toIso8601String(), 'dateModified' => optional($page->updated_at)->toIso8601String(),
            'articleSection' => $page->category ?: null, 'inLanguage' => 'en',
            'author' => ['@id' => $seoHome . '#organization'], 'publisher' => ['@id' => $seoHome . '#organization']]);
    } elseif ($seoIndexable && isset($page)) {
        $seoGraph[] = array_filter(['@type' => 'WebPage', '@id' => $seoUrl . '#webpage', 'url' => $seoUrl, 'name' => $meta['title'], 'description' => $seoDesc, 'isPartOf' => ['@id' => $seoHome . '#website'], 'inLanguage' => 'en']);
    }

    // FAQ rich result: only for a FAQ section that is really shown on the page
    $seoFaqs = [];
    foreach (($sectionPlan ?? []) as $part) {
        if (!str_ends_with($part['view'], '.faq')) { continue; }
        $src = $part['data'] ?? [];
        $key = collect(array_keys($src))->first(fn ($k) => str_ends_with((string) $k, '_faqs'));
        if (!$key) { $key = collect(array_keys($__data))->first(fn ($k) => str_ends_with((string) $k, '_faqs')); $src = $__data; }
        foreach ((array) ($key ? $src[$key] : []) as $f) {
            $q = trim(strip_tags((string) ($f[0] ?? ''))); $a = trim(strip_tags((string) ($f[1] ?? '')));
            if ($q !== '' && $a !== '') { $seoFaqs[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]]; }
        }
        break;
    }
    if ($seoFaqs && $seoIndexable) { $seoGraph[] = ['@type' => 'FAQPage', 'mainEntity' => $seoFaqs]; }
@endphp
<meta property="og:locale" content="en_US">
@if($seoIsBlog)
    <meta property="article:published_time" content="{{ optional($page->published_at)->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ optional($page->updated_at)->toIso8601String() }}">
    @if($page->category)<meta property="article:section" content="{{ $page->category }}">@endif
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $meta['title'] }}">
@if($seoDesc)<meta name="twitter:description" content="{{ $seoDesc }}">@endif
@if($meta['image'])<meta name="twitter:image" content="{{ $meta['image'] }}">@endif
@if($seoIndexable)
<script type="application/ld+json">{!! json_encode(['@' . 'context' => 'https://schema.org', '@graph' => $seoGraph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endif
