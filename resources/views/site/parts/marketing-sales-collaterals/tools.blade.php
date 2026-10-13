@php
$ci = 'assets/images/collaterals/';  // page images
$cc = 'assets/icons/collaterals/';   // page icons
$mc = 'assets/icons/magazine/';      // shared with the magazine page (metric + tool icons)
$vi = 'assets/images/video/';        // shared with the video page (offer art)
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ TOOLS STRIP ============ -->
    <section class="mg-tools">
        <ul class="mg-tools__row">
            <?php foreach ($cl_tools as $i => $t): ?>
                <?php if ($i): ?><li class="mg-tools__divider"></li><?php endif; ?>
                <li class="mg-tool">
                    <?php if ($t[1]): ?>
                        <span class="mg-tool__icon" style="background: <?= $t[1] ?>"><img src="<?= iu($mc, $t[0]) ?>" alt=""></span>
                    <?php else: ?>
                        <img class="cl-tool__tile" src="<?= iu($ci, $t[0]) ?>" width="108" height="72" alt="">
                    <?php endif; ?>
                    <div><strong><?= $t[2] ?></strong><span><?= $t[3] ?></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
