@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
    <!-- ============ STATS ============ -->
    <section class="container ve-stats">
        <img class="ve-stats__bg1" src="{{ $T('stats.1') }}" alt="">
        <img class="ve-stats__bg2" src="{{ $T('stats.2') }}" alt="">
        <div class="ve-stats__bg3"><img src="{{ $T('stats.3') }}" alt=""></div>
        <div class="ve-stats__content">
            <?php foreach ([$ve_stats_top, $ve_stats_bottom] as $n => $row): ?>
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
