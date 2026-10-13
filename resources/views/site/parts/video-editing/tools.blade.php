@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
    <!-- ============ EDITING TOOLS ============ -->
    <section class="ve-tools">
        <img class="ve-tools__bg" src="{{ $T('editing-tools.1') }}" alt="">
        <ul class="ve-tools__row">
            <?php foreach ($ve_tools as $i => $t): ?>
                <?php if ($i): ?><li class="ve-tools__divider"></li><?php endif; ?>
                <li class="ve-tool">
                    <img src="<?= iu($vi, $t[0]) ?>" alt="">
                    <div><strong><?= $t[1] ?></strong><span><?= $t[2] ?></span></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
