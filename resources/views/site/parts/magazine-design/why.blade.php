@php
$mi = 'assets/images/magazine/';   // page images
$mc = 'assets/icons/magazine/';    // page icons
$vi = 'assets/images/video/';      // images shared with the video page (offer cards)
$vc = 'assets/icons/video/';       // icons shared with the video page
@endphp
    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!} </p>
                <div><a href="{{ $T('why-intelliworkz.4') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.3') !!}</a></div>
            </div>
        </div>
        <div class="mg-why">
            <?php foreach ($mg_why as $w): ?>
                <article class="mg-why__card <?= $w['shadow'] ? 'has-shadow' : '' ?>" style="--c: <?= $w['c'] ?>">
                    <div class="mg-why__head">
                        <span class="mg-why__icon"><img src="<?= iu($mc, $w['icon']) ?>" alt=""></span>
                        <?php if ($T('card-numbers.1', '1') !== '0'): ?><span class="mg-why__no" style="color: rgba(255,255,255,<?= $w['noa'] ?>)"><?= $w['no'] ?></span><?php endif; ?>
                    </div>
                    <div class="mg-why__body <?= $w['border'] ? 'has-border' : '' ?>">
                        <h3><?= $w['title'] ?></h3>
                        <p><?= $w['text'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
