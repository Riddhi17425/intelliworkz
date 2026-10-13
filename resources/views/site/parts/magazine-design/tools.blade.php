@php
$mi = 'assets/images/magazine/';   // page images
$mc = 'assets/icons/magazine/';    // page icons
$vi = 'assets/images/video/';      // images shared with the video page (offer cards)
$vc = 'assets/icons/video/';       // icons shared with the video page
@endphp
    <!-- ============ TOOLS STRIP ============ -->
    <section class="mg-tools">
        <ul class="mg-tools__row">
            <?php foreach ($mg_tools as $i => $t): ?>
                <?php if ($i): ?><li class="mg-tools__divider"></li><?php endif; ?>
                <li class="mg-tool">
                    <span class="mg-tool__icon" style="background: <?= $t[1] ?>"><img src="<?= iu($mc, $t[0]) ?>" alt=""></span>
                    <div><strong><?= $t[2] ?></strong><span><?= $t[3] ?></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
