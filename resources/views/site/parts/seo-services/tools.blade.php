@php
$si = 'assets/images/seo/';    // page images
$sc = 'assets/icons/seo/';     // page icons
$vi = 'assets/images/video/';  // shared with video/magazine pages (offer image 4)
$vc = 'assets/icons/video/';   // shared icons
@endphp
    <!-- ============ TOOLS STRIP ============ -->
    <section class="seo-tools">
        <img class="seo-tools__photo" src="{{ $T('tools-strip.1') }}" alt="">
        <div class="seo-tools__grid">
            <?php foreach ($seo_tools as $col): ?>
                <div class="seo-tools__col">
                    <?php foreach ($col as $i => $t): ?>
                        <?php if ($i): ?><hr class="seo-tools__rule"><?php endif; ?>
                        <div class="seo-tool">
                            <img src="<?= iu((str_ends_with($t[0], '.svg') ? $sc : $si), $t[0]) ?>" width="72" height="72" alt="">
                            <div><strong><?= $t[1] ?></strong><span><?= $t[2] ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
