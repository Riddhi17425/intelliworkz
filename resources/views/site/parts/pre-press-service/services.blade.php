@php
$pi = 'assets/images/prepress/';      // page images
$pc = 'assets/icons/prepress/';       // page icons
$ci = 'assets/images/collaterals/';   // image-strip tiles (same two images as the Collaterals page)
$si = 'assets/images/seo/';           // tools-strip photo (same Figma image as the SEO page)
$mc = 'assets/icons/magazine/';       // shared tool glyphs
$vi = 'assets/images/video/';         // shared offer art
$vc = 'assets/icons/video/';          // shared icons
@endphp
    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow pp-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center pp-services__head">
                <h2 class="h2 pp-services__title">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    <div><a href="{{ $T('services-workflow.5') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.4') !!}</a></div>
                </div>
            </div>

            <div class="ve-services__content">
                <div class="acc acc--services" data-accordion>
                    <?php foreach ($pp_services as $i => $s): ?>
                        <?php if ($i): ?><hr class="acc__rule"><?php endif; ?>
                        <div class="acc-item <?= !empty($s['open']) ? 'is-open' : '' ?>">
                            <button type="button" class="acc-head" aria-expanded="<?= !empty($s['open']) ? 'true' : 'false' ?>">
                                <span class="acc-title"><?= $s['title'] ?></span>
                                <span class="acc-icons"><img class="acc-icon acc-icon--closed" src="<?= $vc ?>acc-icon-2.svg" width="42" height="42" alt=""><img class="acc-icon acc-icon--open" src="<?= $vc ?>acc-icon-1.svg" width="42" height="42" alt=""></span>
                            </button>
                            <div class="acc-panel"><div class="acc-panel__inner">
                                <?php if (!empty($s['para']) && empty($s['points'])): ?><p class="acc-para"><?= $s['para'] ?></p><?php endif; ?>
                                <?php if (!empty($s['points'])): ?><ul class="dot-list dot-list--plain acc-points">
                                    <?php foreach ($s['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                                </ul><?php endif; ?>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                    <hr class="acc__rule">
                </div>
                <div class="cl-services__img"><img src="{{ $T('services-workflow.1') }}" alt="Pre-press services overview"></div>
            </div>
        </div>

        <div class="pp-flow">
            <?php foreach ($pp_flow as $n => $f): ?>
                <article class="pp-step" style="background: <?= $f['bg'] ?>; --c: <?= $f['c'] ?>">
                    <span class="pp-step__no"><?= $f['no'] ?></span>
                    <h3><?= $f['title'] ?></h3>
                    <p><?= $f['text'] ?></p>
                    <img src="<?= $pi ?>step-<?= $n + 1 ?>.png" alt="" style="top: <?= $f['iy'] ?>px">
                </article>
            <?php endforeach; ?>
        </div>
    </section>
