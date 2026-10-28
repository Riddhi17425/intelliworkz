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
    $more = \App\Models\Page::published()->where('template', 'blog-post')->where('id', '!=', $page->id)
        ->orderByRaw('category = ? desc', [(string) $page->category])->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();
    $topics = array_values(array_filter(array_map('trim', explode(',', (string) $page->meta_keywords))));
    $shareUrl = urlencode($page->url());
    $shareTitle = urlencode($page->title);
    $cover = $page->featured_image ? \App\Support\Media::url($page->featured_image) : null;
    $company = $settings['company_name'] ?? 'Intelliworkz';
    $schema = [
        '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $page->title,
        'description' => $page->meta_description, 'image' => $cover, 'mainEntityOfPage' => $page->url(),
        'datePublished' => optional($page->published_at)->toIso8601String(), 'dateModified' => optional($page->updated_at)->toIso8601String(),
        'author' => ['@type' => 'Organization', 'name' => $company], 'publisher' => ['@type' => 'Organization', 'name' => $company],
    ];
@endphp
@section('content')
<main class="page gp bx bx-post">
    <div class="bx-progress" data-bx-progress aria-hidden="true"></div>
    @include('site.partials.site-header')
    <script type="application/ld+json">{!! json_encode(array_filter($schema), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <!-- ============ HEAD ============ -->
    <section class="bx-phead">
        <div class="bx-wrap">
            <p class="bx-crumb"><a href="{{ url('/') }}">{{ $G('page.1', 'Home') }}</a> / <a href="{{ url('blog') }}">Blog</a> / <span>{{ \Illuminate\Support\Str::limit($page->title, 60) }}</span></p>
            <div class="bx-phead__tags">
                @if($page->category)<a class="bx-badge" href="{{ url('blog') }}?category={{ urlencode($page->category) }}">{{ $page->category }}</a>@endif
                <span>
                    @if($page->published_at)<time datetime="{{ $page->published_at->toDateString() }}">{{ $page->published_at->format('F j, Y') }}</time><i class="bx-dot"></i>@endif{{ $minutes }} min read
                </span>
            </div>
            <h1 class="bx-phead__title">{{ $page->title }}</h1>
            @if($page->meta_description)<p class="bx-phead__lead">{{ $page->meta_description }}</p>@endif
            <div class="bx-by">
                <span class="bx-by__logo" aria-hidden="true">IW</span>
                <div><strong>{{ $company }} Business Solutions</strong><span>Creative, digital &amp; technology services</span></div>
            </div>
        </div>
    </section>

    @if($cover)
        <div class="bx-cover"><div class="bx-wrap"><img src="{{ $cover }}" alt="{{ $page->title }}"></div></div>
    @endif

    <!-- ============ ARTICLE ============ -->
    <section class="bx-pbody">
        <div class="bx-wrap">
            <div class="bx-pbody__grid{{ count($toc) > 1 ? '' : ' bx-pbody__grid--solo' }}">
                <div class="bx-share" aria-label="Share this article">
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor"><path d="M5.4 8.6H2.2V21h3.2V8.6zM3.8 3a1.9 1.9 0 100 3.8 1.9 1.9 0 000-3.8zM21.8 14.4c0-3.4-1.8-5.9-4.7-5.9-1.4 0-2.6.8-3.2 1.8V8.6h-3.1V21h3.2v-6.4c0-1.7.8-2.8 2.2-2.8s2 1 2 2.7V21h3.2l.4-6.6z"/></svg></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Share on Facebook"><svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.6-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8V13.5h2.5V21h3z"/></svg></a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" aria-label="Share on X"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.8-6.3L5.4 21H2.3l7.3-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.3 4.7H5.5l11.2 14.5z"/></svg></a>
                    <button type="button" data-bx-copy data-url="{{ $page->url() }}" aria-label="Copy link"><svg width="19" height="19" viewBox="0 0 24 24" fill="none"><path d="M10 14a4 4 0 005.7 0l3-3a4 4 0 00-5.7-5.7l-1 1M14 10a4 4 0 00-5.7 0l-3 3a4 4 0 005.7 5.7l1-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg><em>Link copied</em></button>
                </div>

                <article class="bx-article">
                    <div class="bx-prose">{!! $articleHtml !!}</div>

                    @if($topics)
                        <div class="bx-topics"><span>Topics</span>@foreach($topics as $t)<i>{{ $t }}</i>@endforeach</div>
                    @endif

                    <div class="bx-author">
                        <span class="bx-by__logo" aria-hidden="true">IW</span>
                        <div>
                            <strong>Written by the {{ $company }} team</strong>
                            <p>We help brands, publishers and businesses with creative design, digital marketing, publishing production and technology solutions, delivered by our own in-house teams.</p>
                        </div>
                    </div>

                    <div class="bx-cta">
                        <div>
                            <h2>Have a business challenge? Let&rsquo;s work on it.</h2>
                            <p>Tell us what you are trying to build, improve or market and our team will get back to you.</p>
                        </div>
                        <a class="bx-btn" href="{{ url('contact-us') }}">Talk to our team <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </div>
                </article>

                @if(count($toc) > 1)
                    <nav class="bx-toc" aria-label="On this page">
                        <h2 class="bx-toc__title">On this page</h2>
                        <ol>
                            @foreach($toc as $t)<li><a href="#{{ $t['id'] }}" data-bx-toc>{{ $t['text'] }}</a></li>@endforeach
                        </ol>
                    </nav>
                @endif
            </div>
        </div>
    </section>

    @if($more->isNotEmpty())
        <section class="bx-more">
            <div class="bx-wrap">
                <div class="bx-more__head">
                    <h2 class="bx-more__title">Keep reading</h2>
                    <a class="bx-more__all" href="{{ url('blog') }}">All articles <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                </div>
                <div class="bx-grid">
                    @foreach($more as $post)@include('site.partials.blog-card', ['post' => $post])@endforeach
                </div>
            </div>
        </section>
    @endif
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
        var map = {}; links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (e) { if (e.isIntersecting) { links.forEach(function (a) { a.classList.remove('is-on'); }); var a = map[e.target.id]; if (a) a.classList.add('is-on'); } });
        }, { rootMargin: '-20% 0px -70% 0px' });
        Object.keys(map).forEach(function (id) { var h = document.getElementById(id); if (h) io.observe(h); });
    }
    var copy = document.querySelector('[data-bx-copy]');
    if (copy) copy.addEventListener('click', function () {
        var url = copy.getAttribute('data-url');
        (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).catch(function () {}).then(function () { copy.classList.add('is-done'); setTimeout(function () { copy.classList.remove('is-done'); }, 1600); });
    });
})();
</script>
@endsection
