@php
$si = 'assets/images/seo/';    // page images
$sc = 'assets/icons/seo/';     // page icons
$vi = 'assets/images/video/';  // shared with video/magazine pages (offer image 4)
$vc = 'assets/icons/video/';   // shared icons
@endphp
    <!-- ============ SELECTED WORK ============ -->
    <section class="seo-pf">
        <div class="seo-pf__head">
            <div class="seo-pf__top">
                <div class="seo-pf__title">
                    <h2 class="h2">{!! $T('selected-work.3') !!}</h2>
                    <p>{!! $T('selected-work.4') !!}</p>
                </div>
                <div class="round-arrows">
                    <button type="button" class="round-arrow" aria-label="Previous" data-gallery-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                    <button type="button" class="round-arrow" aria-label="Next" data-gallery-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
                </div>
            </div>
            <?php if ($T('filter-tabs.1', '1') !== '0'): ?>
            <div class="filter-tabs">
                <?php foreach ($seo_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="seo-pf__scene">
            <img class="seo-pf__bg" src="{{ $T('selected-work.1') }}" alt="">
            <img class="seo-pf__main" src="{{ $T('selected-work.2') }}" alt="SEO results dashboard: keyword ranking, monthly clicks, traffic growth">

            <div class="seo-gallery">
                <button type="button" class="round-arrow" aria-label="Previous" data-gallery-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                <div class="seo-gallery__viewport" data-gallery>
                    <div class="seo-gallery__track" data-gallery-track>
                        <?php foreach ($seo_gallery as $i => $g): ?>
                            <button type="button" class="seo-gallery__tile <?= $i === 5 ? 'is-featured' : '' ?>" data-cats="<?= $g['cats'] ?>">
                                <img src="<?= iu($si, $g['img']) ?>" alt="SEO work example">
                                <span class="seo-gallery__badge"><?= sprintf('%02d', $i + 1) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="round-arrow" aria-label="Next" data-gallery-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
            </div>

            <div class="seo-pf__panel">
                <h3>{!! $T('selected-work.5') !!}</h3> <!-- kept exactly as written in Figma; looks like a typo for "Flexible" -->
                <p class="seo-pf__panel-sub">{!! $T('selected-work.6') !!}</p>
                <p class="seo-pf__panel-text">{!! $T('selected-work.7') !!}</p>
            </div>
        </div>
    </section>
