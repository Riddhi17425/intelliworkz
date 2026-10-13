@php
$mi = 'assets/images/magazine/';   // page images
$mc = 'assets/icons/magazine/';    // page icons
$vi = 'assets/images/video/';      // images shared with the video page (offer cards)
$vc = 'assets/icons/video/';       // icons shared with the video page
@endphp
    <!-- ============ OUR EXAMPLES ============ -->
    <section class="mg-examples" data-examples data-autoplay="2600">
        <h2 class="h2 h2--center">{!! $T('our-examples.1') !!}</h2>
        <div class="mg-viewport">
            <div class="mg-strip" data-examples-track>
                <?php
                /* Figma shows a fixed 8-tile strip; duplicate it so the autoplay loop never runs out of tiles to slide in. */
                foreach (array_merge($mg_examples, $mg_examples) as $e):
                ?>
                    <?php if ($e['type'] === 'img'): ?>
                        <div class="mg-tile"><img src="<?= iu($mi, $e['img']) ?>" alt="Magazine design example"></div>
                    <?php elseif ($e['type'] === 'frame'): ?>
                        <div class="mg-tile mg-tile--frame"><img src="<?= iu($mi, $e['img']) ?>" alt="Magazine design example"></div>
                    <?php else: ?>
                        <div class="mg-tile mg-tile--blank <?= $e['type'] === 'blank-border' ? 'has-border' : '' ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
