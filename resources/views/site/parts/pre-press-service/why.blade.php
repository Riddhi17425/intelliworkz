@php
$pi = 'assets/images/prepress/';      // page images
$pc = 'assets/icons/prepress/';       // page icons
$ci = 'assets/images/collaterals/';   // image-strip tiles (same two images as the Collaterals page)
$si = 'assets/images/seo/';           // tools-strip photo (same Figma image as the SEO page)
$mc = 'assets/icons/magazine/';       // shared tool glyphs
$vi = 'assets/images/video/';         // shared offer art
$vc = 'assets/icons/video/';          // shared icons
@endphp
    <!-- ============ WHAT YOU GET ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2 pp-why__title">{!! $T('what-you-get.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('what-you-get.2') !!}</p>
                <p>{!! $T('what-you-get.3') !!}</p>
                <p>{!! $T('what-you-get.4') !!}</p>
                <div><a href="{{ $T('what-you-get.6') ?: '#' }}" class="btn btn--enquire">{!! $T('what-you-get.5') !!}</a></div>
            </div>
        </div>
        <div class="pp-why">
            <?php foreach ($pp_why as $w): ?>
                <article class="pp-why__card" style="--c: <?= $w['c'] ?>; --t: <?= $w['tint'] ?>">
                    <span class="pp-why__bar"></span>
                    <div class="pp-why__head">
                        <span class="pp-why__icon"><img src="<?= iu($pc, $w['icon']) ?>" alt=""></span>
                        <span class="pp-why__no"><?= $w['no'] ?></span>
                    </div>
                    <div class="pp-why__text">
                        <h3><?= $w['title'] ?></h3>
                        <p><?= $w['text'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
