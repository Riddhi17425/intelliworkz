{{-- Hero of the blog pages, built like the hero of the other inner pages (black, photo on the right, breadcrumb, title, text, two buttons).
     $heroCrumb = breadcrumb html, $heroTitle, $heroLead (optional), $heroMeta = html shown above the title (optional), $heroButtons (bool), $heroLong (bool: smaller title for long headlines).
     The photo is the featured image of the Blog page in the admin. --}}
@php
    $heroPhoto = \App\Support\Media::url(\App\Models\Page::where('slug', 'blog')->value('featured_image') ?: 'uploads/images/2026/10/blog-hero.jpg');
@endphp
<section class="ve-hero ve-hero--plain ct-hero bx-ihero{{ !empty($heroLong) ? ' bx-ihero--long' : '' }}">
    <img class="ct-hero__photo" src="{{ $heroPhoto }}" alt="">

    <div class="ve-hero__content">
        @include('site.partials.site-header')

        <div class="container mf-hero__copy ct-hero__copy">
            <p class="breadcrumb breadcrumb--light">{!! $heroCrumb !!}</p>
            <div class="ct-hero__text">
                @if(!empty($heroMeta))<div class="bx-ihero__meta">{!! $heroMeta !!}</div>@endif
                <h1 class="h1">{{ $heroTitle }}</h1>
                @if(!empty($heroLead))<p>{{ $heroLead }}</p>@endif
            </div>
            @if(!empty($heroButtons))
                <div class="mf-hero__btns">
                    <a href="{{ url('contact-us.php') }}" class="mf-btn mf-btn--red">Start a Project</a>
                    <a href="{{ url('services.php') }}" class="mf-btn mf-btn--ghost">Explore Our Services</a>
                </div>
            @endif
        </div>
    </div>
</section>
