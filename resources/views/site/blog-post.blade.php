@extends('site.layout')

{{-- A single blog post: a normal admin page (title, featured image, rich text) drawn as an article in the site's dark/red style. --}}
@php
    // give every heading an anchor and build the "In this article" list from them
    $toc = [];
    $articleHtml = (string) $page->content;
    if (trim($articleHtml) !== '') {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="bl-root">' . $articleHtml . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
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
        $root = $dom->getElementById('bl-root');
        $articleHtml = '';
        if ($root) { foreach ($root->childNodes as $child) { $articleHtml .= $dom->saveHTML($child); } }
    }
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $page->content)) / 200));
    $more = \App\Models\Page::published()->where('template', 'blog-post')->where('id', '!=', $page->id)->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get();
    $shareUrl = urlencode($page->url());
    $shareTitle = urlencode($page->title);
@endphp
@section('content')
<main class="page gp bl bl-post">
    <div class="bl-progress" data-bl-progress aria-hidden="true"></div>
    @include('site.partials.site-header')

    <!-- ============ HERO ============ -->
    <section class="blp-hero">
        <div class="container blp-hero__inner">
            <div class="blp-hero__copy">
                <p class="breadcrumb breadcrumb--light"><a href="{{ url('/') }}">{{ $G('page.1', 'Home') }}</a> / <a href="{{ url('blog') }}">Blog</a> / <span>{{ $page->title }}</span></p>
                @if($page->category)<span class="blp-pill">{{ $page->category }}</span>@endif
                <h1 class="blp-title">{{ $page->title }}</h1>
                @if($page->meta_description)<p class="blp-lead">{{ $page->meta_description }}</p>@endif
                <ul class="blp-meta">
                    @if($page->published_at)
                        <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2.5" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 10h17M8 3.5v3M16 3.5v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><time datetime="{{ $page->published_at->toDateString() }}">{{ $page->published_at->format('F j, Y') }}</time></li>
                    @endif
                    <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7.5V12l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>{{ $minutes }} min read</li>
                    <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8.5" r="3.5" stroke="currentColor" stroke-width="1.6"/><path d="M5 19.5c.8-3.3 3.6-5 7-5s6.2 1.7 7 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>By Intelliworkz Team</li>
                </ul>
            </div>
            @if($page->featured_image)
                <figure class="blp-hero__pic"><img src="{{ \App\Support\Media::url($page->featured_image) }}" alt="{{ $page->title }}"></figure>
            @endif
        </div>
    </section>

    <!-- ============ ARTICLE ============ -->
    <section class="blp-body">
        <div class="container blp-body__inner{{ count($toc) > 1 ? '' : ' blp-body__inner--solo' }}">
            <aside class="blp-side">
                @if(count($toc) > 1)
                    <nav class="blp-toc" aria-label="In this article">
                        <h2 class="blp-toc__title">In this article</h2>
                        <ol>
                            @foreach($toc as $t)<li><a href="#{{ $t['id'] }}" data-bl-toc>{{ $t['text'] }}</a></li>@endforeach
                        </ol>
                    </nav>
                @endif
                <div class="blp-share">
                    <span>Share</span>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M5.4 8.6H2.2V21h3.2V8.6zM3.8 3a1.9 1.9 0 100 3.8 1.9 1.9 0 000-3.8zM21.8 14.4c0-3.4-1.8-5.9-4.7-5.9-1.4 0-2.6.8-3.2 1.8V8.6h-3.1V21h3.2v-6.4c0-1.7.8-2.8 2.2-2.8s2 1 2 2.7V21h3.2l.4-6.6z"/></svg></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Share on Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M13.5 21v-7.5h2.6l.4-3h-3V8.6c0-.9.3-1.5 1.6-1.5h1.6V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.9 1.4-3.9 4v2.2H8V13.5h2.5V21h3z"/></svg></a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" aria-label="Share on X"><svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.8-6.3L5.4 21H2.3l7.3-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.3 4.7H5.5l11.2 14.5z"/></svg></a>
                    <button type="button" data-bl-copy data-url="{{ $page->url() }}" aria-label="Copy link"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M10 14a4 4 0 005.7 0l3-3a4 4 0 00-5.7-5.7l-1 1M14 10a4 4 0 00-5.7 0l-3 3a4 4 0 005.7 5.7l1-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg><em>Copied</em></button>
                </div>
            </aside>

            <article class="blp-article">
                <div class="blp-prose">{!! $articleHtml !!}</div>

                <div class="blp-cta">
                    <div>
                        <h2>Have a business challenge? Let&rsquo;s work on it.</h2>
                        <p>Tell us what you are trying to build, improve or market and our team will get back to you.</p>
                    </div>
                    <a class="btn btn--cta" href="{{ url('contact-us') }}">Talk to our team</a>
                </div>
            </article>
        </div>
    </section>

    @if($more->isNotEmpty())
        <section class="bl-more">
            <div class="container bl-list__inner">
                <div class="bl-more__head">
                    <h2 class="bl-more__title">Keep reading</h2>
                    <a class="bl-back" href="{{ url('blog') }}">All articles <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                </div>
                <div class="bl-grid bl-grid--three">
                    @foreach($more as $post)
                        <article class="bl-card">
                            <a class="bl-card__link" href="{{ $post->url() }}" aria-label="{{ $post->title }}">
                                <span class="bl-card__media">@if($post->featured_image)<img src="{{ \App\Support\Media::url($post->featured_image) }}" alt="" loading="lazy">@endif</span>
                                <span class="bl-card__body">
                                    <span class="bl-card__meta">
                                        @if($post->category)<span class="bl-tag">{{ $post->category }}</span>@endif
                                        @if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>@endif
                                    </span>
                                    <h3 class="bl-card__title">{{ $post->title }}</h3>
                                    <span class="bl-card__more">Read article <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                </span>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>

<script>
(function () {
    var bar = document.querySelector('[data-bl-progress]'), art = document.querySelector('.blp-article');
    function progress() {
        if (!bar || !art) return;
        var r = art.getBoundingClientRect(), total = r.height - window.innerHeight * .5;
        var p = Math.min(1, Math.max(0, (window.innerHeight * .35 - r.top) / Math.max(total, 1)));
        bar.style.transform = 'scaleX(' + p + ')';
    }
    window.addEventListener('scroll', progress, { passive: true }); progress();
    var links = [].slice.call(document.querySelectorAll('[data-bl-toc]'));
    if (links.length && 'IntersectionObserver' in window) {
        var map = {}; links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (e) { if (e.isIntersecting) { links.forEach(function (a) { a.classList.remove('is-on'); }); var a = map[e.target.id]; if (a) a.classList.add('is-on'); } });
        }, { rootMargin: '-20% 0px -70% 0px' });
        Object.keys(map).forEach(function (id) { var h = document.getElementById(id); if (h) io.observe(h); });
    }
    var copy = document.querySelector('[data-bl-copy]');
    if (copy) copy.addEventListener('click', function () {
        var url = copy.getAttribute('data-url');
        (navigator.clipboard ? navigator.clipboard.writeText(url) : Promise.reject()).catch(function () {}).then(function () { copy.classList.add('is-done'); setTimeout(function () { copy.classList.remove('is-done'); }, 1600); });
    });
})();
</script>
@endsection
