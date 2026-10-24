@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
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
                            <?php if (!empty($s['points']) || !empty($s['para'])): ?>
                                <div class="acc-panel"><div class="acc-panel__inner">
                                    <?php if (!empty($s['para']) && empty($s['points'])): ?><p class="acc-para"><?= $s['para'] ?></p><?php endif; ?>
                                    <?php if (!empty($s['points'])): ?><ul class="dot-list dot-list--plain acc-points">
                                        <?php foreach ($s['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                                    </ul><?php endif; ?>
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
