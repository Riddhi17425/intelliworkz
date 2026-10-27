@php
$ci = 'assets/images/collaterals/';  // page images
$cc = 'assets/icons/collaterals/';   // page icons
$mc = 'assets/icons/magazine/';      // shared with the magazine page (metric + tool icons)
$vi = 'assets/images/video/';        // shared with the video page (offer art)
$vc = 'assets/icons/video/';         // shared icons
@endphp
    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!}</p>
                <p>{!! $T('why-intelliworkz.3') !!}</p>
                @if(trim(strip_tags($T('why-intelliworkz.4'))) !== '')<div><a href="{{ $T('why-intelliworkz.5') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.4') !!}</a></div>@endif
            </div>
        </div>
        <div class="cl-why">
            <?php foreach ($cl_why as $w): ?>
                <article class="cl-why__card" style="--c: <?= $w['c'] ?>">
                    <img class="cl-why__bg" src="<?= iu($ci, $w['bg']) ?>" alt="">
                    <div class="cl-why__tabbar">
                        <img class="cl-why__tab" src="<?= iu($cc, $w['tab']) ?>" alt="">
                        <?php if ($T('card-numbers.1', '1') !== '0'): ?><span class="cl-why__no" style="color: <?= $w['num'] ?>"><?= $w['no'] ?></span><?php endif; ?>
                    </div>
                    <div class="cl-why__content">
                        <span class="cl-why__icon"><img src="<?= iu($cc, $w['icon']) ?>" alt=""></span>
                        <div class="cl-why__text">
                            <h3><?= $w['title'] ?></h3>
                            <p><?= $w['text'] ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
