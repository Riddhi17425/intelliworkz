@php
$ki = 'assets/images/packaging/';   // page images
$kc = 'assets/icons/packaging/';    // page icons
$mc = 'assets/icons/magazine/';     // shared tool glyphs
$vi = 'assets/images/video/';       // shared offer art
$vc = 'assets/icons/video/';        // shared icons
@endphp
    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!}</p>
                <p>{!! $T('why-intelliworkz.3') !!}</p>
                <p>{!! $T('why-intelliworkz.4') !!}</p>
                @if(trim(strip_tags($T('why-intelliworkz.5'))) !== '')<div><a href="{{ $T('why-intelliworkz.6') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.5') !!}</a></div>@endif
            </div>
        </div>
        <div class="pk-why">
            <?php foreach ($pk_why as $w): ?>
                <article class="pk-why__card" style="--c: <?= $w['c'] ?>">
                    <img class="pk-why__bg" src="<?= iu($ki, $w['bg']) ?>" alt="">
                    <?php if ($T('card-numbers.1', '1') !== '0'): ?><span class="pk-why__no"><?= $w['no'] ?></span><?php endif; ?>
                    <div class="pk-why__content">
                        <span class="pk-why__icon"><img src="<?= iu($kc, $w['icon']) ?>" alt=""></span>
                        <div class="pk-why__text">
                            <h3><?= $w['title'] ?></h3>
                            <p><?= $w['text'] ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
