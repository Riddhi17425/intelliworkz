@extends('site.layout')

{{-- A single blog post: a normal admin page (title, featured image, rich text, category, keywords) drawn as an article. --}}
@php
    // give every heading an anchor and build the "On this page" list from them
    $toc = [];
    $articleHtml = (string) $page->content;
    if (trim($articleHtml) !== '') {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="bx-root">' . $articleHtml . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $used = [];
        foreach ($dom->getElementsByTagName('h2') as $h) {
            $text = trim($h->textContent);
            $id = \Illuminate\Support\Str::slug($text) ?: 'section';
            $base = $id; $n = 2; while (isset($used[$id])) { $id = $base . '-' . $n++; }
            $used[$id] = true;
            $h->setAttribute('id', $id);
            $toc[] = ['id' => $id, 'text' => $text];
        }
        $root = $dom->getElementById('bx-root');
        $articleHtml = '';
        if ($root) { foreach ($root->childNodes as $child) { $articleHtml .= $dom->saveHTML($child); } }
    }
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $page->content)) / 200));
    $topics = array_values(array_filter(array_map('trim', explode(',', (string) $page->meta_keywords))));
    $pageUrl = $page->url();
    $cover = $page->featured_image ? \App\Support\Media::url($page->featured_image) : null;
    $company = $settings['company_name'] ?? 'Intelliworkz';
    $schema = [
        '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $page->title,
        'description' => $page->meta_description, 'image' => $cover, 'mainEntityOfPage' => $page->url(),
        'datePublished' => optional($page->published_at)->toIso8601String(), 'dateModified' => optional($page->updated_at)->toIso8601String(),
        'author' => ['@type' => 'Organization', 'name' => $company], 'publisher' => ['@type' => 'Organization', 'name' => $company],
    ];
    $arrow = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp
@section('content')
<main class="page gp bx bx-post">
    <script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <!-- ============ HERO ============ -->
    @php
        $heroMeta = ($page->category ? '<a class="bx-ihero__cat" href="' . e(url('blog') . '?category=' . urlencode($page->category)) . '">' . e($page->category) . '</a>' : '')
            . ($page->published_at ? '<time datetime="' . $page->published_at->toDateString() . '">' . $page->published_at->format('F j, Y') . '</time>' : '')
            . '<span>' . $minutes . ' min read</span>';
    @endphp
    @include('site.partials.blog-hero', [
        'heroCrumb' => '<a href="' . e(url('/')) . '">' . e($G('page.1', 'Home')) . '</a> / <a href="' . e(url('blog')) . '">Blog</a> / ' . e(\Illuminate\Support\Str::limit($page->title, 70)),
        'heroTitle' => $page->title,
        'heroLead' => $page->meta_description,
        'heroMeta' => $heroMeta,
        'heroLong' => true,
    ])

    <div class="bx-byline"><div class="bx-wrap">
        <div class="bx-who">
            <span class="bx-avatar" aria-hidden="true"><img src="{{ $settings['favicon'] ?? 'assets/images/favicon.png' }}" alt=""></span>
            <span><strong>{{ $company }} Business Solutions</strong><em>Creative, digital &amp; technology services</em></span>
        </div>
    </div></div>

    @if($cover)
        <figure class="bx-cover"><div class="bx-wrap"><img src="{{ $cover }}" alt="{{ $page->title }}"></div></figure>
    @endif

    <!-- ============ ARTICLE ============ -->
    <section class="bx-pbody">
        <div class="bx-wrap bx-pbody__grid{{ count($toc) > 1 ? '' : ' bx-pbody__grid--solo' }}">
            <article class="bx-article">
                @if(count($toc) > 1)
                    <details class="bx-toc-m">
                        <summary>On this page <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                        <ol>@foreach($toc as $t)<li><a href="{{ $pageUrl }}#{{ $t['id'] }}">{{ $t['text'] }}</a></li>@endforeach</ol>
                    </details>
                @endif

                <div class="bx-prose">{!! $articleHtml !!}</div>

                <footer class="bx-art-foot">
                    @if($topics)
                        <div class="bx-topics"><span>Topics</span>@foreach($topics as $t)<i>{{ $t }}</i>@endforeach</div>
                    @endif
                    <div class="bx-art-foot__share"><span>Share this article</span>@include('site.partials.blog-share', ['url' => $page->url(), 'title' => $page->title])</div>
                </footer>

                <div class="bx-author">
                    <span class="bx-avatar bx-avatar--lg" aria-hidden="true"><img src="{{ $settings['favicon'] ?? 'assets/images/favicon.png' }}" alt=""></span>
                    <div>
                        <span class="bx-author__label">Written by</span>
                        <strong>The {{ $company }} team</strong>
                        <p>We help brands, publishers and businesses with creative design, digital marketing, publishing production and technology solutions, delivered by our own in-house teams.</p>
                    </div>
                </div>
            </article>

            @if(count($toc) > 1)
                <aside class="bx-aside">
                    <nav class="bx-toc" aria-label="On this page">
                        <h2 class="bx-toc__title">On this page</h2>
                        <ol>@foreach($toc as $t)<li><a href="{{ $pageUrl }}#{{ $t['id'] }}" data-bx-toc>{{ $t['text'] }}</a></li>@endforeach</ol>
                    </nav>
                    <div class="bx-aside__share"><span>Share</span>@include('site.partials.blog-share', ['url' => $page->url(), 'title' => $page->title])</div>
                </aside>
            @endif
        </div>
    </section>

    <!-- ============ CALL TO ACTION ============ -->
    <section class="bx-band bx-band--post">
        <div class="bx-wrap">
            <div class="bx-band__box">
                <div>
                    <span class="bx-eyebrow">Work with us</span>
                    <h2>Have a business challenge? Let&rsquo;s work on it.</h2>
                    <p>Tell us what you are trying to build, improve or market and our team will get back to you.</p>
                </div>
                <a class="bx-btn bx-btn--light" href="{{ url('contact-us.php') }}">Talk to our team {!! $arrow !!}</a>
            </div>
        </div>
    </section>

</main>

<script>
(function () {
    var bar = document.querySelector('[data-bx-progress]'), art = document.querySelector('.bx-prose');
    function progress() {
        if (!bar || !art) return;
        var r = art.getBoundingClientRect(), total = r.height - window.innerHeight * .5;
        var p = Math.min(1, Math.max(0, (window.innerHeight * .35 - r.top) / Math.max(total, 1)));
        bar.style.transform = 'scaleX(' + p + ')';
    }
    window.addEventListener('scroll', progress, { passive: true }); progress();
    var links = [].slice.call(document.querySelectorAll('[data-bx-toc]'));
    if (links.length && 'IntersectionObserver' in window) {
        var map = {}; links.forEach(function (a) { map[a.hash.slice(1)] = a; });
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (e) { if (e.isIntersecting) { links.forEach(function (a) { a.classList.remove('is-on'); }); var a = map[e.target.id]; if (a) a.classList.add('is-on'); } });
        }, { rootMargin: '-20% 0px -70% 0px' });
        Object.keys(map).forEach(function (id) { var h = document.getElementById(id); if (h) io.observe(h); });
    }
    [].forEach.call(document.querySelectorAll('[data-bx-copy]'), function (copy) {
        copy.addEventListener('click', function () {
            var url = copy.getAttribute('data-url');
            (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).catch(function () {}).then(function () { copy.classList.add('is-done'); setTimeout(function () { copy.classList.remove('is-done'); }, 1600); });
        });
    });
})();
</script>
@endsection
