@extends('site.layout')

@section('content')
@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
<main class="page ve">

    <!-- ============ HERO ============ -->
    <section class="ve-hero">
        <img class="ve-hero__bg" src="{{ $T('hero.1') }}" alt="">
        <img class="ve-hero__pattern" src="assets/icons/showcase-bg.svg" alt="">
        <img class="ve-hero__floor" src="{{ $T('hero.2') }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container ve-hero__cols">
                <div class="ve-hero__copy">
                    <p class="breadcrumb">{!! $T('hero.3') !!}</p>

                    <div class="ve-hero__stack">
                        <div class="ve-hero__group">
                        <div class="ve-hero__intro">
                            <h1 class="h1">{!! $T('hero.4') !!}</h1>
                            <p>{!! $T('hero.5') !!}</p>
                        </div>
                        <ul class="ve-benefits">
                            <?php foreach ($ve_benefits as $b): ?><li><?= $b ?></li><?php endforeach; ?>
                        </ul>
                        </div>
                        <div class="ve-pm">
                            <span class="ve-pm__icon"><img src="<?= $vc ?>pm-users.svg" width="24" height="24" alt=""></span>
                            <div>
                                <strong>{!! $T('hero.6') !!}</strong>
                                <span>{!! $T('hero.7') !!}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="lead-form" action="{{ route('inquiries.store') }}" method="post" data-inquiry-form novalidate>
