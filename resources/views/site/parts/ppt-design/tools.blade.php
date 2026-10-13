@php
$ti = 'assets/images/ppt/';          // page images
$tc = 'assets/icons/ppt/';           // page icons
$pi = 'assets/images/prepress/';     // stats photo (same Figma image as the Pre-Press page)
$pc = 'assets/icons/prepress/';      // why-card icons (same card design as the Pre-Press page)
$mc = 'assets/icons/magazine/';      // shared tool glyphs
$vi = 'assets/images/video/';        // shared offer art
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ TOOLS STRIP ============ -->
    <section class="seo-tools ppt-tools">
        <img class="seo-tools__photo" src="{{ $T('tools-strip.1') }}" alt="">
        <div class="seo-tools__grid">
            <?php foreach ($ppt_tools as $col): ?>
                <div class="seo-tools__col">
                    <?php foreach ($col as $i => $t): ?>
                        <?php if ($i): ?><hr class="seo-tools__rule"><?php endif; ?>
                        <div class="seo-tool">
                            <?php if ($t[0] === 'mag'): ?>
                                <span class="mg-tool__icon" style="background: <?= $t[2] ?>"><img src="<?= iu($mc, $t[1]) ?>" alt=""></span>
                            <?php else: ?>
                                <img src="<?= iu(($t[0] === 'svg' ? $tc : $ti), $t[1]) ?>" width="72" height="72" alt="">
                            <?php endif; ?>
                            <div><strong><?= $t[3] ?></strong><span><?= $t[4] ?></span></div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($col) === 1): ?><hr class="seo-tools__rule"><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
