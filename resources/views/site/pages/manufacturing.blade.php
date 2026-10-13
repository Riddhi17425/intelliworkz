@extends('site.layout')

@section('content')
@php
$fi = 'assets/images/manufacture/';  // page images
$fc = 'assets/icons/manufacture/';   // page icons
$vi = 'assets/images/video/';        // shared offer art
$vc = 'assets/icons/video/';         // shared icons
@endphp
<main class="page ve mf">

    <!-- ============ HERO ============ -->
    <section class="ve-hero ve-hero--plain mf-hero">
        <img class="mf-hero__photo" src="{{ $T('hero.1') }}" alt="">
        <img class="mf-hero__decor" src="{{ $T('hero.2') }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container mf-hero__copy">
                <p class="breadcrumb breadcrumb--light">{!! $T('hero.3') !!}</p>
                <div class="mf-hero__text">
                    <h1 class="h1">{!! $T('hero.4') !!}</h1>
                    <p>{!! $T('hero.5') !!}</p>
                </div>
                <div class="mf-hero__btns">
                    <a href="{{ $T('hero.7') ?: '#' }}" class="mf-btn mf-btn--red">{!! $T('hero.6') !!}</a>
                    <a href="{{ $T('hero.9') ?: '#' }}" class="mf-btn mf-btn--ghost">{!! $T('hero.8') !!}</a>
                </div>
            </div>
        </div>
    </section>

    <div class="mf-body">

        <!-- ============ BUYERS DECIDE ============ -->
        <section class="container mf-buyers">
            <div class="mf-buyers__copy">
                <h2 class="h2">{!! $T('buyers-decide.2') !!}</h2>
                <div class="mf-buyers__text">
                    <p>{!! $T('buyers-decide.3') !!}</p>
                    <p>{!! $T('buyers-decide.4') !!}</p>
                    <p>{!! $T('buyers-decide.5') !!}</p>
                </div>
            </div>
            <img class="mf-buyers__img" src="{{ $T('buyers-decide.1') }}" alt="Industrial engineering marketing collage">
        </section>

        <!-- ============ WHERE MANUFACTURERS LOSE GROUND ============ -->
        <section class="container mf-lose">
            <h2 class="h2 h2--center">{!! $T('where-manufacturers-lose-ground.1') !!}</h2>
            <div class="mf-lose__row">
                <?php foreach ($mf_lose as $c): ?>
                    <article class="mf-card" style="--c: <?= $c['c'] ?>">
                        <img class="mf-card__img" src="<?= iu($fi, $c['img']) ?>" alt="" style="left: <?= $c['box'][0] ?>px; top: <?= $c['box'][1] ?>px; width: <?= $c['box'][2] ?>px; height: <?= $c['box'][3] ?>px">
                        <h3 class="mf-card__title"><?= $c['title'] ?></h3>
                        <p class="mf-card__text"><?= $c['text'] ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============ DARK: BUILT FOR MANUFACTURERS ============ -->
        <section class="mf-dark">
            <img class="mf-dark__decor mf-dark__decor--top" src="{{ $T('dark-built-for-manufacturers.1') }}" alt="">
            <img class="mf-dark__decor mf-dark__decor--bottom" src="{{ $T('dark-built-for-manufacturers.2') }}" alt="">
            <div class="mf-dark__head">
                <h2 class="h2">{!! $T('dark-built-for-manufacturers.4') !!}</h2>
                <p>{!! $T('dark-built-for-manufacturers.5') !!}</p>
            </div>
            <img class="mf-dark__shot" src="{{ $T('dark-built-for-manufacturers.3') }}" alt="Industrial precision engineering showcase">
            <div class="acc acc--services mf-acc" data-accordion>
                <?php foreach ($mf_services as $i => $s): ?>
                    <div class="acc-item <?= !empty($s['open']) ? 'is-open' : '' ?>">
                        <button type="button" class="acc-head" aria-expanded="<?= !empty($s['open']) ? 'true' : 'false' ?>">
                            <span class="acc-title"><?= $s['title'] ?></span>
                            <span class="acc-icons"><img class="acc-icon acc-icon--closed" src="<?= $fc ?>acc-closed.svg" width="42" height="42" alt=""><img class="acc-icon acc-icon--open" src="<?= $fc ?>acc-open.svg" width="42" height="42" alt=""></span>
                        </button>
                        <div class="acc-panel"><div class="acc-panel__inner"><p><?= $s['text'] ?></p></div></div>
                        <hr class="acc__rule">
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============ WHO WE SUPPORT ============ -->
        <section class="container mf-support">
            <h2 class="h2">{!! $T('who-we-support.1') !!}</h2>
            <div class="mf-support__body">
                <p>{!! $T('who-we-support.2') !!}</p>
                <div><a href="{{ $T('who-we-support.4') ?: '#' }}" class="btn btn--enquire">{!! $T('who-we-support.3') !!}</a></div>
            </div>
        </section>

        <!-- ============ WHAT WE BRING + CTA ============ -->
        <div class="ve-offer-engagement mf-offers-wrap">
            <section class="ve-offers">
                <div class="ve-offers__head">
                    <h2 class="h2 h2--center">{!! $T('what-we-bring-cta.2') !!}</h2>
                    <p>{!! $T('what-we-bring-cta.3') !!}</p>
                </div>
                <div class="container offer-viewport" data-offers>
                    <div class="offer-cards" data-offers-track>
                        <?php foreach ($mf_offers as $k => $o): ?>
                            <article class="offer-card" data-i="<?= $k ?>" style="background: <?= $o['bg'] ?>">
                                <div class="offer-card__top">
                                    <span class="offer-card__no" style="color: <?= $o['noc'] ?>"><?= $o['no'] ?></span>
                                    <a href="<?= e(!empty($o['href']) ? $o['href'] : 'contact-us.php') ?>" aria-label="<?= $o['title'] ?>"><img src="<?= iu($vc, $o['arrow']) ?>" width="48" height="48" alt=""></a>
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
                    <?php foreach ($mf_offers as $o): ?><button type="button" class="dots__dot" aria-label="Show <?= $o['title'] ?>"></button><?php endforeach; ?>
                </div>
            </section>

            <section class="cta-band">
                <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
                <h2 class="cta-title">{!! $T('what-we-bring-cta.4') !!}</h2>
                <?php btn($T('what-we-bring-cta.1'), 'btn--cta', 'arrow-cta.svg', $T('what-we-bring-cta.1.url', 'contact-us.php')); ?>
            </section>
        </div>

        <!-- ============ FAQs ============ -->
        <section class="container ve-faq">
            <h2 class="h2 h2--center">{!! $T('faqs.1') !!}</h2>
            <div class="acc acc--faq" data-accordion>
                <?php foreach ($mf_faqs as $i => $f): ?>
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

    </div>

</main>


@endsection
