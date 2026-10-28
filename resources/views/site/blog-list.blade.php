@extends('site.layout')

{{-- The blog page: every published page marked "This is a blog post" in the admin, newest first, with a category filter. --}}
@php
    $cat = trim((string) request('category'));
    $base = \App\Models\Page::published()->where('template', 'blog-post');
    $categories = (clone $base)->whereNotNull('category')->where('category', '!=', '')
        ->selectRaw('category, count(*) as total')->groupBy('category')->orderBy('category')->pluck('total', 'category');
    $all = $categories->sum();
    $posts = (clone $base)->when($cat !== '', fn ($q) => $q->where('category', $cat))
        ->orderByDesc('published_at')->orderByDesc('id')->paginate(10)->withQueryString();
    $featured = ($posts->onFirstPage() && $cat === '') ? $posts->first() : null;
    $rest = $featured ? $posts->slice(1) : $posts;
    $minutes = fn ($p) => max(1, (int) ceil(str_word_count(strip_tags((string) $p->content)) / 200));
@endphp
@section('content')
<main class="page gp bx">
    @include('site.partials.site-header')

    <!-- ============ MASTHEAD ============ -->
    <section class="bx-mast">
        <div class="bx-wrap">
            <p class="bx-crumb"><a href="{{ url('/') }}">{{ $G('page.1', 'Home') }}</a> / <span>{{ $page->title }}</span></p>
            <span class="bx-eyebrow">Insights from Intelliworkz</span>
            <h1 class="bx-mast__title">{{ $page->title }}</h1>
            @if($page->meta_description)<p class="bx-mast__lead">{{ $page->meta_description }}</p>@endif
            <ul class="bx-mast__facts">
                <li>{{ $all }} {{ \Illuminate\Support\Str::plural('article', $all) }}</li>
                @if($categories->count())<li>{{ $categories->count() }} {{ \Illuminate\Support\Str::plural('topic', $categories->count()) }}</li>@endif
            </ul>
        </div>
    </section>

    @if($categories->count() > 1)
        <div class="bx-filter">
            <div class="bx-wrap">
                <nav class="bx-filter__bar" aria-label="Blog topics">
                    <a class="bx-chip{{ $cat === '' ? ' is-on' : '' }}" href="{{ url('blog') }}">All <em>{{ $all }}</em></a>
                    @foreach($categories as $name => $total)
                        <a class="bx-chip{{ $cat === $name ? ' is-on' : '' }}" href="{{ url('blog') }}?category={{ urlencode($name) }}">{{ $name }} <em>{{ $total }}</em></a>
                    @endforeach
                </nav>
            </div>
        </div>
    @endif

    <!-- ============ POSTS ============ -->
    <section class="bx-list">
        <div class="bx-wrap">
            @if($posts->isEmpty())
                <p class="bx-empty">{{ $cat !== '' ? 'No articles in this topic yet.' : 'New articles are on the way. Please check back soon.' }}@if($cat !== '') <a href="{{ url('blog') }}">See all articles</a>@endif</p>
            @else
                @if($featured)
                    <article class="bx-feature">
                        <a class="bx-feature__media" href="{{ $featured->url() }}" tabindex="-1" aria-hidden="true">
                            @if($featured->featured_image)<img src="{{ \App\Support\Media::url($featured->featured_image) }}" alt="">@endif
                        </a>
                        <div class="bx-feature__body">
                            <span class="bx-feature__label">Latest article</span>
                            <div class="bx-meta">
                                @if($featured->category)<span class="bx-badge bx-badge--soft">{{ $featured->category }}</span><i class="bx-dot"></i>@endif
                                @if($featured->published_at)<time datetime="{{ $featured->published_at->toDateString() }}">{{ $featured->published_at->format('F j, Y') }}</time><i class="bx-dot"></i>@endif{{ $minutes($featured) }} min read
                            </div>
                            <h2 class="bx-feature__title"><a href="{{ $featured->url() }}">{{ $featured->title }}</a></h2>
                            <p class="bx-feature__text">{{ $featured->meta_description ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $featured->content))), 220) }}</p>
                            <a class="bx-btn" href="{{ $featured->url() }}">Read the article <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                        </div>
                    </article>
                @endif

                @if($rest->isNotEmpty())
                    <div class="bx-grid">
                        @foreach($rest as $post)@include('site.partials.blog-card', ['post' => $post])@endforeach
                    </div>
                @endif

                @if($posts->hasPages())
                    <nav class="bx-pager" aria-label="Blog pages">
                        @if($posts->onFirstPage())<span class="is-off">&larr; Newer</span>@else<a href="{{ $posts->previousPageUrl() }}">&larr; Newer</a>@endif
                        @foreach(range(1, $posts->lastPage()) as $n)
                            @if($n === $posts->currentPage())<span class="is-on" aria-current="page">{{ $n }}</span>@else<a href="{{ $posts->url($n) }}">{{ $n }}</a>@endif
                        @endforeach
                        @if($posts->hasMorePages())<a href="{{ $posts->nextPageUrl() }}">Older &rarr;</a>@else<span class="is-off">Older &rarr;</span>@endif
                    </nav>
                @endif
            @endif
        </div>
    </section>

    <section class="bx-band">
        <div class="bx-wrap">
            <div class="bx-band__box">
                <div>
                    <h2>Have a project in mind?</h2>
                    <p>Tell us what you are trying to build, improve or market and our team will get back to you.</p>
                </div>
                <a class="bx-btn" href="{{ url('contact-us') }}">Talk to our team <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
        </div>
    </section>
</main>
@endsection
