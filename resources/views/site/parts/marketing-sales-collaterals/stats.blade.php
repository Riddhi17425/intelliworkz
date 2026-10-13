@php
$ci = 'assets/images/collaterals/';  // page images
$cc = 'assets/icons/collaterals/';   // page icons
$mc = 'assets/icons/magazine/';      // shared with the magazine page (metric + tool icons)
$vi = 'assets/images/video/';        // shared with the video page (offer art)
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ METRICS (dark) ============ -->
    <section class="container metrics metrics--dark">
        <?php foreach ($cl_metrics as $i => $m): ?>
            <?php if ($i): ?><span class="metrics__divider"></span><?php endif; ?>
            <div class="metric">
                <span class="metric__icon"><img src="<?= iu($mc, $m[0]) ?>" width="24" height="24" alt=""></span>
                <div class="metric__body">
                    <div class="metric__value"><span data-count="<?= $m[1] ?>"><?= $m[1] ?></span><span>+</span></div>
                    <span class="metric__label"><?= $m[2] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
