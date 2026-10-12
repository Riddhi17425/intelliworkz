@extends('site.layout')

@section('content')
@php
$ki = 'assets/images/packaging/';   // page images
$kc = 'assets/icons/packaging/';    // page icons
$mc = 'assets/icons/magazine/';     // shared tool glyphs
$vi = 'assets/images/video/';       // shared offer art
$vc = 'assets/icons/video/';        // shared icons
@endphp
<main class="page ve pk">

    <!-- ============ HERO ============ -->
    <section class="ve-hero ve-hero--plain">
        <img class="ve-hero__pattern" src="assets/icons/showcase-bg.svg" alt="">
        <img class="pk-hero__art" src="{{ $T('hero.1') }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container ve-hero__cols">
                <div class="ve-hero__copy">
                    <p class="breadcrumb breadcrumb--light">{!! $T('hero.2') !!}</p>

                    <div class="ve-hero__stack">
                        <div class="ve-hero__group">
                            <div class="ve-hero__intro">
                                <h1 class="h1">{!! $T('hero.3') !!}</h1>
                                <p>{!! $T('hero.4') !!}</p>
                            </div>
                            <ul class="ve-benefits">
                                <?php foreach ($pk_benefits as $b): ?><li><?= $b ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="ve-pm">
                            <span class="ve-pm__icon"><img src="<?= $vc ?>pm-users.svg" width="24" height="24" alt=""></span>
                            <div>
                                <strong>{!! $T('hero.5') !!}</strong>
                                <span>{!! $T('hero.6') !!}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="lead-form" action="#" method="post">
                    <div class="lead-form__head">
                        <h2>{!! $T('hero.7') !!}</h2>
                        <p>{!! $T('hero.8') !!}</p>
                    </div>
                    <div class="lead-form__fields">
                        <?php foreach (array_chunk($ve_form_fields, 2) as $row): ?>
                            <div class="lead-form__row">
                                <?php foreach ($row as $f): ?>
                                    <label class="field"><span><?= $f[0] ?></span><input type="<?= $f[2] ?>" name="<?= strtolower(str_replace(' ', '_', $f[0])) ?>" placeholder="<?= $f[1] ?>"></label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <label class="field"><span>{!! $T('hero.9') !!}</span><input type="text" name="subject" placeholder="Enter your subject"></label>
                        <label class="field"><span>{!! $T('hero.10') !!}</span><textarea name="message" rows="1" placeholder="Tell us about your requirements"></textarea></label>
                    </div>
                    <button type="submit" class="btn btn--cta btn--block">{!! $T('hero.11') !!}</button>
                    <p class="lead-form__privacy"><img src="<?= $vc ?>lock.svg" width="14" height="14" alt="">We respect your privacy. Your information is safe with us.</p>
                </form>
            </div>
        </div>
    </section>

    <!-- ============ SELECTED WORK ============ -->
    <section class="container pk-pf">
        <div class="pk-pf__head">
            <div class="pk-pf__title">
                <h2 class="h2">{!! $T('selected-work.1') !!}</h2>
                <p>{!! $T('selected-work.2') !!}</p>
            </div>
            <div class="filter-tabs" data-pk-tabs>
                <?php foreach ($pk_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="pk-grid" data-pk-grid>
            <?php foreach ($pk_tiles as $t): ?>
                <a href="#" class="pk-tile" data-cats="<?= $t['cats'] ?>" style="left: <?= $t['x'] ?>px; top: <?= $t['y'] ?>px; width: <?= $t['w'] ?>px; height: <?= $t['h'] ?>px">
                    <img src="<?= iu($ki, $t['img']) ?>" alt="Packaging design example" style="height: <?= $t['ih'] ?>px">
                    <span class="pk-tile__meta">
                        <span class="pk-tile__row"><strong>{!! $T('selected-work.3') !!}</strong></span>
                        <span class="pk-tile__sub">{!! $T('selected-work.4') !!}</span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ STATS ============ -->
    <section class="container ve-stats pk-stats">
        <img class="pk-stats__lines" src="<?= $kc ?>stats-lines.svg" alt="">
        <img class="pk-stats__bg" src="{{ $T('stats.1') }}" alt="">
        <div class="ve-stats__content">
            <?php foreach ($pk_stats as $n => $row): ?>
                <?php if ($n): ?><hr class="ve-stats__rule"><?php endif; ?>
                <div class="ve-stats__row">
                    <?php foreach ($row as $i => $s): ?>
                        <?php if ($i): ?><span class="ve-stats__divider"></span><?php endif; ?>
                        <div class="ve-stat">
                            <div class="ve-stat__value"><span data-count="<?= $s[0] ?>"><?= $s[0] ?></span><span><?= $s[1] ?></span></div>
                            <span class="ve-stat__label"><?= $s[2] ?></span>
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
                <p>{!! $T('why-intelliworkz.2') !!}</p>
                <p>{!! $T('why-intelliworkz.3') !!}</p>
                <p>{!! $T('why-intelliworkz.4') !!}</p>
                <div><a href="#" class="btn btn--enquire">{!! $T('why-intelliworkz.5') !!}</a></div>
            </div>
        </div>
        <div class="pk-why">
            <?php foreach ($pk_why as $w): ?>
                <article class="pk-why__card" style="--c: <?= $w['c'] ?>">
                    <img class="pk-why__bg" src="<?= iu($ki, $w['bg']) ?>" alt="">
                    <span class="pk-why__no"><?= $w['no'] ?></span>
                    <div class="pk-why__content">
                        <span class="pk-why__icon"><img src="<?= iu($kc, $w['icon']) ?>" alt=""></span>
                        <div class="pk-why__text">
                            <h3><?= $w['title'] ?></h3>
                            <p><?= $w['text'] ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ TOOLS STRIP ============ -->
    <section class="pk-tools">
        <img class="pk-tools__photo" src="{{ $T('tools-strip.1') }}" alt="">
        <ul class="pk-tools__row">
            <?php foreach ($pk_tools as $i => $t): ?>
                <?php if ($i): ?><li class="pk-tools__divider"></li><?php endif; ?>
                <li class="seo-tool">
                    <span class="mg-tool__icon" style="background: <?= $t[1] ?>"><img src="<?= iu($mc, $t[0]) ?>" alt=""></span>
                    <div><strong><?= $t[2] ?></strong><span><?= $t[3] ?></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow pk-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center seo-services__head">
                <h2 class="h2 pk-services__title">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    <p>{!! $T('services-workflow.4') !!}</p>
                    <div><a href="#" class="btn btn--enquire">{!! $T('services-workflow.5') !!}</a></div>
                </div>
            </div>

            <div class="ve-services__content">
                <div class="acc acc--services" data-accordion>
                    <?php foreach ($pk_services as $i => $s): ?>
                        <?php if ($i): ?><hr class="acc__rule"><?php endif; ?>
                        <div class="acc-item <?= !empty($s['open']) ? 'is-open' : '' ?>">
                            <button type="button" class="acc-head" aria-expanded="<?= !empty($s['open']) ? 'true' : 'false' ?>">
                                <span class="acc-title"><?= $s['title'] ?></span>
                                <span class="acc-icons"><img class="acc-icon acc-icon--closed" src="<?= $vc ?>acc-icon-2.svg" width="42" height="42" alt=""><img class="acc-icon acc-icon--open" src="<?= $vc ?>acc-icon-1.svg" width="42" height="42" alt=""></span>
                            </button>
                            <div class="acc-panel"><div class="acc-panel__inner">
                                <ul class="dot-list dot-list--plain acc-points">
                                    <?php foreach ($s['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                                </ul>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                    <hr class="acc__rule">
                </div>
                <div class="cl-services__img pk-services__img"><img src="{{ $T('services-workflow.1') }}" alt="Packaging design services overview"></div>
            </div>
        </div>

        <div class="pk-flow-wrap">
            <div class="pk-flow-head">
                <h2 class="h2 h2--center">{!! $T('services-workflow.7') !!}</h2>
                <p>{!! $T('services-workflow.8') !!}</p>
            </div>
            <div class="pk-flow">
                <?php $segX = [-1, 359, 720, 1081, 1441]; $segW = [360, 361, 361, 360, 361]; ?>
                <?php foreach ($pk_flow as $i => $f): ?>
                    <span class="pk-flow__seg" style="left: <?= $segX[$i] ?>px; width: <?= $segW[$i] ?>px; background: <?= $f['c'] ?>"></span>
                <?php endforeach; ?>
                <?php foreach ($pk_flow as $i => $f): ?>
                    <article class="pk-step" style="left: <?= [39, 400, 762, 1123, 1484][$i] ?>px; --c: <?= $f['c'] ?>">
                        <div class="pk-step__art">
                            <span class="pk-step__no" style="left: <?= $f['nx'] ?>px"><?= $f['no'] ?></span>
                            <img src="<?= iu($ki, $f['img']) ?>" alt="" style="width: <?= $f['iw'] ?>px; height: <?= $f['ih'] ?>px">
                        </div>
                        <div class="pk-step__copy">
                            <span class="pk-step__dot"></span>
                            <h3><?= $f['title'] ?><span></span></h3>
                            <p><?= $f['text'] ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php foreach ([348, 708, 1068, 1438] as $x): ?>
                    <img class="pk-flow__next" src="<?= $vc ?>next-step-1.svg" width="30" height="30" alt="" style="left: <?= $x ?>px">
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase.1');
@endphp
    @include('site.sections.showcase')

    <!-- ============ WHAT WE OFFER + CTA + ENGAGEMENT ============ -->
    <div class="ve-offer-engagement">
        <section class="ve-offers">
            <div class="ve-offers__head">
                <h2 class="h2 h2--center">{!! $T('what-we-offer-cta-engagement.2') !!}</h2>
                <p>{!! $T('what-we-offer-cta-engagement.3') !!}</p>
            </div>
            <div class="container offer-viewport" data-offers>
                <div class="offer-cards" data-offers-track>
                    <?php foreach ($pk_offers as $k => $o): ?>
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
                <?php foreach ($pk_offers as $o): ?><button type="button" class="dots__dot" aria-label="Show <?= $o['title'] ?>"></button><?php endforeach; ?>
            </div>
        </section>

        <section class="cta-band">
            <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
            <h2 class="cta-title">{!! $T('what-we-offer-cta-engagement.4') !!}</h2>
            <?php btn($T('what-we-offer-cta-engagement.1'), 'btn--cta', 'arrow-cta.svg'); ?>
        </section>

        @include('site.sections.engagement')
    </div>

    <!-- ============ FAQs ============ -->
    <section class="container ve-faq">
        <h2 class="h2 h2--center">{!! $T('faqs.1') !!}</h2>
        <div class="acc acc--faq" data-accordion>
            <?php foreach ($pk_faqs as $i => $f): ?>
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


@endsection
