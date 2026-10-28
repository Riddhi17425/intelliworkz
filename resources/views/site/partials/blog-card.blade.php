{{-- One blog post card. $post = a published blog post (Page). Used by the blog page and by "Keep reading". --}}
@php
    $summary = $post->meta_description ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $post->content))), 170);
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->content)) / 200));
@endphp
<article class="bx-card">
    <span class="bx-card__media">
        @if($post->featured_image)<img src="{{ \App\Support\Media::url($post->featured_image) }}" alt="" loading="lazy">@endif
    </span>
    <div class="bx-card__body">
        <p class="bx-meta">
            @if($post->category)<span class="bx-badge bx-badge--soft">{{ $post->category }}</span><i class="bx-dot"></i>@endif
            @if($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('M j, Y') }}</time><i class="bx-dot"></i>@endif{{ $minutes }} min read
        </p>
        <h2 class="bx-card__title"><a href="{{ $post->url() }}">{{ $post->title }}</a></h2>
        <p class="bx-card__text">{{ $summary }}</p>
        <div class="bx-card__foot">
            <span>Read article</span>
            <span class="bx-card__go"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        </div>
    </div>
</article>
