@php
$mi = 'assets/images/magazine/';   // page images
$mc = 'assets/icons/magazine/';    // page icons
$vi = 'assets/images/video/';      // images shared with the video page (offer cards)
$vc = 'assets/icons/video/';       // icons shared with the video page
@endphp
    <!-- ============ METRICS (dark) ============ -->
    <section class="container metrics metrics--dark">
        <?php foreach ($mg_metrics as $i => $m): ?>
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
