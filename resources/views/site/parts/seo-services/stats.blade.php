@php
$si = 'assets/images/seo/';    // page images
$sc = 'assets/icons/seo/';     // page icons
$vi = 'assets/images/video/';  // shared with video/magazine pages (offer image 4)
$vc = 'assets/icons/video/';   // shared icons
@endphp
    <!-- ============ METRICS (dark) ============ -->
    <section class="container metrics metrics--dark seo-metrics">
        <?php foreach ($seo_metrics as $i => $m): ?>
            <?php if ($i): ?><span class="metrics__divider"></span><?php endif; ?>
            <div class="metric">
                <span class="metric__icon"><img src="<?= iu($sc, $m[0]) ?>" width="24" height="24" alt=""></span>
                <div class="metric__body">
                    <div class="metric__value"><span data-count="<?= $m[1] ?>"><?= $m[1] ?></span><span>+</span></div>
                    <span class="metric__label"><?= $m[2] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
