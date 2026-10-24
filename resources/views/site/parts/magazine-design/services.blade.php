@php
$mi = 'assets/images/magazine/';   // page images
$mc = 'assets/icons/magazine/';    // page icons
$vi = 'assets/images/video/';      // images shared with the video page (offer cards)
$vc = 'assets/icons/video/';       // icons shared with the video page
@endphp
    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center">
                <h2 class="h2">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!} </p>
                    <div><a href="{{ $T('services-workflow.5') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.4') !!}</a></div>
                </div>
            </div>

            <div class="ve-services__content">
                <div class="acc acc--services acc--mag" data-accordion>
                    <?php foreach ($mg_services as $i => $s): ?>
                        <?php if ($i): ?><hr class="acc__rule"><?php endif; ?>
                        <div class="acc-item <?= !empty($s['open']) ? 'is-open' : '' ?>">
                            <button type="button" class="acc-head" aria-expanded="<?= !empty($s['open']) ? 'true' : 'false' ?>">
                                <span class="acc-title"><?= $s['title'] ?></span>
                                <span class="acc-icons"><img class="acc-icon acc-icon--closed" src="<?= $mc ?>acc-closed.svg" width="42" height="42" alt=""><img class="acc-icon acc-icon--open" src="<?= $mc ?>acc-open.svg" width="42" height="42" alt=""></span>
                            </button>
                            <div class="acc-panel"><div class="acc-panel__inner">
                                <?php if (!empty($s['para']) && empty($s['points'])): ?><p class="acc-para"><?= $s['para'] ?></p><?php endif; ?>
                                <?php if (!empty($s['points'])): ?><ul class="dot-list dot-list--plain acc-points">
                                    <?php foreach ($s['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                                </ul><?php endif; ?>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <img class="mg-services__img" src="{{ $T('services-workflow.1') }}" alt="Magazine design services overview">
            </div>
        </div>

        <div class="mg-workflow">
            <img class="mg-workflow__art" src="<?= $mc ?>workflow-infographic.svg" width="1140" height="547" alt="">
            <img class="mg-workflow__icon" src="<?= $mc ?>workflow-icon.svg" width="72" height="72" alt="">
            <div class="mg-workflow__title">
                <h2 class="h2">{!! $T('services-workflow.6') !!}</h2>
                <p>{!! $T('services-workflow.7') !!}</p>
            </div>
            <?php foreach ($mg_flow as $f): ?>
                <div class="mg-flow" style="left: <?= $f['x'] ?>px; top: <?= $f['y'] ?>px; width: <?= $f['w'] ?>px; text-align: <?= $f['align'] ?>">
                    <h3 style="width: <?= $f['tw'] ?>px"><?= $f['title'] ?></h3>
                    <p><?= $f['text'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
