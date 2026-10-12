@extends('site.layout')

@section('content')
@php
$ci = 'assets/images/collaterals/';  // page images
$cc = 'assets/icons/collaterals/';   // page icons
$mc = 'assets/icons/magazine/';      // shared with the magazine page (metric + tool icons)
$vi = 'assets/images/video/';        // shared with the video page (offer art)
$vc = 'assets/icons/video/';         // shared icons
@endphp
<main class="page ve cl">

    <!-- ============ HERO ============ -->
    <section class="ve-hero ve-hero--plain">
        <div class="cl-hero__art" role="img" aria-label=""></div>
        <img class="ve-hero__pattern" src="assets/icons/showcase-bg.svg" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container ve-hero__cols">
                <div class="ve-hero__copy">
                    <p class="breadcrumb breadcrumb--light">{!! $T('hero.1') !!}</p>

                    <div class="ve-hero__stack">
                        <div class="ve-hero__group">
                            <div class="ve-hero__intro">
                                <h1 class="h1">{!! $T('hero.2') !!}</h1>
                                <p>{!! $T('hero.3') !!}</p>
                            </div>
                            <ul class="ve-benefits">
                                <?php foreach ($cl_benefits as $b): ?><li><?= $b ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="ve-pm">
                            <span class="ve-pm__icon"><img src="<?= $vc ?>pm-users.svg" width="24" height="24" alt=""></span>
                            <div>
                                <strong>{!! $T('hero.4') !!}</strong>
                                <span>{!! $T('hero.5') !!}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="lead-form" action="{{ route('inquiries.store') }}" method="post" data-inquiry-form novalidate>
