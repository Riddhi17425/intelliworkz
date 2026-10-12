@extends('site.layout')

{{-- Pages created in the admin panel. Same header/footer as the designed pages; the body is the editor's rich text. --}}
@section('content')
<main class="page gp">
    @include('site.partials.site-header')

    <section class="gp-hero">
        <div class="container gp-hero__inner">
            <p class="breadcrumb breadcrumb--light"><a href="{{ url('/') }}">Home</a> / <span>{{ $page->title }}</span></p>
            <h1 class="gp-hero__title">{{ $page->title }}</h1>
        </div>
    </section>

    <article class="gp-body">
        <div class="container gp-body__inner">
            @if($page->featured_image)
                <figure class="gp-figure"><img src="{{ \App\Support\Media::url($page->featured_image) }}" alt="{{ $page->title }}"></figure>
            @endif
            <div class="gp-prose">{!! $page->content !!}</div>
        </div>
    </article>
</main>
@endsection
