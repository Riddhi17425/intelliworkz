@php
$ki = 'assets/images/packaging/';   // page images
$kc = 'assets/icons/packaging/';    // page icons
$mc = 'assets/icons/magazine/';     // shared tool glyphs
$vi = 'assets/images/video/';       // shared offer art
$vc = 'assets/icons/video/';        // shared icons
@endphp
    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow pk-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center seo-services__head">
                <h2 class="h2 pk-services__title">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    <p>{!! $T('services-workflow.4') !!}</p>
                    @if(trim(strip_tags($T('services-workflow.5'))) !== '')<div><a href="{{ $T('services-workflow.6') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.5') !!}</a></div>@endif
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
                                <?php if (!empty($s['para'])): ?><p class="acc-para"><?= $s['para'] ?></p><?php endif; ?>
                                <?php if (!empty($s['points'])): ?><ul class="dot-list dot-list--plain acc-points">
                                    <?php foreach ($s['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                                </ul><?php endif; ?>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                    <hr class="acc__rule">
                </div>
                <div class="cl-services__img pk-services__img"><img src="{{ $T('services-workflow.1') }}" alt="Packaging design services overview"></div>
            </div>
        </div>

        @if(($page->slug ?? '') === 'logo-design')
        {{-- Logo Design only: the same 6-step ring layout as the Brand Identity page, filled with this page's own steps and pictures --}}
        @php
            $wfc = ['#F6B700', '#CF6CB9', '#59B38D', '#C9AC61', '#53A2BC', '#7F68CE'];
            $wfn = [[46, 51], [26, 71], [25, 72], [25, 72], [22, 75], [22, 75]];   // number: left, width
        @endphp
        <div class="pk-flow-head ve-flow-head" style="text-align:center">
            <h2 class="h2 h2--center">{!! $T('services-workflow.7') !!}</h2>
            <p>{!! $T('services-workflow.8') !!}</p>
        </div>
        <div class="workflow">
            <div class="workflow__inner">
                <?php foreach ($pk_flow as $i => $w): ?>
                    <span class="workflow__line" style="left: <?= $i * 304 ?>px; background: <?= $wfc[$i % 6] ?>"></span>
                <?php endforeach; ?>
                <?php foreach ($pk_flow as $i => $w): ?>
                    <article class="wstep" style="left: <?= 37 + $i * 304 ?>px; --c: <?= $wfc[$i % 6] ?>">
                        <div class="wstep__art">
                            <img class="wstep__ring" src="<?= $vc ?>step-ring-<?= ($i % 6) + 1 ?>.svg" alt="">
                            <span class="wstep__no" style="left: <?= $wfn[$i % 6][0] ?>px; width: <?= $wfn[$i % 6][1] ?>px"><?= $w['no'] ?></span>
                            <img class="wstep__img" src="<?= e(iu($ki, $w['img'])) ?>" alt="" style="left: 24px; top: 84px; width: 168px; height: 120px; object-fit: contain">
                        </div>
                        <div class="wstep__body">
                            <img src="<?= $vc ?>step-conn-<?= ($i % 6) + 1 ?>.svg" width="14" height="62" alt="">
                            <div class="wstep__copy">
                                <h3><?= $w['title'] ?><span></span></h3>
                                <p><?= $w['text'] ?></p>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php foreach ([291, 611, 921, 1241, 1551] as $x): ?>
                    <img class="workflow__next" src="<?= $vc ?>next-step-1.svg" alt="" style="left: <?= $x ?>px">
                <?php endforeach; ?>
            </div>
        </div>
        @else
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
        @endif
    </section>
