@extends('site.layout')

@section('content')
@php
$accents = ['var(--c-red)', 'var(--c-navy)', 'var(--c-teal)', 'var(--c-green)', 'var(--c-purple)', 'var(--c-gold)'];
@endphp
<main class="page ve cs">

    <!-- ============ HERO ============ -->
    <section class="ve-hero ve-hero--plain cs-hero">
        <img class="cs-hero__pattern" src="{{ $T('hero.1') }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container cs-hero__cols">
                <div class="cs-hero__copy">
                    <p class="breadcrumb breadcrumb--light">{!! $T('hero.2') !!}</p>
                    <h1 class="h1">{!! $T('hero.4') !!}</h1>
                    <span class="cs-tag">{!! $T('hero.3') !!}</span>
                    <p class="cs-hero__lead">{!! $T('hero.5') !!}</p>
                    <div class="cs-hero__btns">
                        <a href="{{ $T('hero.7') ?: '#' }}" class="cs-btn cs-btn--red">{!! $T('hero.6') !!}</a>
                        <a href="{{ $T('hero.9') ?: '#' }}" class="cs-btn cs-btn--ghost">{!! $T('hero.8') !!}</a>
                    </div>
                </div>

                <aside class="cs-client">
                    <h2 class="cs-client__title">{!! $T('hero.10') !!}</h2>
                    <dl>
                        @foreach($cs_profile as $row)
                            <div class="cs-client__row">
                                <dt>{!! $row['title'] !!}</dt>
                                <dd>{!! $row['text'] !!}</dd>
                            </div>
                        @endforeach
                    </dl>
                </aside>
            </div>
        </div>
    </section>

    <!-- ============ HEADLINE NUMBERS ============ -->
    <section class="cs-snapshot" aria-label="At a glance">
        <div class="container">
            @if($T('numbers.1'))<p class="cs-snapshot__label">{!! $T('numbers.1') !!}</p>@endif
            <div class="cs-snapshot__row">
                @foreach($cs_stats as $i => $s)
                    <div class="cs-snap" style="--c: {{ $accents[$i % count($accents)] }}">
                        <strong data-count="{{ $s['title'] }}">{!! $s['title'] !!}</strong>
                        <span><i aria-hidden="true"></i>{!! $s['text'] !!}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @if($T('numbers.2'))
            <div class="container"><figure class="cs-snapshot__photo"><img src="{{ iu('assets/images/', $T('numbers.2')) }}" alt="" loading="lazy"></figure></div>
        @endif
        @unless($T('numbers.2'))<img class="cs-snapshot__art" src="assets/images/case-study/snapshot-art.png" alt="" aria-hidden="true">@endunless
    </section>

    <!-- ============ 01 INDUSTRY OVERVIEW ============ -->
    @php
        // heading: the last word is drawn lighter (only for plain-text headings)
        $ovTitle = trim($T('overview.2'));
        if ($ovTitle !== '' && strip_tags($ovTitle) === $ovTitle && str_contains($ovTitle, ' ')) {
            $words = explode(' ', $ovTitle);
            $lastWord = array_pop($words);
            $ovTitle = implode(' ', $words) . ' <span>' . $lastWord . '</span>';
        }
        $hlTones = ['#E0263A', '#3B2FBF', '#1E88E5', '#5BAF2E'];
        $hlIcons = [
            '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/><path d="m3 17.5 9 4.5 9-4.5"/>',
            '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
            '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
            '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/><path d="M8.5 11h7M8.5 14h4"/>',
        ];
    @endphp
    <section class="container cs-overview">
        <div class="cs-overview__top">
            <div class="cs-overview__copy">
                <h2 class="h2 cs-overview__title">{!! $ovTitle !!}</h2>
                <p>{!! $T('overview.3') !!}</p>
            </div>
            @if($T('overview.5'))
                <figure class="cs-overview__pic"><img src="{{ iu('assets/images/', $T('overview.5')) }}" alt="" loading="lazy"></figure>
            @endif
        </div>
        <div class="cs-highlights">
            <span class="cs-eyebrow">{!! $T('overview.4') !!}</span>
            <ul>
                @foreach($cs_highlights as $h)
                    <li style="--c: {{ $hlTones[$loop->index % count($hlTones)] }}">
                        <span class="cs-hl__tile" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $hlIcons[$loop->index % count($hlIcons)] !!}</svg></span>
                        <div class="cs-hl__body">
                            <span class="cs-hl__line" aria-hidden="true"></span>
                            <strong>{!! $h['title'] !!}</strong>
                            <span class="cs-hl__text">{!! $h['text'] !!}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <!-- ============ 02 THE CHALLENGE ============ -->
    @php
        $chIcons = [
            '<path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M18 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"/>',
            '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
            '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/><path d="m3 17.5 9 4.5 9-4.5"/>',
            '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/><path d="M8.5 11h7M8.5 14h4"/>',
            '<path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1Z"/><path d="M15 9a4 4 0 0 1 0 6"/><path d="M18 6.5a8 8 0 0 1 0 11"/>',
        ];
    @endphp
    @php
        $chTitle = trim($T('challenge.2'));
        if ($chTitle !== '' && strip_tags($chTitle) === $chTitle && str_contains($chTitle, ' ')) {
            $w = explode(' ', $chTitle); $last = array_pop($w);
            $chTitle = implode(' ', $w) . ' <span>' . $last . '</span>';
        }
        $chIcons = [
            '<path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M18 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5"/>',
            '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
            '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/><path d="m3 17.5 9 4.5 9-4.5"/>',
            '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12Z"/><path d="M8.5 11h7M8.5 14h4"/>',
            '<path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1Z"/><path d="M15 9a4 4 0 0 1 0 6"/><path d="M18 6.5a8 8 0 0 1 0 11"/>',
        ];
    @endphp
    <section class="container cs-split">
        <header class="cs-split__intro">
            <h2 class="h2">{!! $chTitle !!}</h2>
            <p>{!! $T('challenge.3') !!}</p>
        </header>
        <ol class="cs-split__grid">
            @foreach($cs_challenges as $c)
                <li class="cs-split__item" style="--c: {{ $accents[$loop->index % count($accents)] }}">
                    <span class="cs-split__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $chIcons[$loop->index % count($chIcons)] !!}</svg></span>
                    <h3>{!! $c['title'] !!}</h3>
                    <p>{!! $c['text'] !!}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <!-- ============ 03 THE APPROACH (dark) ============ -->
    <section class="why cs-approach">
        <div class="why__inner">
            <header class="cs-head cs-head--light">
                <h2 class="h2 h2--center h2--white">{!! $T('approach.2') !!}</h2>
                <p>{!! $T('approach.3') !!}</p>
            </header>
            <div class="why__grid">
                @foreach($cs_approach as $a)
                    <article class="reason">
                        <div class="reason__top"><span class="reason__no">{{ sprintf('%02d', $loop->iteration) }}</span><span class="reason__line"></span></div>
                        <div class="reason__body">
                            <h3 class="h-card h-card--white">{!! $a['title'] !!}</h3>
                            <p>{!! $a['text'] !!}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============ 04 RESULTS ============ -->
    @php
        $tones = ['#C42030', '#282762', '#2A9D8F', '#D99A00'];
        // simple line icons, one per card (repeat after four)
        $icons = [
            '<path d="M12 3 3 8l9 5 9-5-9-5Z"/><path d="m3 13 9 5 9-5"/><path d="m3 17.5 9 4.5 9-4.5"/>',
            '<circle cx="12" cy="12" r="3"/><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>',
            '<path d="M21 12a9 9 0 0 1-15.4 6.4L3 16"/><path d="M3 12a9 9 0 0 1 15.4-6.4L21 8"/><path d="M21 3v5h-5"/><path d="M3 21v-5h5"/>',
            '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
        ];
    @endphp
    <section class="container cs-results">
        <header class="cs-head cs-results__head">
            <h2 class="h2">{!! $T('results.2') !!}</h2>
            <p>{!! $T('results.3') !!}</p>
        </header>
        <div class="cs-outcomes">
            @foreach($cs_results as $r)
                <article class="cs-outcome" style="--c: {{ $tones[$loop->index % count($tones)] }}">
                    <span class="cs-outcome__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$loop->index % count($icons)] !!}</svg></span>
                    <h3>{!! $r['title'] !!}</h3>
                    <p>{!! $r['text'] !!}</p>
                </article>
            @endforeach
        </div>
    </section>

    <!-- ============ 05 THE IMPACT ============ -->
    <section class="container cs-impact">
        <div class="cs-impact__side">
            <svg class="cs-impact__mark" viewBox="0 0 48 40" fill="currentColor" aria-hidden="true"><path d="M0 40V24C0 10.7 6.3 2.7 18.9 0l2.6 5.2C14.3 7.6 10.7 12.1 10.4 18.9H20V40H0Zm28 0V24C28 10.7 34.3 2.7 46.9 0l2.6 5.2c-7.2 2.4-10.8 6.9-11.1 13.7H48V40H28Z"/></svg>
            <h2 class="cs-impact__label">{!! $T('impact.2') !!}</h2>
        </div>
        <blockquote class="cs-impact__quote">{!! $T('impact.3') !!}</blockquote>
    </section>

    <!-- ============ 06 SERVICES DELIVERED ============ -->
    <section class="container cs-services">
        <header class="cs-head">
            <h2 class="h2">{!! $T('services.2') !!}</h2>
        </header>
        <ul class="cs-svc-grid">
            @foreach($cs_services as $s)
                <li class="cs-svc" style="--c: {{ $accents[$loop->index % count($accents)] }}">
                    <span class="cs-svc__no">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <div>
                        <strong>{!! $s['title'] !!}</strong>
                        <span>{!! $s['text'] !!}</span>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="cs-caps">
            <h3>{!! $T('services.3') !!}</h3>
            <ul>
                @foreach($cs_capabilities as $cap)<li>{!! $cap !!}</li>@endforeach
            </ul>
        </div>
    </section>

    <!-- ============ CALL TO ACTION ============ -->
    <section class="cs-cta">
        <img class="cs-cta__pattern" src="{{ $T('hero.1') }}" alt="">
        <div class="cs-cta__inner">
            <div class="cs-cta__copy">
                <h2 class="cta-title">{!! $T('cta.1') !!}</h2>
                <p>{!! $T('cta.2') !!}</p>
            </div>
            <a href="{{ $T('cta.4') ?: 'contact-us.php' }}" class="btn btn--cta">{!! $T('cta.3') !!}</a>
        </div>
    </section>

</main>
@endsection
