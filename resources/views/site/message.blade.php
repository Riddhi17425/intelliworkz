@extends('site.layout')

{{-- Page-not-found (404) and the thank-you page after an enquiry. Same header and footer as every other page. --}}
@section('content')
<main class="page gp msg">
    @include('site.partials.site-header')

    <section class="msg-hero">
        <div class="container msg-hero__inner">
            @if($kind === 'thanks')
                <span class="msg-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                </span>
                <h1 class="msg-title">Thank you{{ $name ? ', ' . $name : '' }}!</h1>
                <p class="msg-text">Your message has reached us. Our team will read it and get back to you shortly, usually within one working day.</p>
            @else
                <span class="msg-code" aria-hidden="true">404</span>
                <h1 class="msg-title">We can't find that page</h1>
                <p class="msg-text">The address may be mistyped, or the page has moved or is no longer available.</p>
            @endif

            <div class="msg-actions">
                <?php btn('Back to home', 'btn--cta', null, url('/')); ?>
                @if($kind === 'thanks')
                    <?php btn('Explore our services', 'btn--stroke btn--cta', null, url('/') . '#services'); ?>
                @else
                    <?php btn('Contact us', 'btn--stroke btn--cta', null, url('contact-us.php')); ?>
                @endif
            </div>
        </div>
    </section>

    @if($links->isNotEmpty())
        <section class="msg-links">
            <div class="container msg-links__inner">
                <h2>{{ $kind === 'thanks' ? 'While you are here' : 'Maybe you were looking for' }}</h2>
                <ul>
                    @foreach($links as $p)<li><a href="{{ $p->url() }}">{{ $p->title }}</a></li>@endforeach
                </ul>
            </div>
        </section>
    @endif
</main>
@endsection
