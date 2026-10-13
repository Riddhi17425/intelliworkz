@php
$ti = 'assets/images/ppt/';          // page images
$tc = 'assets/icons/ppt/';           // page icons
$pi = 'assets/images/prepress/';     // stats photo (same Figma image as the Pre-Press page)
$pc = 'assets/icons/prepress/';      // why-card icons (same card design as the Pre-Press page)
$mc = 'assets/icons/magazine/';      // shared tool glyphs
$vi = 'assets/images/video/';        // shared offer art
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ STATS ============ -->
    <section class="container ve-stats pp-stats">
        <img class="ppt-stats__layer" src="{{ $T('stats.1') }}" alt="">
        <img class="pp-stats__bg" src="{{ $T('stats.2') }}" alt="">
        <div class="ve-stats__content">
            <?php foreach ([$ppt_stats_top, $ppt_stats_bottom] as $n => $row): ?>
                <?php if ($n): ?><hr class="ve-stats__rule"><?php endif; ?>
                <div class="ve-stats__row">
                    <?php foreach ($row as $i => $s): ?>
                        <?php if ($i): ?><span class="ve-stats__divider"></span><?php endif; ?>
                        <div class="ve-stat">
                            <div class="ve-stat__value"><span data-count="<?= $s[0] ?>"><?= $s[0] ?></span><span>+</span></div>
                            <span class="ve-stat__label"><?= $s[1] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
