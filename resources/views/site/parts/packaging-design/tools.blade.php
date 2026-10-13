@php
$ki = 'assets/images/packaging/';   // page images
$kc = 'assets/icons/packaging/';    // page icons
$mc = 'assets/icons/magazine/';     // shared tool glyphs
$vi = 'assets/images/video/';       // shared offer art
$vc = 'assets/icons/video/';        // shared icons
@endphp
    <!-- ============ TOOLS STRIP ============ -->
    <section class="pk-tools">
        <img class="pk-tools__photo" src="{{ $T('tools-strip.1') }}" alt="">
        <ul class="pk-tools__row">
            <?php foreach ($pk_tools as $i => $t): ?>
                <?php if ($i): ?><li class="pk-tools__divider"></li><?php endif; ?>
                <li class="seo-tool">
                    <?= tool_icon($mc, $t[0], $t[1]) ?>
                    <div><strong><?= $t[2] ?></strong><span><?= $t[3] ?></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
