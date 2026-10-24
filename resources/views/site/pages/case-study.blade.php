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
        <svg class="cs-snapshot__art" viewBox="0 0 1800 150" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="csRed" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#d6283b"/><stop offset=".55" stop-color="#f3b9bf"/><stop offset="1" stop-color="#f4f4f6"/></linearGradient>
                <linearGradient id="csSlab" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ececf0"/><stop offset=".45" stop-color="#8d8d96"/><stop offset="1" stop-color="#26262c"/></linearGradient>
                <linearGradient id="csDark" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4a4a52"/><stop offset="1" stop-color="#0c0c10"/></linearGradient>
                <filter id="csGlow" x="-5%" y="-40%" width="110%" height="180%"><feGaussianBlur stdDeviation="3"/></filter>
            </defs>
            <path d="M0,104 C200,70 420,78 640,118 L640,150 L0,150 Z" fill="url(#csRed)"/>
            <path d="M0,104 C200,70 420,78 640,118" fill="none" stroke="#e23a4c" stroke-width="3"/>
            <path d="M0,128 C220,104 430,110 660,140 L660,150 L0,150 Z" fill="#fff" opacity=".55"/>
            <g>
                <path d="M520,150 L700,86 Q720,78 756,78 L930,80 Q958,81 958,110 L958,150 Z" fill="url(#csSlab)"/>
                <path d="M700,86 Q720,78 756,78 L930,80 Q958,81 958,110" fill="none" stroke="#8a56d6" stroke-width="5" filter="url(#csGlow)" opacity=".8"/>
                <path d="M700,86 Q720,78 756,78 L930,80 Q958,81 958,110" fill="none" stroke="#b891f0" stroke-width="2.5"/>
            </g>
            <g>
                <path d="M880,150 L1040,92 Q1060,84 1096,84 L1260,86 Q1290,87 1290,116 L1290,150 Z" fill="url(#csSlab)"/>
                <path d="M1040,92 Q1060,84 1096,84 L1260,86 Q1290,87 1290,116" fill="none" stroke="#3b8be8" stroke-width="5" filter="url(#csGlow)" opacity=".8"/>
                <path d="M1040,92 Q1060,84 1096,84 L1260,86 Q1290,87 1290,116" fill="none" stroke="#7cb4f5" stroke-width="2.5"/>
            </g>
            <g>
                <path d="M1230,150 L1390,98 Q1410,90 1446,90 L1600,92 Q1630,93 1630,120 L1630,150 Z" fill="url(#csSlab)"/>
                <path d="M1390,98 Q1410,90 1446,90 L1600,92 Q1630,93 1630,120" fill="none" stroke="#7bbf3a" stroke-width="5" filter="url(#csGlow)" opacity=".8"/>
                <path d="M1390,98 Q1410,90 1446,90 L1600,92 Q1630,93 1630,120" fill="none" stroke="#a6dc6a" stroke-width="2.5"/>
            </g>
            <path d="M1560,150 L1700,70 Q1730,56 1770,58 L1800,60 L1800,150 Z" fill="url(#csDark)"/>
            <path d="M1700,70 Q1730,56 1770,58 L1800,60" fill="none" stroke="#6a6a74" stroke-width="2.5"/>
        </svg>
    </section>

    <!-- ============ 01 INDUSTRY OVERVIEW ============ -->
    <section class="container cs-overview">
        <div class="cs-overview__top">
            @if($T('overview.5'))
                <figure class="cs-overview__pic"><img src="{{ iu('assets/images/', $T('overview.5')) }}" alt="" loading="lazy"></figure>
            @endif
            <div class="cs-overview__copy">
                <h2 class="h2">{!! $T('overview.2') !!}</h2>
                <p>{!! $T('overview.3') !!}</p>
            </div>
        </div>
        <div class="cs-highlights">
            <h3 class="cs-highlights__title">{!! $T('overview.4') !!}</h3>
            <ul>
                @foreach($cs_highlights as $h)
                    <li style="--c: {{ $accents[$loop->index % count($accents)] }}">
                        <span class="cs-hl__badge" aria-hidden="true"></span>
                        <strong>{!! $h['title'] !!}</strong>
                        <span class="cs-hl__text">{!! $h['text'] !!}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <!-- ============ 02 THE CHALLENGE ============ -->
    <section class="container cs-challenge">
        <header class="cs-head">
            <h2 class="h2">{!! $T('challenge.2') !!}</h2>
            <p>{!! $T('challenge.3') !!}</p>
        </header>
        <div class="cs-cards">
            @foreach($cs_challenges as $c)
                <article class="cs-card" style="--c: {{ $accents[$loop->index % count($accents)] }}">
                    <span class="cs-card__no">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <h3>{!! $c['title'] !!}</h3>
                    <p>{!! $c['text'] !!}</p>
                </article>
            @endforeach
        </div>
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
    <section class="container cs-results">
        <header class="cs-head">
            <h2 class="h2">{!! $T('results.2') !!}</h2>
            <p>{!! $T('results.3') !!}</p>
        </header>
        <div class="cs-outcomes">
            @foreach($cs_results as $r)
                <article class="cs-outcome" style="--c: {{ $accents[$loop->index % count($accents)] }}">
                    <h3>{!! $r['title'] !!}</h3>
                    <p>{!! $r['text'] !!}</p>
                </article>
            @endforeach
        </div>
    </section>

    <!-- ============ 05 THE IMPACT ============ -->
    <section class="container cs-impact">
        <h2 class="cs-impact__label">{!! $T('impact.2') !!}</h2>
        <blockquote>{!! $T('impact.3') !!}</blockquote>
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
