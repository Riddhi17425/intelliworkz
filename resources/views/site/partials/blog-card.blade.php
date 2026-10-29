{{-- One blog post card. $post = a published blog post (Page); $wide = draw it as a horizontal card. Used by the blog page and by "Keep reading". --}}
@php
    $summary = $post->meta_description ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->content))), 170);
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->content)) / 200));
@endphp
<article class="bx-card{{ !empty($wide) ? ' bx-card--wide' : '' }}">
    <a class="bx-card__media" href="{{ $post->url() }}" tabindex="-1" aria-hidden="true">
        @if($post->featured_image)<img src="{{ \App\Support\Media::url($post->featured_image) }}" alt="" loading="lazy">@endif
    </a>
    <div class="bx-card__body">
        <div class="bx-meta">
            @if($post->category)<span class="bx-cat">{{ $post->category }}</span>@endif
            @if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time>@endif
            <span>{{ $minutes }} min read</span>
        </div>
        <h3 class="bx-card__title"><a href="{{ $post->url() }}">{{ $post->title }}</a></h3>
        <p class="bx-card__text">{{ $summary }}</p>
        <span class="bx-more-link" aria-hidden="true">Read article <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
    </div>
</article>
