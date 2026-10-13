@php
$ti = 'assets/images/3d/';   // page images
$tc = 'assets/icons/3d/';    // page icons
$vi = 'assets/images/video/'; // shared with video/magazine pages (offer + engagement images)
$vc = 'assets/icons/video/';  // shared icons
$tm = 'assets/models/3d/';    // 3D model files (.glb)
@endphp
    <!-- ============ TOOLS STRIP ============ -->
    <section class="ve-tools td-tools">
        <img class="ve-tools__bg" src="{{ $T('tools-strip.1') }}" alt="">
        <ul class="ve-tools__row">
            <?php foreach ($td_tools as $i => $t): ?>
                <?php if ($i): ?><li class="ve-tools__divider"></li><?php endif; ?>
                <?php $is_ps = $t[0] === 'tool-photoshop.svg'; ?>
                <li class="ve-tool <?= $t[0] === 'tool-3dsmax.png' || $is_ps ? '' : 've-tool--mark' ?> <?= $t[0] === 'tool-blender.svg' ? 'td-tool--lg' : '' ?>">
                    <?php if ($is_ps): ?>
                        <span class="td-tool__tile"><img src="<?= iu($tc, $t[0]) ?>" alt=""></span>
                    <?php else: ?>
                        <img src="<?= iu((str_ends_with($t[0], '.svg') ? $tc : $ti), $t[0]) ?>" alt="">
                    <?php endif; ?>
                    <div><strong><?= $t[1] ?></strong><span><?= $t[2] ?></span></div>
                </li>
            <?php endforeach; ?>
            <li class="ve-tools__divider"></li>
            <li class="ve-tool td-tool-formats">
                <img class="td-tool-formats__img" src="{{ $T('tools-strip.2') }}" alt="STP, FBX, STL and OBJ file icons">
                <div><strong><?= $td_tool_formats_label[0] ?></strong><span><?= $td_tool_formats_label[1] ?></span></div>
            </li>
            <li class="ve-tools__divider"></li> <!-- Figma ends the row with a trailing divider -->
        </ul>
    </section>
