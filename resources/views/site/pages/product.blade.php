@extends('site.layout')

{{--
    Product page (DataNote, Formezy, Docezy …): every page whose template is "product" is drawn here.
    All copy, pictures and lists come from the admin (scope = the page slug). A section is left out when its
    "Show this section" switch is off or when it has nothing to show, so one design serves products with very
    different amounts of content. --pd-a / --pd-b are the product's own colours (admin: Design Colours).
--}}

@section('content')
@php
    $on = fn (string $section) => $T($section . '.show', '1') !== '0';
    $hex = fn ($v, string $fallback) => preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', trim((string) $v)) ? trim((string) $v) : $fallback;
    $has = fn ($v) => trim(strip_tags((string) $v)) !== '';
    $pic = fn ($v) => iu('assets/images/products/', (string) $v);
    $ext = fn ($url) => (bool) preg_match('~^https?://~i', trim((string) $url));
    $isNum = fn ($v) => (bool) preg_match('/^[0-9][0-9,.]*\+?$/', trim(strip_tags((string) $v)));
    $lines = fn ($v) => array_values(array_filter(array_map('trim', is_array($v) ? $v : preg_split('/\R/', (string) $v)), fn ($l) => $l !== ''));
    $rows = fn ($list, string $key = 'title') => array_values(array_filter((array) $list, fn ($r) => is_array($r) && trim(strip_tags((string) ($r[$key] ?? ''))) !== ''));

    // columns for a grid of $n cards: all in one row when they fit, otherwise the widest even split
    $cols = function (int $n, int $max): int {
        if ($n <= $max) {
            return max($n, 1);
        }
        foreach ([$max, $max - 1, $max - 2] as $c) {
            if ($c > 1 && $n % $c === 0) {
                return $c;
            }
        }
        return $max;
    };

    // eyebrow + heading + intro of a section
    $head = function ($label, $title, $lead = '', bool $light = false) use ($has): string {
        if (!$has($label) && !$has($title) && !$has($lead)) {
            return '';
        }
        $html = '<div class="pd-head' . ($light ? ' pd-head--light' : '') . '">';
        $html .= $has($label) ? '<p class="pd-eyebrow">' . $label . '</p>' : '';
        $html .= $has($title) ? '<h2 class="h2 pd-head__title">' . $title . '</h2>' : '';
        $html .= $has($lead) ? '<p class="pd-head__lead">' . $lead . '</p>' : '';
        return $html . '</div>';
    };

    $check = '<svg class="pd-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    $arrow = '<svg class="pd-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
    $outIcon = '<svg class="pd-ico" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9"/></svg>';

    $name = strip_tags($T('hero.1')) ?: $page->title;
    $accent = $hex($T('theme.1'), '#C42030');
    $accent2 = $hex($T('theme.2'), '#F5F5F7');

    $points = array_values(array_filter(array_map(fn ($p) => is_string($p) ? trim($p) : '', (array) ($pd_points ?? [])), fn ($p) => $p !== ''));
    $stats = $rows($pd_stats ?? [], 'value');
    $chips = $rows($pd_chips ?? []);
    $cards = $rows($pd_cards ?? []);
    $modules = $rows($pd_modules ?? []);
    $highlights = $rows($pd_highlights ?? []);
    $features = $rows($pd_features ?? []);
    $industries = $rows($pd_industries ?? []);
    $benefits = array_values(array_filter(array_map(fn ($p) => is_string($p) ? trim($p) : '', (array) ($pd_benefits ?? [])), fn ($p) => $p !== ''));
    $quotes = $rows($pd_testimonials ?? [], 'quote');
    $clients = array_values(array_filter((array) ($pd_clients ?? []), fn ($c) => is_array($c) && !empty($c['img'])));

    // the other published product pages, with their own name, tagline, picture and colours
    $content = app(\App\Support\Content::class);
    $others = \App\Models\Page::published()->where('template', 'product')->whereKeyNot($page->id)->orderBy('sort')->orderBy('id')->get()
        ->map(function ($o) use ($content, $hex) {
            $f = $content->fields($o->slug);
            return [
                'url' => $o->url(), 'name' => strip_tags($f['hero.1'] ?? '') ?: $o->title, 'tag' => $f['hero.4'] ?? '',
                'img' => $f['hero.7'] ?? '', 'logo' => $f['hero.6'] ?? '',
                'a' => $hex($f['theme.1'] ?? '', '#C42030'), 'b' => $hex($f['theme.2'] ?? '', '#F5F5F7'),
            ];
        });
