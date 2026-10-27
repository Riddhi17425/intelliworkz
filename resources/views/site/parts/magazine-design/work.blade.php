@php
$mi = 'assets/images/magazine/';   // page images
$mc = 'assets/icons/magazine/';    // page icons
$vi = 'assets/images/video/';      // images shared with the video page (offer cards)
$vc = 'assets/icons/video/';       // icons shared with the video page
// same section design as "Selected Pre-Press Services Work": title on the left, round arrows, a row of work pictures that slides
$pics = array_values(array_filter($mg_examples, fn ($e) => in_array($e['type'], ['img', 'frame'], true) && !empty($e['img'])));
@endphp
    <!-- ============ OUR EXAMPLES (Pre-Press style) ============ -->
    <section class="cl-pf" data-examples data-autoplay="2600">
        <div class="cl-pf__head">
            <div class="cl-pf__top">
                <div class="cl-pf__title">
                    <h2 class="h2">{!! $T('our-examples.1') !!}</h2>
                </div>
                <div class="round-arrows">
                    <button type="button" class="round-arrow" aria-label="Previous" data-examples-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                    <button type="button" class="round-arrow" aria-label="Next" data-examples-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
                </div>
            </div>
        </div>
        <div class="mg-viewport">
            <div class="mg-strip" data-examples-track>
                <?php foreach (array_merge($pics, $pics) as $e): ?>
                    <div class="cl-tile"><img src="<?= iu($mi, $e['img']) ?>" alt="Magazine design example"></div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
