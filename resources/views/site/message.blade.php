@extends('site.layout')

{{-- Page-not-found (404) and the thank-you page after an enquiry. Same header and footer as every other page. --}}
@section('content')
<main class="page gp msg">
    @include('site.partials.site-header')

    <section class="msg-hero{{ $kind === 'thanks' ? ' msg-hero--thanks' : '' }}">
        <div class="container msg-hero__inner">
            @if($kind === 'thanks')
                <span class="msg-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                </span>
                <h1 class="msg-title">{!! $G('thanks.1', 'Thank you') !!}{{ $name ? ', ' . $name : '' }}!</h1>
                <p class="msg-text">{!! $G('thanks.2', 'Your message has reached us. Our team will read it and get back to you shortly, usually within one working day.') !!}</p>
            @else
                <span class="msg-code" aria-hidden="true">404</span>
                <h1 class="msg-title">{!! $G('notfound.1', 'We can\'t find that page') !!}</h1>
                <p class="msg-text">{!! $G('notfound.2', 'The address may be mistyped, or the page has moved or is no longer available.') !!}</p>
            @endif

            <div class="msg-actions">
                <?php btn($kind === 'thanks' ? $G('thanks.3', 'Back to home') : $G('notfound.3', 'Back to home'), 'btn--cta', null, url('/')); ?>
                @if($kind === 'thanks')
                    <?php btn($G('thanks.4', 'Explore our services'), 'btn--stroke btn--cta', null, url($G('thanks.5', 'services.php'))); ?>
                @else
                    <?php btn($G('notfound.4', 'Contact us'), 'btn--stroke btn--cta', null, url($G('notfound.5', 'contact-us.php'))); ?>
                @endif
            </div>

            @if($kind === 'thanks')
                <ol class="msg-steps" aria-label="What happens next">
                    <li>
                        <span class="msg-steps__ico"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="M4 7l8 6 8-6"/></svg></span>
                        <strong>We read your message</strong>
                        <span>Your enquiry goes straight to the right team.</span>
                    </li>
                    <li>
                        <span class="msg-steps__ico"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                        <strong>We get back to you</strong>
                        <span>Usually within one working day.</span>
                    </li>
                    <li>
                        <span class="msg-steps__ico"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20l4-1 11-11-3-3L5 16l-1 4z"/><path d="M14 6l3 3"/></svg></span>
                        <strong>We plan the next step</strong>
                        <span>Scope, timeline and how we can help.</span>
                    </li>
                </ol>
            @endif
        </div>
    </section>

    @if($links->isNotEmpty())
        <section class="msg-links">
            <div class="container msg-links__inner">
                <h2>{{ $kind === 'thanks' ? $G('thanks.6', 'While you are here') : $G('notfound.6', 'Maybe you were looking for') }}</h2>
                <ul>
                    @foreach($links as $p)<li><a href="{{ $p->url() }}">{{ $p->title }}</a></li>@endforeach
                </ul>
            </div>
        </section>
    @endif
</main>
@endsection