@endphp
<main class="page pd" style="--pd-a: {{ $accent }}; --pd-b: {{ $accent2 }}">

    <!-- ============ HERO ============ -->
    <section class="pd-hero">
        <div class="pd-hero__bg" aria-hidden="true"></div>
        <div class="pd-hero__content">
            @include('site.partials.site-header')

            <div class="container pd-hero__inner">
                <div class="pd-hero__copy">
                    <p class="pd-crumb"><a href="{{ url('/') }}">{!! $G('page.1', 'Home') !!}</a>@if($has($T('hero.12')))<span aria-hidden="true">/</span>{!! $T('hero.12') !!}@endif<span aria-hidden="true">/</span><b>{{ $name }}</b></p>
                    @if($T('hero.6'))<span class="pd-hero__logo"><img src="{{ $pic($T('hero.6')) }}" width="240" height="60" alt="{{ $name }}"></span>@endif
                    @if($has($T('hero.2')))<p class="pd-eyebrow pd-eyebrow--light">{!! $T('hero.2') !!}</p>@endif
                    <h1 class="h1 pd-hero__title">{!! $has($T('hero.3')) ? $T('hero.3') : e($name) !!}</h1>
                    @if($has($T('hero.4')))<p class="pd-hero__tag">{!! $T('hero.4') !!}</p>@endif
                    @if($has($T('hero.5')))<p class="pd-hero__text">{!! $T('hero.5') !!}</p>@endif
                    @if($points)
                        <ul class="pd-hero__points">
                            @foreach($points as $p)<li>{!! $check !!}<span>{!! $p !!}</span></li>@endforeach
                        </ul>
                    @endif
                    <div class="pd-hero__btns">
                        @if($has($T('hero.8')))<a href="{{ $T('hero.9') ?: 'contact-us.php' }}" class="btn btn--cta">{!! $T('hero.8') !!}<img src="assets/icons/arrow-right.svg" width="24" height="24" alt=""></a>@endif
                        @if($has($T('hero.10')) && $T('hero.11'))<a href="{{ $T('hero.11') }}" class="btn btn--cta btn--ghost pd-btn-out"@if($ext($T('hero.11'))) target="_blank" rel="noopener"@endif>{!! $T('hero.10') !!}{!! $outIcon !!}</a>@endif
                    </div>
                </div>
                @if($T('hero.7'))
                    <div class="pd-hero__media">
                        <img src="{{ $pic($T('hero.7')) }}" width="800" height="610" alt="{{ $name }} on laptop, tablet and mobile" fetchpriority="high">
                    </div>
                @endif
            </div>

            @if($on('stats') && $stats)
                <div class="container">
                    <div class="pd-stats" style="--n: {{ count($stats) }}">
                        @foreach($stats as $s)
                            <div class="pd-stat">
                                <strong class="pd-stat__value">@if($isNum($s['value']))<span data-count="{{ strip_tags($s['value']) }}">{{ strip_tags($s['value']) }}</span>@else{!! $s['value'] !!}@endif</strong>
                                <span class="pd-stat__label">{!! $s['label'] ?? '' !!}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- ============ OVERVIEW ============ -->
    @if($on('intro') && ($has($T('intro.2')) || $has($T('intro.3'))))
        @php $compare = $T('intro.4') && $T('intro.6'); @endphp
        @if($compare)
            <section class="container pd-intro pd-intro--compare">
                <div class="pd-intro__head">
                    <div class="pd-intro__title">
                        @if($has($T('intro.1')))<p class="pd-eyebrow">{!! $T('intro.1') !!}</p>@endif
                        <h2 class="h2">{!! $T('intro.2') !!}</h2>
                    </div>
                    <div class="pd-prose">{!! $T('intro.3') !!}</div>
                </div>
                <div class="pd-compare">
                    <figure class="pd-compare__item">
                        <div class="pd-compare__img"><img src="{{ $pic($T('intro.4')) }}" alt="{{ strip_tags($T('intro.5')) }}" loading="lazy"></div>
                        @if($has($T('intro.5')))<figcaption>{!! $T('intro.5') !!}</figcaption>@endif
                    </figure>
                    <span class="pd-compare__arrow" aria-hidden="true">{!! $arrow !!}</span>
                    <figure class="pd-compare__item pd-compare__item--after">
                        <div class="pd-compare__img"><img src="{{ $pic($T('intro.6')) }}" alt="{{ strip_tags($T('intro.7')) }}" loading="lazy"></div>
                        @if($has($T('intro.7')))<figcaption>{!! $T('intro.7') !!}</figcaption>@endif
                    </figure>
                </div>
                @if($chips)
                    <ul class="pd-chips pd-chips--row">
                        @foreach($chips as $c)<li class="pd-chip"><strong>{!! $c['title'] !!}</strong>@if($has($c['text'] ?? ''))<span>{!! $c['text'] !!}</span>@endif</li>@endforeach
                    </ul>
                @endif
            </section>
        @else
            <section class="container pd-intro {{ $T('intro.4') ? '' : 'pd-intro--plain' }}">
                @if($T('intro.4'))
                    <div class="pd-intro__media"><img src="{{ $pic($T('intro.4')) }}" alt="{{ $name }}" loading="lazy"></div>
                @endif
                <div class="pd-intro__copy">
                    @if($has($T('intro.1')))<p class="pd-eyebrow">{!! $T('intro.1') !!}</p>@endif
                    @if($has($T('intro.2')))<h2 class="h2">{!! $T('intro.2') !!}</h2>@endif
                    @if($has($T('intro.3')))<div class="pd-prose">{!! $T('intro.3') !!}</div>@endif
                    @if($chips)
                        <ul class="pd-chips">
                            @foreach($chips as $c)<li class="pd-chip"><strong>{!! $c['title'] !!}</strong>@if($has($c['text'] ?? ''))<span>{!! $c['text'] !!}</span>@endif</li>@endforeach
                        </ul>
                    @endif
                </div>
            </section>
        @endif
    @endif

    <!-- ============ CHALLENGES / FRAMEWORK CARDS ============ -->
    @if($on('challenge') && $cards)
        @php $withImg = (bool) $T('challenge.4'); @endphp
        <section class="pd-band pd-challenge">
            <div class="container">
                {!! $head($T('challenge.1'), $T('challenge.2'), $T('challenge.3')) !!}
                <div class="pd-challenge__body {{ $withImg ? 'has-img' : '' }}">
                    @if($withImg)
                        <div class="pd-challenge__img"><img src="{{ $pic($T('challenge.4')) }}" alt="" loading="lazy"></div>
                    @endif
                    <div class="pd-cards" style="--cols: {{ $withImg ? min(count($cards), 2) : $cols(count($cards), 4) }}">
                        @foreach($cards as $i => $c)
                            <article class="pd-card">
                                <span class="pd-card__no">{{ sprintf('%02d', $i + 1) }}</span>
                                <h3 class="pd-card__title">{!! $c['title'] !!}</h3>
                                @if($has($c['text'] ?? ''))<p class="pd-card__text">{!! $c['text'] !!}</p>@endif
                            </article>
                        @endforeach
                    </div>
                </div>
                @if($has($T('challenge.5')))<p class="pd-challenge__note">{!! $T('challenge.5') !!}</p>@endif
            </div>
        </section>
    @endif

    <!-- ============ MODULES ============ -->
    @if($on('modules') && $modules)
        @php $round = ($modules[0]['look'] ?? '') === 'round'; @endphp
        <section class="container pd-modules">
            {!! $head($T('modules.1'), $T('modules.2'), $T('modules.3')) !!}
            <div class="pd-modules__grid {{ $round ? 'pd-modules__grid--round' : '' }}" style="--cols: {{ $cols(count($modules), 5) }}">
                @foreach($modules as $m)
                    <article class="pd-module {{ $round ? 'pd-module--round' : 'pd-module--wide' }}">
                        @if(!empty($m['img']))<div class="pd-module__media"><img src="{{ $pic($m['img']) }}" alt="{{ strip_tags($m['title']) }}" loading="lazy"></div>@endif
                        <div class="pd-module__body">
                            <h3 class="pd-module__title">{!! $m['title'] !!}</h3>
                            @if($has($m['text'] ?? ''))<p>{!! $m['text'] !!}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ============ FEATURE ROWS ============ -->
    @if($on('highlights') && $highlights)
        <section class="container pd-hl">
            {!! $head($T('highlights.1'), $T('highlights.2'), $T('highlights.3')) !!}
            <div class="pd-hl__rows">
                @foreach($highlights as $h)
                    <article class="pd-hl__row {{ empty($h['img']) ? 'pd-hl__row--text' : '' }}">
                        @if(!empty($h['img']))<div class="pd-hl__media"><img src="{{ $pic($h['img']) }}" alt="{{ strip_tags($h['title']) }}" loading="lazy"></div>@endif
                        <div class="pd-hl__copy">
                            @if($has($h['tag'] ?? ''))<p class="pd-eyebrow">{!! $h['tag'] !!}</p>@endif
                            <h3 class="pd-hl__title">{!! $h['title'] !!}</h3>
                            @if($has($h['text'] ?? ''))<p class="pd-hl__text">{!! $h['text'] !!}</p>@endif
                            @if($lines($h['points'] ?? []))
                                <ul class="pd-checks">
                                    @foreach($lines($h['points'] ?? []) as $pt)<li>{!! $check !!}<span>{!! $pt !!}</span></li>@endforeach
                                </ul>
                            @endif
                            @if($has($h['btn'] ?? '') && !empty($h['href']))<a href="{{ $h['href'] }}" class="btn btn--cta pd-hl__btn">{!! $h['btn'] !!}<img src="assets/icons/arrow-right.svg" width="24" height="24" alt=""></a>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ============ FEATURE TILES ============ -->
    @if($on('features') && $features)
        @php
            $compact = collect($features)->every(fn ($f) => !$has($f['text'] ?? ''));
            $scroll = !$compact && count($features) > 6;   // many described tiles: one auto-scrolling row instead of a tall grid
        @endphp
        <section class="pd-band pd-band--tint pd-feat {{ $compact ? 'pd-feat--compact' : '' }} {{ $scroll ? 'pd-feat--scroll' : '' }}">
            <div class="container">
                {!! $head($T('features.1'), $T('features.2'), $T('features.3')) !!}
                @if($scroll)<div class="pd-feat__viewport">@endif
                <div class="{{ $scroll ? 'pd-feat__track' : 'pd-feat__grid' }}" style="--cols: {{ $compact ? $cols(count($features), 6) : $cols(count($features), 5) }}">
                    @foreach(($scroll ? [false, true] : [false]) as $copy)
                    @foreach($features as $i => $f)
                        <article class="pd-tile"@if($copy) aria-hidden="true"@endif>
                            @if(!empty($f['img']))
                                <span class="pd-tile__icon"><img src="{{ $pic($f['img']) }}" width="64" height="64" alt="" loading="lazy"></span>
                            @else
                                <span class="pd-tile__no">{{ sprintf('%02d', $i + 1) }}</span>
                            @endif
                            <div class="pd-tile__body">
                                @if($has($f['tag'] ?? ''))<span class="pd-tile__tag">{!! $f['tag'] !!}</span>@endif
                                <h3 class="pd-tile__title">{!! $f['title'] !!}</h3>
                                @if($has($f['text'] ?? ''))<p class="pd-tile__text">{!! $f['text'] !!}</p>@endif
                            </div>
                        </article>
                    @endforeach
                    @endforeach
                </div>
                @if($scroll)</div>@endif
            </div>
        </section>
    @endif

    <!-- ============ INDUSTRIES ============ -->
    @if($on('industries') && $industries)
        @php $photo = ($industries[0]['look'] ?? '') === 'photo'; @endphp
        <section class="container pd-ind {{ $photo ? 'pd-ind--photo' : 'pd-ind--icon' }}">
            {!! $head($T('industries.1'), $T('industries.2'), $T('industries.3')) !!}
            <div class="pd-ind__grid" style="--cols: {{ $photo ? $cols(count($industries), 6) : $cols(count($industries), 4) }}">
                @foreach($industries as $it)
                    <article class="pd-ind__card">
                        @if(!empty($it['img']))<img class="pd-ind__img" src="{{ $pic($it['img']) }}" alt="{{ $photo ? strip_tags($it['title']) : '' }}" loading="lazy">@endif
                        <div class="pd-ind__body">
                            <h3 class="pd-ind__title">{!! $it['title'] !!}</h3>
                            @if($has($it['text'] ?? ''))<p class="pd-ind__text">{!! $it['text'] !!}</p>@endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ============ BENEFITS (dark) ============ -->
    @if($on('benefits') && ($benefits || $has($T('benefits.2'))))
        @php $benImg = (bool) $T('benefits.4'); @endphp
        <section class="pd-ben {{ $benImg ? 'has-img' : '' }}">
            <div class="pd-ben__bg" aria-hidden="true"></div>
            <div class="container pd-ben__inner">
                <div class="pd-ben__copy">
                    @if($has($T('benefits.1')))<p class="pd-eyebrow pd-eyebrow--light">{!! $T('benefits.1') !!}</p>@endif
                    @if($has($T('benefits.2')))<h2 class="h2 h2--white">{!! $T('benefits.2') !!}</h2>@endif
                    @if($has($T('benefits.3')))<p class="pd-ben__text">{!! $T('benefits.3') !!}</p>@endif
                    @if($benefits)
                        <ul class="pd-checks pd-checks--light">
                            @foreach($benefits as $b)<li>{!! $check !!}<span>{!! $b !!}</span></li>@endforeach
                        </ul>
                    @endif
                </div>
                @if($benImg)
                    <div class="pd-ben__media"><img src="{{ $pic($T('benefits.4')) }}" alt="" loading="lazy"></div>
                @endif
            </div>
        </section>
    @endif

    <!-- ============ TESTIMONIALS ============ -->
    @if($on('testimonials') && $quotes)
        <section class="container pd-quotes {{ count($quotes) === 1 ? 'pd-quotes--single' : '' }}">
            {!! $head($T('testimonials.1'), $T('testimonials.2'), $T('testimonials.3')) !!}
            <div class="pd-quotes__grid" style="--cols: {{ count($quotes) === 1 ? 1 : $cols(count($quotes), 3) }}">
                @foreach($quotes as $q)
                    @php
                        $who = trim(preg_replace('/^(mr|mrs|ms|dr)\.?\s+/i', '', strip_tags($q['name'] ?? '')));
                        $initials = collect(preg_split('/\s+/', $who))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                    @endphp
                    <figure class="pd-quote">
                        <svg class="pd-quote__mark" viewBox="0 0 48 36" aria-hidden="true"><path d="M0 36V21C0 9 7 2 19 0l2 5c-7 2-10 6-10 12h8v19H0Zm27 0V21C27 9 34 2 46 0l2 5c-7 2-10 6-10 12h8v19H27Z"/></svg>
                        <blockquote><p>{!! $q['quote'] !!}</p></blockquote>
                        <figcaption>
                            @if(!empty($q['img']))<img class="pd-quote__logo" src="{{ $pic($q['img']) }}" alt="" loading="lazy">@elseif($initials !== '')<span class="pd-quote__avatar" aria-hidden="true">{{ $initials }}</span>@endif
                            <span class="pd-quote__who">@if($has($q['name'] ?? ''))<strong>{!! $q['name'] !!}</strong>@endif @if($has($q['role'] ?? ''))<small>{!! $q['role'] !!}</small>@endif</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ============ CLIENT LOGOS ============ -->
    @if($on('clients') && $clients)
        @php $marquee = count($clients) > 6; @endphp
        <section class="pd-clients {{ $marquee ? 'pd-clients--marquee' : '' }}">
            @if($has($T('clients.1')))<div class="container"><h2 class="pd-clients__title">{!! $T('clients.1') !!}</h2></div>@endif
            <div class="pd-clients__viewport">
                <ul class="pd-clients__track">
                    @foreach($clients as $c)<li><img src="{{ $pic($c['img']) }}" alt="{{ strip_tags($c['name'] ?? '') }}" loading="lazy"></li>@endforeach
                    @if($marquee)
                        @foreach($clients as $c)<li aria-hidden="true"><img src="{{ $pic($c['img']) }}" alt="" loading="lazy"></li>@endforeach
                    @endif
                </ul>
            </div>
        </section>
    @endif

    <!-- ============ OTHER PRODUCTS ============ -->
    @if($on('others') && $others->isNotEmpty())
        <section class="pd-band pd-others">
            <div class="container">
                {!! $head($T('others.1'), $T('others.2')) !!}
                <div class="pd-others__grid" style="--cols: {{ min($others->count(), 3) }}">
                    @foreach($others as $o)
                        <a class="pd-other" href="{{ $o['url'] }}" style="--pd-a: {{ $o['a'] }}; --pd-b: {{ $o['b'] }}">
                            @if($o['img'])<span class="pd-other__media"><img src="{{ $pic($o['img']) }}" alt="" loading="lazy"></span>@endif
                            <span class="pd-other__body">
                                @if($o['logo'])<span class="pd-other__logo"><img src="{{ $pic($o['logo']) }}" alt="{{ $o['name'] }}" loading="lazy"></span>@else<strong class="pd-other__name">{{ $o['name'] }}</strong>@endif
                                @if($has($o['tag']))<span class="pd-other__tag">{!! $o['tag'] !!}</span>@endif
                                <span class="pd-other__link">{!! $T('others.3') ?: 'Explore product' !!}{!! $arrow !!}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ============ CALL TO ACTION ============ -->
    @if($has($T('cta.1')))
        <section class="container pd-cta">
            <div class="pd-cta__box">
                <div class="pd-cta__copy">
                    <h2 class="pd-cta__title">{!! $T('cta.1') !!}</h2>
                    @if($has($T('cta.2')))<p>{!! $T('cta.2') !!}</p>@endif
                </div>
                <div class="pd-cta__btns">
                    @if($has($T('cta.3')))<a href="{{ $T('cta.4') ?: 'contact-us.php' }}" class="btn btn--cta">{!! $T('cta.3') !!}<img src="assets/icons/arrow-right.svg" width="24" height="24" alt=""></a>@endif
                    @if($has($T('cta.5')) && $T('cta.6'))<a href="{{ $T('cta.6') }}" class="btn btn--cta btn--ghost pd-btn-out"@if($ext($T('cta.6'))) target="_blank" rel="noopener"@endif>{!! $T('cta.5') !!}{!! $outIcon !!}</a>@endif
                </div>
            </div>
        </section>
    @endif

</main>
@endsection