@csrf
<input type="hidden" name="source" value="{{ $page->slug }}">
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
                    <div class="lead-form__head">
                        <h2>{!! $T('hero.8') !!}</h2>
                        <p>{!! $T('hero.9') !!}</p>
                    </div>
                    <div class="lead-form__fields">
                        <?php foreach (array_chunk($ve_form_fields, 2) as $row): ?>
                            <div class="lead-form__row">
                                <?php foreach ($row as $f): ?>
                                    <label class="field"><span><?= $f[0] ?></span><input type="<?= $f[2] ?>" name="<?= ($f[3] ?? strtolower(str_replace(' ', '_', $f[0]))) ?>" placeholder="<?= $f[1] ?>"></label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <label class="field"><span>{!! $T('hero.10') !!}</span><input type="text" name="subject" placeholder="Enter your subject"></label>
                        <label class="field"><span>{!! $T('hero.11') !!}</span><textarea name="message" rows="1" placeholder="Tell us about your requirements"></textarea></label>
                    </div>
                    <p class="form-status" data-form-status role="status" aria-live="polite"></p>
                    <button type="submit" class="btn btn--cta btn--block">{!! $T('hero.12') !!}</button>
                    <p class="lead-form__privacy"><img src="<?= $vc ?>lock.svg" width="14" height="14" alt="">We respect your privacy. Your information is safe with us.</p>
                </form>
            </div>
        </div>
    </section>

    <!-- ============ PORTFOLIO ============ -->
    <section class="container ve-portfolio">
        <div class="ve-portfolio__head">
            <div class="ve-portfolio__top">
                <div class="ve-portfolio__title">
                    <h2 class="h2">{!! $T('portfolio.3') !!}</h2>
                    <p class="lead lead--left">{!! $T('portfolio.4') !!}</p>
                </div>
                <div class="round-arrows">
                    <button type="button" class="round-arrow" aria-label="Previous" data-pf-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                    <button type="button" class="round-arrow" aria-label="Next" data-pf-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
                </div>
            </div>
            <div class="filter-tabs" data-filter-tabs>
                <?php foreach ($ve_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="pf-cards" data-pf-track>
            <?php foreach ($ve_cards as $c): ?>
                <article class="pf-card" data-cats="<?= $c['cats'] ?>" data-video="<?= $c['video'] ?>" data-title="<?= $c['title'] ?>">
                    <div class="pf-media" data-play>
                        <img class="pf-media__base" src="{{ $T('portfolio.1') }}" alt="">
                        <?php if ($c['overlay']): ?><img class="pf-media__overlay" src="<?= iu($vi, $c['overlay']) ?>" alt=""><?php endif; ?>
                        <span class="pf-media__time">02:45</span>
                        <button type="button" class="pf-media__play" aria-label="Play <?= $c['title'] ?>"><img src="{{ $T('portfolio.2') }}" width="80" height="80" alt=""></button>
                    </div>
                    <div class="pf-meta">
                        <div class="pf-meta__row">
                            <h3><?= $c['title'] ?></h3>
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M3 11L11 3M4 3h7v7" stroke="#111827" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <p><?= $c['meta'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ STATS ============ -->
    <section class="container ve-stats">
        <img class="ve-stats__bg1" src="{{ $T('stats.1') }}" alt="">
        <img class="ve-stats__bg2" src="{{ $T('stats.2') }}" alt="">
        <div class="ve-stats__bg3"><img src="{{ $T('stats.3') }}" alt=""></div>
        <div class="ve-stats__content">
            <?php foreach ([$ve_stats_top, $ve_stats_bottom] as $n => $row): ?>
                <?php if ($n): ?><hr class="ve-stats__rule"><?php endif; ?>
                <div class="ve-stats__row">
                    <?php foreach ($row as $i => $s): ?>
                        <?php if ($i): ?><span class="ve-stats__divider"></span><?php endif; ?>
                        <div class="ve-stat">
                            <div class="ve-stat__value"><span data-count="<?= $s[0] ?>"><?= $s[0] ?></span><span>+</span></div>
                            <span class="ve-stat__label"><?= $s[1] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!} </p>
                <div><a href="{{ $T('why-intelliworkz.4') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.3') !!}</a></div>
            </div>
        </div>
        <div class="pillars">
            <?php foreach ($ve_pillars as $p): ?>
                <article class="pillar" style="background: <?= $p['bg'] ?>">
                    <img class="pillar__img" src="<?= iu($vi, $p['img']) ?>" alt="" style="left: <?= $p['x'] ?>px">
                    <div class="pillar__content">
                        <span class="pillar__icon" style="border-color: <?= $p['ring'] ?>"><img src="<?= iu($vc, $p['icon']) ?>" alt=""></span>
                        <div class="pillar__text">
                            <h3><?= $p['title'] ?></h3>
                            <p><?= $p['text'] ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ EDITING TOOLS ============ -->
    <section class="ve-tools">
        <img class="ve-tools__bg" src="{{ $T('editing-tools.1') }}" alt="">
        <ul class="ve-tools__row">
            <?php foreach ($ve_tools as $i => $t): ?>
                <?php if ($i): ?><li class="ve-tools__divider"></li><?php endif; ?>
                <li class="ve-tool">
                    <img src="<?= iu($vi, $t[0]) ?>" alt="">
                    <div><strong><?= $t[1] ?></strong><span><?= $t[2] ?></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center">
                <h2 class="h2">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    <div><a href="{{ $T('services-workflow.5') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.4') !!}</a></div>
                </div>
            </div>

            <div class="ve-services__content">
                <div class="acc acc--services" data-accordion>
                    <?php foreach ($ve_services as $i => $s): ?>
                        <?php if ($i): ?><hr class="acc__rule"><?php endif; ?>
                        <div class="acc-item <?= $s['open'] ? 'is-open' : '' ?> ">
                            <button type="button" class="acc-head" aria-expanded="<?= $s['open'] ? 'true' : 'false' ?>">
                                <span class="acc-title"><?= $s['title'] ?></span>
                                <span class="acc-icons"><img class="acc-icon acc-icon--closed" src="<?= $vc ?>acc-icon-2.svg" width="42" height="42" alt=""><img class="acc-icon acc-icon--open" src="<?= $vc ?>acc-icon-1.svg" width="42" height="42" alt=""></span>
                            </button>
                            <?php if ($s['points']): ?>
                                <div class="acc-panel"><div class="acc-panel__inner">
                                    <ul class="dot-list dot-list--plain acc-points">
                                        <?php foreach ($s['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                                    </ul>
                                </div></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <img class="ve-services__img" src="{{ $T('services-workflow.1') }}" alt="Video editing services overview">
            </div>
        </div>

        <div class="workflow">
            <div class="workflow__inner">
                <?php foreach ($ve_workflow as $i => $w): ?>
                    <span class="workflow__line" style="left: <?= $i * 304 ?>px; background: <?= $w[1] ?>"></span>
                <?php endforeach; ?>
                <?php foreach ($ve_workflow as $i => $w): ?>
                    <article class="wstep" style="left: <?= 37 + $i * 304 ?>px; --c: <?= $w[1] ?>">
                        <div class="wstep__art">
                            <img class="wstep__ring" src="<?= $vc ?>step-ring-<?= $i + 1 ?>.svg" alt="">
                            <span class="wstep__no" style="left: <?= $w[2] ?>px; width: <?= $w[3] ?>px"><?= $w[0] ?></span>
                            <img class="wstep__img" src="<?= iu($vi, $w[4]) ?>" alt="" style="left: <?= $w[5] ?>px; top: <?= $w[6] ?>px; width: <?= $w[7] ?>px; height: <?= $w[8] ?>px">
                        </div>
                        <div class="wstep__body">
                            <img src="<?= $vc ?>step-conn-<?= $i + 1 ?>.svg" width="14" height="62" alt="">
                            <div class="wstep__copy">
                                <h3><?= $w[9] ?><span></span></h3>
                                <p><?= $w[10] ?></p>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php foreach ([291, 611, 921, 1241, 1551] as $x): ?>
                    <img class="workflow__next" src="<?= $vc ?>next-step-1.svg" alt="" style="left: <?= $x ?>px">
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
    $showcase_decor = [
        ['src' => $vi . 'floor-glow.png',      'style' => 'left:1145.53px;top:508.46px;width:969.91px;height:599.17px;opacity:.5'],
        ['src' => $vi . 'showcase-deco.png',   'style' => 'left:-111.5px;top:766px;width:699.31px;height:208.5px;opacity:.4'],
    ];
@endphp
    @include('site.sections.showcase')

    <!-- ============ WHAT WE OFFER + CTA + ENGAGEMENT ============ -->
    <div class="ve-offer-engagement">
        <section class="ve-offers">
            <div class="ve-offers__head">
                <h2 class="h2 h2--center">{!! $T('what-we-offer-cta-engagement.3') !!}</h2>
                <p>{!! $T('what-we-offer-cta-engagement.4') !!}</p>
            </div>
            <div class="container offer-viewport" data-offers>
            <div class="offer-cards" data-offers-track>
                <?php foreach ($ve_offers as $k => $o): ?>
                    <article class="offer-card" data-i="<?= $k ?>" style="background: <?= $o['bg'] ?>">
                        <div class="offer-card__top">
                            <span class="offer-card__no" style="color: <?= $o['noc'] ?>"><?= $o['no'] ?></span>
                            <a href="#" aria-label="<?= $o['title'] ?>"><img src="<?= iu($vc, $o['arrow']) ?>" width="48" height="48" alt=""></a>
                        </div>
                        <div class="offer-card__body">
                            <h3><?= $o['title'] ?></h3>
                            <p><?= $o['text'] ?></p>
                        </div>
                        <img class="offer-card__img" src="<?= iu($vi, $o['img']) ?>" alt="" style="left: <?= $o['x'] ?>px; top: <?= $o['y'] ?>px; width: <?= $o['w'] ?>px; height: <?= $o['h'] ?>px">
                    </article>
                <?php endforeach; ?>
            </div>
            </div>
            <div class="dots dots--navy dots--center" data-offers-dots>
                <?php foreach ($ve_offers as $k => $o): ?><button type="button" class="dots__dot" aria-label="Show <?= $o['title'] ?>"></button><?php endforeach; ?>
            </div>
        </section>

        <section class="ve-cta">
            <img class="ve-cta__bg" src="{{ $T('what-we-offer-cta-engagement.1') }}" alt="">
            <h2 class="h1">{!! $T('what-we-offer-cta-engagement.5') !!}</h2>
            <?php btn($T('what-we-offer-cta-engagement.2'), 'btn--cta', 'arrow-cta.svg'); ?>
        </section>

        @include('site.sections.engagement')
    </div>

    <!-- ============ FAQs ============ -->
    <section class="container ve-faq">
        <h2 class="h2 h2--center">{!! $T('faqs.1') !!}</h2>
        <div class="acc acc--faq" data-accordion>
            <?php foreach ($ve_faqs as $i => $f): ?>
                <div class="acc-item <?= $i === 0 ? 'is-open' : '' ?>">
                    <button type="button" class="acc-head" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                        <span class="acc-chevron"><img src="<?= $vc ?>faq-chevron.svg" width="20" height="20" alt=""></span>
                        <span class="acc-title"><?= $f[0] ?></span>
                    </button>
                    <div class="acc-panel"><div class="acc-panel__inner"><p><?= $f[1] ?></p></div></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<!-- Video popup -->
<div class="video-modal" id="video-modal" role="dialog" aria-modal="true" aria-label="Video player" hidden>
    <div class="video-modal__backdrop" data-video-close></div>
    <div class="video-modal__box">
        <button type="button" class="video-modal__close" aria-label="Close video" data-video-close>&times;</button>
        <div class="video-modal__title"></div>
        <div class="video-modal__stage"></div>
    </div>
</div>


@endsection