@csrf
<input type="hidden" name="source" value="{{ $page->slug }}">
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
                    <div class="lead-form__head">
                        <h2>{!! $T('hero.6') !!}</h2> <!-- kept exactly as written in Figma; looks like a leftover from the PPT page -->
                        <p>{!! $T('hero.7') !!}</p>
                    </div>
                    <div class="lead-form__fields">
                        <?php foreach (array_chunk($ve_form_fields, 2) as $row): ?>
                            <div class="lead-form__row">
                                <?php foreach ($row as $f): ?>
                                    <label class="field"><span><?= $f[0] ?></span><input type="<?= $f[2] ?>" name="<?= ($f[3] ?? strtolower(str_replace(' ', '_', $f[0]))) ?>" placeholder="<?= $f[1] ?>"></label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <label class="field"><span>{!! $T('hero.8') !!}</span><input type="text" name="subject" placeholder="Enter your subject"></label>
                        <label class="field"><span>{!! $T('hero.9') !!}</span><textarea name="message" rows="1" placeholder="Tell us about your requirements"></textarea></label>
                    </div>
                    <p class="form-status" data-form-status role="status" aria-live="polite"></p>
                    <button type="submit" class="btn btn--cta btn--block">{!! $T('hero.10') !!}</button>
                    <p class="lead-form__privacy"><img src="<?= $vc ?>lock.svg" width="14" height="14" alt="">We respect your privacy. Your information is safe with us.</p>
                </form>
            </div>
        </div>
    </section>

    <!-- ============ SELECTED WORK ============ -->
    <section class="cl-pf" data-examples data-autoplay="2600">
        <div class="cl-pf__head">
            <div class="cl-pf__top">
                <div class="cl-pf__title">
                    <h2 class="h2">{!! $T('selected-work.1') !!}</h2>
                    <p>{!! $T('selected-work.2') !!}</p>
                </div>
                <div class="round-arrows">
                    <button type="button" class="round-arrow" aria-label="Previous" data-examples-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                    <button type="button" class="round-arrow" aria-label="Next" data-examples-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
                </div>
            </div>
            <div class="filter-tabs">
                <?php foreach ($cl_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="mg-viewport">
            <div class="mg-strip" data-examples-track>
                <?php /* Figma shows a fixed 5-tile strip; duplicate it so the autoplay loop never runs out of tiles. */ ?>
                <?php foreach (array_merge($cl_strip, $cl_strip) as $t): ?>
                    <div class="cl-tile" data-cats="<?= $t['cats'] ?>"><img src="<?= iu($ci, $t['img']) ?>" alt="Marketing and sales collateral example"></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ METRICS (dark) ============ -->
    <section class="container metrics metrics--dark">
        <?php foreach ($cl_metrics as $i => $m): ?>
            <?php if ($i): ?><span class="metrics__divider"></span><?php endif; ?>
            <div class="metric">
                <span class="metric__icon"><img src="<?= iu($mc, $m[0]) ?>" width="24" height="24" alt=""></span>
                <div class="metric__body">
                    <div class="metric__value"><span data-count="<?= $m[1] ?>"><?= $m[1] ?></span><span>+</span></div>
                    <span class="metric__label"><?= $m[2] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!}</p>
                <p>{!! $T('why-intelliworkz.3') !!}</p>
                <div><a href="{{ $T('why-intelliworkz.5') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.4') !!}</a></div>
            </div>
        </div>
        <div class="cl-why">
            <?php foreach ($cl_why as $w): ?>
                <article class="cl-why__card" style="--c: <?= $w['c'] ?>">
                    <img class="cl-why__bg" src="<?= iu($ci, $w['bg']) ?>" alt="">
                    <div class="cl-why__tabbar">
                        <img class="cl-why__tab" src="<?= iu($cc, $w['tab']) ?>" alt="">
                        <span class="cl-why__no" style="color: <?= $w['num'] ?>"><?= $w['no'] ?></span>
                    </div>
                    <div class="cl-why__content">
                        <span class="cl-why__icon"><img src="<?= iu($cc, $w['icon']) ?>" alt=""></span>
                        <div class="cl-why__text">
                            <h3><?= $w['title'] ?></h3>
                            <p><?= $w['text'] ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ TOOLS STRIP ============ -->
    <section class="mg-tools">
        <ul class="mg-tools__row">
            <?php foreach ($cl_tools as $i => $t): ?>
                <?php if ($i): ?><li class="mg-tools__divider"></li><?php endif; ?>
                <li class="mg-tool">
                    <?php if ($t[1]): ?>
                        <span class="mg-tool__icon" style="background: <?= $t[1] ?>"><img src="<?= iu($mc, $t[0]) ?>" alt=""></span>
                    <?php else: ?>
                        <img class="cl-tool__tile" src="<?= iu($ci, $t[0]) ?>" width="108" height="72" alt="">
                    <?php endif; ?>
                    <div><strong><?= $t[2] ?></strong><span><?= $t[3] ?></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow cl-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center cl-services__head">
                <h2 class="h2 cl-services__title">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    <div><a href="{{ $T('services-workflow.5') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.4') !!}</a></div>
                </div>
            </div>

            <div class="ve-services__content">
                <div class="acc acc--services" data-accordion>
                    <?php foreach ($cl_services as $i => $s): ?>
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
                <div class="cl-services__img"><img src="{{ $T('services-workflow.1') }}" alt="Marketing and sales collateral overview"></div>
            </div>
        </div>

        <div class="cl-flow">
            <?php foreach ($cl_flow as $f): ?>
                <span class="cl-flow__seg" style="left: <?= $f['seg'] ?>px; background: <?= $f['c'] ?>"></span>
            <?php endforeach; ?>
            <?php foreach ($cl_flow as $n => $f): ?>
                <img class="cl-flow__img" src="<?= $ci ?>step-<?= $n + 1 ?>.png" alt="" style="left: <?= $f['img'] ?>px">
                <span class="cl-flow__dot" style="left: <?= $f['dot'] ?>px; --c: <?= $f['c'] ?>"></span>
                <div class="cl-flow__text" style="left: <?= $f['tx'] ?>px; width: <?= $f['tw'] ?>px">
                    <h3><?= $f['title'] ?></h3>
                    <p><?= $f['text'] ?></p>
                </div>
            <?php endforeach; ?>
            <?php foreach ($cl_flow_next as $x): ?>
                <img class="cl-flow__next" src="<?= $vc ?>next-step-1.svg" width="30" height="30" alt="" style="left: <?= $x ?>px">
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
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
                    <?php foreach ($cl_offers as $k => $o): ?>
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
                <?php foreach ($cl_offers as $o): ?><button type="button" class="dots__dot" aria-label="Show <?= $o['title'] ?>"></button><?php endforeach; ?>
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
            <?php foreach ($cl_faqs as $i => $f): ?>
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
