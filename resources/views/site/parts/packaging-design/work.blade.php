@php
$ki = 'assets/images/packaging/';   // page images
$kc = 'assets/icons/packaging/';    // page icons
$mc = 'assets/icons/magazine/';     // shared tool glyphs
$vi = 'assets/images/video/';       // shared offer art
$vc = 'assets/icons/video/';        // shared icons
@endphp
    <!-- ============ SELECTED WORK ============ -->
    <section class="container pk-pf">
        <div class="pk-pf__head">
            <div class="pk-pf__title">
                <h2 class="h2">{!! $T('selected-work.1') !!}</h2>
                <p>{!! $T('selected-work.2') !!}</p>
            </div>
            <?php if ($T('filter-tabs.1', '1') !== '0'): ?>
            <div class="filter-tabs" data-pk-tabs>
                <?php foreach ($pk_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="pk-grid" data-pk-grid>
            <?php foreach ($pk_tiles as $t): ?>
                <div class="pk-tile" style="left: <?= $t['x'] ?>px; top: <?= $t['y'] ?>px; width: <?= $t['w'] ?>px; height: <?= $t['h'] ?>px">
                    <?php /* every tab brings its own picture for this slot: data-img-<tab key> */ ?>
                    <img src="<?= iu($ki, $t['img']) ?>" alt="Packaging design example" style="height: <?= $t['ih'] ?>px"<?php foreach ($pk_filters as $key => $label): ?> data-img-<?= $key ?>="<?= e(iu($ki, $t[$key] ?? $t['img'])) ?>"<?php endforeach; ?>>
                    <span class="pk-tile__meta">
                        <span class="pk-tile__row"><strong>{!! $T('selected-work.3') !!}</strong></span>
                        <span class="pk-tile__sub">{!! $T('selected-work.4') !!}</span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
