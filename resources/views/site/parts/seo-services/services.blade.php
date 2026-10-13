@php
$si = 'assets/images/seo/';    // page images
$sc = 'assets/icons/seo/';     // page icons
$vi = 'assets/images/video/';  // shared with video/magazine pages (offer image 4)
$vc = 'assets/icons/video/';   // shared icons
@endphp
    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center seo-services__head">
                <h2 class="h2 seo-services__title">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    <p>{!! $T('services-workflow.4') !!}</p>
                    <p>{!! $T('services-workflow.5') !!}</p>
                    <div><a href="{{ $T('services-workflow.7') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.6') !!}</a></div>
                </div>
            </div>

            <div class="ve-services__content">
                <div class="acc acc--services" data-accordion>
                    <?php foreach ($seo_services as $i => $s): ?>
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
                </div>
                <div class="seo-services__img"><img src="{{ $T('services-workflow.1') }}" alt="SEO services overview"></div>
            </div>
        </div>

        <div class="seo-workflow">
            <?php foreach ($seo_workflow as $w): ?>
                <article class="seo-step" style="--c: <?= $w['color'] ?>">
                    <div class="seo-step__art">
                        <img src="<?= iu($si, $w['img']) ?>" alt="" style="left: <?= $w['art'][0] ?>px; top: <?= $w['art'][1] ?>px; width: <?= $w['art'][2] ?>px; height: <?= $w['art'][3] ?>px">
                    </div>
                    <div class="seo-step__no"><?= $w['no'] ?><span></span></div>
                    <div class="seo-step__copy">
                        <h3><?= $w['title'] ?></h3>
                        <p><?= $w['text'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
