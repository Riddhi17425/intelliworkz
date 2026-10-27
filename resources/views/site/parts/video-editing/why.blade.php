@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!} </p>
                @if(trim(strip_tags($T('why-intelliworkz.3'))) !== '')<div><a href="{{ $T('why-intelliworkz.4') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.3') !!}</a></div>@endif
            </div>
        </div>
        <div class="pillars">
            <?php foreach ($ve_pillars as $p): ?>
                <article class="pillar" style="background: <?= $p['bg'] ?>">
                    <img class="pillar__img" src="<?= iu($vi, $p['img']) ?>" alt="" style="left: <?= $p['x'] ?>px">
                    <div class="pillar__content">
                        <span class="pillar__icon" style="border-color: <?= $p['ring'] ?>"><img src="<?= iu($vc, $p['icon']) ?>" alt=""></span>
                        <div class="pillar__text">
                            <h3><?= $p['title'] ?></h3>
                            <p><?= $p['text'] ?></p>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
