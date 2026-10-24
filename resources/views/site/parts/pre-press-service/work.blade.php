@php
$pi = 'assets/images/prepress/';      // page images
$pc = 'assets/icons/prepress/';       // page icons
$ci = 'assets/images/collaterals/';   // image-strip tiles (same two images as the Collaterals page)
$si = 'assets/images/seo/';           // tools-strip photo (same Figma image as the SEO page)
$mc = 'assets/icons/magazine/';       // shared tool glyphs
$vi = 'assets/images/video/';         // shared offer art
$vc = 'assets/icons/video/';          // shared icons
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
            <?php if ($T('filter-tabs.1', '1') !== '0' && !empty($pp_filters)): ?>
            <div class="filter-tabs">
                <?php foreach ($pp_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="mg-viewport">
            <div class="mg-strip" data-examples-track>
                <?php foreach (array_merge($pp_strip, $pp_strip) as $t): ?>
                    <div class="cl-tile" data-cats="<?= $t['cats'] ?>"><img src="<?= iu($ci, $t['img']) ?>" alt="Pre-press work example"></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
