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
                    <span class="cs-tag">{!! $T('hero.3') !!}</span>
                    <h1 class="h1">{!! $T('hero.4') !!}</h1>
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
    <section class="container cs-stats" aria-label="At a glance">
        @foreach($cs_stats as $i => $s)
            <div class="cs-stat{{ $loop->last ? ' is-accent' : '' }}">
                <strong data-count="{{ $s['title'] }}">{!! $s['title'] !!}</strong>
                <span>{!! $s['text'] !!}</span>
            </div>
        @endforeach
    </section>

    <!-- ============ 01 INDUSTRY OVERVIEW ============ -->
    <section class="container cs-overview">
        <div class="cs-overview__copy">
            <span class="cs-no">{!! $T('overview.1') !!}</span>
            <h2 class="h2">{!! $T('overview.2') !!}</h2>
            <p>{!! $T('overview.3') !!}</p>
        </div>
        <div class="cs-highlights">
            <h3 class="cs-highlights__title">{!! $T('overview.4') !!}</h3>
            <ul>
                @foreach($cs_highlights as $h)
                    <li style="--c: {{ $accents[$loop->index % count($accents)] }}">
                        <strong>{!! $h['title'] !!}</strong>
                        <span>{!! $h['text'] !!}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <!-- ============ 02 THE CHALLENGE ============ -->
    <section class="container cs-challenge">
        <header class="cs-head">
            <span class="cs-no">{!! $T('challenge.1') !!}</span>
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
    <section class="cs-approach">
        <img class="cs-approach__pattern" src="{{ $T('hero.1') }}" alt="">
        <div class="container">
            <header class="cs-head cs-head--light">
                <span class="cs-no">{!! $T('approach.1') !!}</span>
                <h2 class="h2 h2--white">{!! $T('approach.2') !!}</h2>
                <p>{!! $T('approach.3') !!}</p>
            </header>
            <ol class="cs-steps">
                @foreach($cs_approach as $a)
                    <li class="cs-step">
                        <span class="cs-step__no">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <h3>{!! $a['title'] !!}</h3>
                        <p>{!! $a['text'] !!}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <!-- ============ 04 RESULTS ============ -->
    <section class="container cs-results">
        <header class="cs-head">
            <span class="cs-no">{!! $T('results.1') !!}</span>
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
        <span class="cs-no">{!! $T('impact.1') !!}</span>
        <h2 class="cs-impact__label">{!! $T('impact.2') !!}</h2>
        <blockquote>{!! $T('impact.3') !!}</blockquote>
    </section>

    <!-- ============ 06 SERVICES DELIVERED ============ -->
    <section class="container cs-services">
        <header class="cs-head">
            <span class="cs-no">{!! $T('services.1') !!}</span>
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
