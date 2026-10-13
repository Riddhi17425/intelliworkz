@php
$si = 'assets/images/seo/';    // page images
$sc = 'assets/icons/seo/';     // page icons
$vi = 'assets/images/video/';  // shared with video/magazine pages (offer image 4)
$vc = 'assets/icons/video/';   // shared icons
@endphp
    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!}</p>
                <p>{!! $T('why-intelliworkz.3') !!}</p>
                <p>{!! $T('why-intelliworkz.4') !!}</p>
                <div><a href="{{ $T('why-intelliworkz.6') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.5') !!}</a></div>
            </div>
        </div>
        <div class="seo-why">
            <?php foreach ($seo_why as $w): ?>
                <article class="seo-why__card" style="--c: <?= $w['c'] ?>">
                    <div class="seo-why__head">
                        <span class="seo-why__icon"><img src="<?= iu($sc, $w['icon']) ?>" alt=""></span>
                        <?php if ($T('card-numbers.1', '1') !== '0'): ?><span class="seo-why__no" style="color: rgba(255,255,255,<?= $w['noa'] ?>)"><?= $w['no'] ?></span><?php endif; ?>
                    </div>
                    <div class="seo-why__body">
                        <h3><?= $w['title'] ?></h3>
                        <p><?= $w['text'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
