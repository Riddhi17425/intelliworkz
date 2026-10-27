@php
$ci = 'assets/images/collaterals/';  // page images
$cc = 'assets/icons/collaterals/';   // page icons
$mc = 'assets/icons/magazine/';      // shared with the magazine page (metric + tool icons)
$vi = 'assets/images/video/';        // shared with the video page (offer art)
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow cl-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center cl-services__head">
                <h2 class="h2 cl-services__title">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    @if(trim(strip_tags($T('services-workflow.4'))) !== '')<div><a href="{{ $T('services-workflow.5') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.4') !!}</a></div>@endif
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
                                <?php if (!empty($s['para'])): ?><p class="acc-para"><?= $s['para'] ?></p><?php endif; ?>
                                <?php if (!empty($s['points'])): ?><ul class="dot-list dot-list--plain acc-points">
                                    <?php foreach ($s['points'] as $pt): ?><li><span><?= $pt ?></span></li><?php endforeach; ?>
                                </ul><?php endif; ?>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                    <hr class="acc__rule">
                </div>
                <div class="cl-services__img"><img src="{{ $T('services-workflow.1') }}" alt="Marketing and sales collateral overview"></div>
            </div>
        </div>

        <?php if ($T('services-workflow.6')): ?><h2 class="h2 h2--center cl-flow__title">{!! $T('services-workflow.6') !!}</h2><?php endif; ?>
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
