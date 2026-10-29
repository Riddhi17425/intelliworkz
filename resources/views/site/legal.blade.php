@extends('site.layout')

{{-- Privacy policy, terms of use and cookies: an admin page (title, rich text) drawn as a legal document with a contents list. --}}
@php
    // every heading gets an anchor; the "On this page" list is built from them
    $toc = [];
    $html = (string) $page->content;
    if (trim($html) !== '') {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?><div id="lg-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
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
        $root = $dom->getElementById('lg-root');
        $html = '';
        if ($root) { foreach ($root->childNodes as $child) { $html .= $dom->saveHTML($child); } }
    }
    $docs = \App\Models\Page::published()->whereIn('slug', \App\Http\Controllers\PageController::LEGAL_PAGES)->get()
        ->sortBy(fn ($p) => array_search($p->slug, \App\Http\Controllers\PageController::LEGAL_PAGES))->values();
    $updated = $page->published_at ?? $page->updated_at;
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $page->content)) / 200));
@endphp
@section('content')
<main class="page gp lg">
    @include('site.partials.site-header')

    <!-- ============ HEADING ============ -->
    <section class="lg-hero">
        <div class="lg-wrap">
            <p class="lg-crumb"><a href="{{ url('/') }}">{{ $G('page.1', 'Home') }}</a><span aria-hidden="true">/</span><span>{{ $page->title }}</span></p>
            <span class="lg-eyebrow">Legal</span>
            <h1 class="lg-title">{{ $page->title }}</h1>
            @if($page->meta_description)<p class="lg-lead">{{ $page->meta_description }}</p>@endif
            <ul class="lg-facts">
                @if($updated)<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 10h17M8 3.5v3M16 3.5v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>Last updated <time datetime="{{ $updated->toDateString() }}">{{ $updated->format('F j, Y') }}</time></li>@endif
                <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5V12l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>{{ $minutes }} min read</li>
                <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20h16M6 20V9l6-5 6 5v11M10 20v-5h4v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>{{ $settings['company_name'] ?? 'Intelliworkz' }} Business Solutions Pvt. Ltd.</li>
            </ul>
        </div>
    </section>

    @if($docs->count() > 1)
        <nav class="lg-tabs" aria-label="Legal pages">
            <div class="lg-wrap">
                @foreach($docs as $d)<a class="lg-tab{{ $d->id === $page->id ? ' is-on' : '' }}" href="{{ $d->url() }}"@if($d->id === $page->id) aria-current="page"@endif>{{ $d->title }}</a>@endforeach
            </div>
        </nav>
    @endif

    <!-- ============ DOCUMENT ============ -->
    <section class="lg-body">
        <div class="lg-wrap lg-grid{{ count($toc) > 1 ? '' : ' lg-grid--solo' }}">
            @if(count($toc) > 1)
                <aside class="lg-aside">
                    <nav class="lg-toc" aria-label="On this page">
                        <h2 class="lg-toc__title">On this page</h2>
                        <ol>@foreach($toc as $t)<li><a href="#{{ $t['id'] }}" data-lg-toc>{{ $t['text'] }}</a></li>@endforeach</ol>
                    </nav>
                </aside>
            @endif

            <article class="lg-doc">
                @if(count($toc) > 1)
                    <details class="lg-toc-m">
                        <summary>On this page <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                        <ol>@foreach($toc as $t)<li><a href="#{{ $t['id'] }}">{{ $t['text'] }}</a></li>@endforeach</ol>
                    </details>
                @endif

                <div class="lg-prose">{!! $html !!}</div>

                <div class="lg-help">
                    <div>
                        <h2>Questions about this page?</h2>
                        <p>Our team is happy to help. Write to <a href="mailto:hello@intelliworkz.tech">hello@intelliworkz.tech</a> or send us a message.</p>
                    </div>
                    <a class="lg-btn" href="{{ url('contact-us.php') }}">Contact us <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                </div>
            </article>
        </div>
    </section>
</main>

<script>
(function () {
    var links = [].slice.call(document.querySelectorAll('[data-lg-toc]'));
    if (!links.length || !('IntersectionObserver' in window)) return;
    var map = {}; links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
    var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) { if (e.isIntersecting) { links.forEach(function (a) { a.classList.remove('is-on'); }); var a = map[e.target.id]; if (a) a.classList.add('is-on'); } });
    }, { rootMargin: '-20% 0px -70% 0px' });
    Object.keys(map).forEach(function (id) { var h = document.getElementById(id); if (h) io.observe(h); });
})();
</script>
@endsection
