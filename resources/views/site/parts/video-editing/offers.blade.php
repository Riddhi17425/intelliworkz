@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
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
                <?php foreach ($ve_offers as $k => $o): ?><button type="button" class="dots__dot" aria-label="Show <?= $o['title'] ?>"></button><?php endforeach; ?>
            </div>
        </section>

        <section class="ve-cta">
            <img class="ve-cta__bg" src="{{ $T('what-we-offer-cta-engagement.1') }}" alt="">
            <h2 class="h1">{!! $T('what-we-offer-cta-engagement.5') !!}</h2>
            <?php btn($T('what-we-offer-cta-engagement.2'), 'btn--cta', 'arrow-cta.svg', $T('what-we-offer-cta-engagement.2.url', 'contact-us.php')); ?>
        </section>

        @include('site.sections.engagement')
    </div>
