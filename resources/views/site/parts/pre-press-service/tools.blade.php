@php
$pi = 'assets/images/prepress/';      // page images
$pc = 'assets/icons/prepress/';       // page icons
$ci = 'assets/images/collaterals/';   // image-strip tiles (same two images as the Collaterals page)
$si = 'assets/images/seo/';           // tools-strip photo (same Figma image as the SEO page)
$mc = 'assets/icons/magazine/';       // shared tool glyphs
$vi = 'assets/images/video/';         // shared offer art
$vc = 'assets/icons/video/';          // shared icons
@endphp
    <!-- ============ TOOLS STRIP ============ -->
    <section class="seo-tools pp-tools">
        <img class="seo-tools__photo" src="{{ $T('tools-strip.1') }}" alt="">
        <div class="seo-tools__grid">
            <?php foreach ($pp_tools as $col): ?>
                <div class="seo-tools__col">
                    <?php foreach ($col as $i => $t): ?>
                        <?php if ($i): ?><hr class="seo-tools__rule"><?php endif; ?>
                        <div class="seo-tool">
                            <?php if ($t[0] === 'mag'): ?>
                                <span class="mg-tool__icon" style="background: <?= $t[2] ?>"><img src="<?= iu($mc, $t[1]) ?>" alt=""></span>
                            <?php else: ?>
                                <img src="<?= iu(($t[0] === 'svg' ? $pc : $pi), $t[1]) ?>" width="72" height="72" alt="">
                            <?php endif; ?>
                            <div><strong><?= $t[3] ?></strong><span><?= $t[4] ?></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
