@extends('site.layout')

{{-- The blog page: every published page marked "This is a blog post" in the admin, newest first, with topic filter and search. --}}
@php
    $cat = trim((string) request('category'));
    $term = \Illuminate\Support\Str::limit(trim((string) request('q')), 80, '');
    $base = \App\Models\Page::published()->where('template', 'blog-post');
    $categories = (clone $base)->whereNotNull('category')->where('category', '!=', '')
        ->selectRaw('category, count(*) as total')->groupBy('category')->orderBy('category')->pluck('total', 'category');
    $all = (clone $base)->count();
    $like = '%' . addcslashes($term, '%_\\') . '%';
    $posts = (clone $base)
        ->when($cat !== '', fn ($q) => $q->where('category', $cat))
        ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', $like)
            ->orWhere('meta_description', 'like', $like)->orWhere('content', 'like', $like)))
        ->orderByDesc('published_at')->orderByDesc('id')->paginate(10)->withQueryString();
    $filtered = $cat !== '' || $term !== '';
    $rest = $posts->getCollection();
    $minutes = fn ($p) => max(1, (int) ceil(str_word_count(strip_tags((string) $p->content)) / 200));
    $arrow = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp
@section('content')
<main class="page gp bx">
    <!-- ============ HERO ============ -->
    @include('site.partials.blog-hero', [
        'heroCrumb' => '<a href="' . e(url('/')) . '">' . e($G('page.1', 'Home')) . '</a> / ' . e($page->title),
        'heroTitle' => $page->title,
        'heroLead' => $page->meta_description,
        'heroButtons' => true,
    ])

    <!-- ============ POSTS ============ -->
    <section class="bx-list">
        <div class="bx-wrap">
            @if($posts->isNotEmpty() || $filtered)
                <div class="bx-bar">
                    <h2 class="bx-bar__title">
                        @if($term !== '')Results for &ldquo;{{ $term }}&rdquo;@elseif($cat !== ''){{ $cat }}@else All articles @endif
                        <span>{{ $posts->total() }}</span>
                    </h2>
                    @if($categories->count() > 1 || $filtered)
                        <nav class="bx-chips" aria-label="Blog topics">
                            <a class="bx-chip{{ $cat === '' ? ' is-on' : '' }}" href="{{ url('blog') }}{{ $term !== '' ? '?q=' . urlencode($term) : '' }}">All <em>{{ $all }}</em></a>
                            @foreach($categories as $name => $total)
                                <a class="bx-chip{{ $cat === $name ? ' is-on' : '' }}" href="{{ url('blog') }}?category={{ urlencode($name) }}{{ $term !== '' ? '&q=' . urlencode($term) : '' }}">{{ $name }} <em>{{ $total }}</em></a>
                            @endforeach
                        </nav>
                    @endif
                </div>
            @endif

            @if($posts->isEmpty())
                <div class="bx-empty">
                    <p>{{ $filtered ? 'No articles match your search yet.' : 'New articles are on the way. Please check back soon.' }}</p>
                    @if($filtered)<a class="bx-btn" href="{{ url('blog') }}">See all articles {!! $arrow !!}</a>@endif
                </div>
            @elseif($rest->isNotEmpty())
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
        </div>
    </section>

    <!-- ============ CALL TO ACTION ============ -->
    <section class="bx-band">
        <div class="bx-wrap">
            <div class="bx-band__box">
                <div>
                    <span class="bx-eyebrow">Work with us</span>
                    <h2>Have a project in mind?</h2>
                    <p>Tell us what you are trying to build, improve or market and our team will get back to you.</p>
                </div>
                <a class="bx-btn bx-btn--light" href="{{ url('contact-us.php') }}">Talk to our team {!! $arrow !!}</a>
            </div>
        </div>
    </section>
</main>
@endsection
