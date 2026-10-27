@extends('site.layout')

{{-- The blog page: every published page marked "This is a blog post" in the admin, newest first. --}}
@php
    $posts = \App\Models\Page::published()->where('template', 'blog-post')->orderByDesc('published_at')->orderByDesc('id')->paginate(9)->withQueryString();
@endphp
@section('content')
<main class="page gp bl">
    @include('site.partials.site-header')

    <section class="gp-hero">
        <div class="container gp-hero__inner">
            <p class="breadcrumb breadcrumb--light"><a href="{{ url('/') }}">{{ $G('page.1', 'Home') }}</a> / <span>{{ $page->title }}</span></p>
            <h1 class="gp-hero__title">{{ $page->title }}</h1>
            @if($page->meta_description)<p class="bl-hero__lead">{{ $page->meta_description }}</p>@endif
        </div>
    </section>

    <section class="bl-list">
        <div class="container bl-list__inner">
            @if($posts->isEmpty())
                <p class="bl-empty">New articles are on the way. Please check back soon.</p>
            @else
                <div class="bl-grid">
                    @foreach($posts as $post)
                        @php
                            $summary = $post->meta_description ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->content))), 170);
                        @endphp
                        <article class="bl-card">
                            <a class="bl-card__link" href="{{ $post->url() }}" aria-label="{{ $post->title }}">
                                <span class="bl-card__media">
                                    @if($post->featured_image)<img src="{{ \App\Support\Media::url($post->featured_image) }}" alt="" loading="lazy">@endif
                                </span>
                                <span class="bl-card__body">
                                    <span class="bl-card__meta">
                                        @if($post->category)<span class="bl-tag">{{ $post->category }}</span>@endif
                                        @if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('F j, Y') }}</time>@endif
                                    </span>
                                    <h2 class="bl-card__title">{{ $post->title }}</h2>
                                    <span class="bl-card__text">{{ $summary }}</span>
                                    <span class="bl-card__more">Read article <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                </span>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if($posts->hasPages())
                    <nav class="bl-pager" aria-label="Blog pages">
                        @if($posts->onFirstPage())<span class="is-off">&larr; Newer</span>@else<a href="{{ $posts->previousPageUrl() }}">&larr; Newer</a>@endif
                        <span class="bl-pager__now">Page {{ $posts->currentPage() }} of {{ $posts->lastPage() }}</span>
                        @if($posts->hasMorePages())<a href="{{ $posts->nextPageUrl() }}">Older &rarr;</a>@else<span class="is-off">Older &rarr;</span>@endif
                    </nav>
                @endif
            @endif
        </div>
    </section>
</main>
@endsection
