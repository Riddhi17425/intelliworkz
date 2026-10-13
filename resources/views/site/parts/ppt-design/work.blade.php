@php
$ti = 'assets/images/ppt/';          // page images
$tc = 'assets/icons/ppt/';           // page icons
$pi = 'assets/images/prepress/';     // stats photo (same Figma image as the Pre-Press page)
$pc = 'assets/icons/prepress/';      // why-card icons (same card design as the Pre-Press page)
$mc = 'assets/icons/magazine/';      // shared tool glyphs
$vi = 'assets/images/video/';        // shared offer art
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ SELECTED WORK ============ -->
    <section class="cl-pf ppt-pf">
        <div class="cl-pf__head">
            <div class="cl-pf__top">
                <div class="cl-pf__title">
                    <h2 class="h2">{!! $T('selected-work.5') !!}</h2>
                    <p>{!! $T('selected-work.6') !!}</p>
                </div>
                <div class="round-arrows">
                    <button type="button" class="round-arrow" aria-label="Previous" data-gallery-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                    <button type="button" class="round-arrow" aria-label="Next" data-gallery-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
                </div>
            </div>
            <div class="filter-tabs">
                <?php foreach ($ppt_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ppt-scene">
            <img class="ppt-scene__bg" src="{{ $T('selected-work.1') }}" alt="">
            <img class="ppt-scene__layer ppt-scene__layer--left" src="{{ $T('selected-work.2') }}" alt="">
            <img class="ppt-scene__layer ppt-scene__layer--right" src="{{ $T('selected-work.3') }}" alt="">
            <img class="ppt-scene__main" src="{{ $T('selected-work.4') }}" alt="Presentation design examples">

            <div class="ppt-gallery">
                <button type="button" class="round-arrow" aria-label="Previous" data-gallery-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                <div class="ppt-gallery__viewport" data-gallery>
                    <div class="ppt-gallery__track" data-gallery-track>
                        <?php foreach ($ppt_thumbs as $i => $g): ?>
                            <button type="button" class="ppt-gallery__tile <?= $i === 2 ? 'is-featured' : '' ?>" data-cats="<?= $g['cats'] ?>"><img src="<?= iu($ti, $g['img']) ?>" alt="Presentation example <?= $i + 1 ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="round-arrow" aria-label="Next" data-gallery-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
            </div>
        </div>
    </section>
