@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
    <!-- ============ PORTFOLIO ============ -->
    <section class="container ve-portfolio">
        <div class="ve-portfolio__head">
            <div class="ve-portfolio__top">
                <div class="ve-portfolio__title">
                    <h2 class="h2">{!! $T('portfolio.3') !!}</h2>
                    <p class="lead lead--left">{!! $T('portfolio.4') !!}</p>
                </div>
                <div class="round-arrows">
                    <button type="button" class="round-arrow" aria-label="Previous" data-pf-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
                    <button type="button" class="round-arrow" aria-label="Next" data-pf-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
                </div>
            </div>
            <?php if ($T('filter-tabs.1', '1') !== '0' && !empty($ve_filters)): ?>
            <div class="filter-tabs" data-filter-tabs>
                <?php foreach ($ve_filters as $key => $label): ?>
                    <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="pf-cards" data-pf-track>
            <?php foreach ($ve_cards as $c): ?>
                <article class="pf-card" data-cats="<?= $c['cats'] ?>" data-video="<?= $c['video'] ?>" data-title="<?= $c['title'] ?>">
                    <div class="pf-media" data-play>
                        <img class="pf-media__base" src="{{ $T('portfolio.1') }}" alt="">
                        <?php if ($c['overlay']): ?><img class="pf-media__overlay" src="<?= iu($vi, $c['overlay']) ?>" alt=""><?php endif; ?>
                        <span class="pf-media__time">02:45</span>
                        <button type="button" class="pf-media__play" aria-label="Play <?= $c['title'] ?>"><img src="{{ $T('portfolio.2') }}" width="80" height="80" alt=""></button>
                    </div>
                    <div class="pf-meta">
                        <div class="pf-meta__row">
                            <h3><?= $c['title'] ?></h3>
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M3 11L11 3M4 3h7v7" stroke="#111827" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <p><?= $c['meta'] ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
