@php
$ti = 'assets/images/3d/';   // page images
$tc = 'assets/icons/3d/';    // page icons
$vi = 'assets/images/video/'; // shared with video/magazine pages (offer + engagement images)
$vc = 'assets/icons/video/';  // shared icons
$tm = 'assets/models/3d/';    // 3D model files (.glb)
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
        <div class="td-benefits">
            <?php foreach ($td_benefit_cards as $c): ?>
                <article class="td-benefit <?= $c['raised'] ? 'is-raised' : '' ?>" style="--c: <?= $c['color'] ?? $c['ring'] ?>">
                    <span class="td-benefit__deco"></span>
                    <?php if ($T('card-numbers.1', '1') !== '0'): ?><span class="td-benefit__no" style="color: <?= $c['noc'] ?>66"><?= $c['no'] ?></span><?php endif; ?>
                    <span class="td-benefit__icon" style="background: <?= $c['ring'] ?>"><img src="<?= iu($tc, $c['icon']) ?>" alt=""></span>
                    <div class="td-benefit__copy">
                        <h3><?= $c['title'] ?></h3>
                        <p><?= $c['text'] ?></p>
                    </div>
                    <span class="td-benefit__rule" style="background: <?= $c['ring'] ?>"></span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
