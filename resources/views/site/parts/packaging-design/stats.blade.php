@php
$ki = 'assets/images/packaging/';   // page images
$kc = 'assets/icons/packaging/';    // page icons
$mc = 'assets/icons/magazine/';     // shared tool glyphs
$vi = 'assets/images/video/';       // shared offer art
$vc = 'assets/icons/video/';        // shared icons
@endphp
    <!-- ============ STATS ============ -->
    <section class="container ve-stats pk-stats">
        <img class="pk-stats__lines" src="<?= $kc ?>stats-lines.svg" alt="">
        <img class="pk-stats__bg" src="{{ $T('stats.1') }}" alt="">
        <div class="ve-stats__content">
            <?php foreach ($pk_stats as $n => $row): ?>
                <?php if ($n): ?><hr class="ve-stats__rule"><?php endif; ?>
                <div class="ve-stats__row">
                    <?php foreach ($row as $i => $s): ?>
                        <?php if ($i): ?><span class="ve-stats__divider"></span><?php endif; ?>
                        <div class="ve-stat">
                            <div class="ve-stat__value"><span data-count="<?= $s[0] ?>"><?= $s[0] ?></span><span><?= $s[1] ?></span></div>
                            <span class="ve-stat__label"><?= $s[2] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
