@php
$ci = 'assets/images/collaterals/';  // page images
$cc = 'assets/icons/collaterals/';   // page icons
$mc = 'assets/icons/magazine/';      // shared with the magazine page (metric + tool icons)
$vi = 'assets/images/video/';        // shared with the video page (offer art)
$vc = 'assets/icons/video/';         // shared icons
@endphp
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
            <?php if ($T('filter-tabs.1', '1') !== '0' && !empty($cl_filters)): ?>
            <div class="filter-tabs">
                <?php foreach ($cl_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
