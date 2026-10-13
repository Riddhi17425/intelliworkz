@extends('site.layout')

@section('content')
@php
$industry_text = $T('shared-copy.1');
@endphp
<main class="page">

    <!-- ============ HERO ============ -->
    <section class="hero">
        <img class="hero__bg" src="{{ $T('hero.1') }}" alt="">

        @include('site.partials.site-header')

        <div class="container hero__content">
            <h1 class="h1">{!! $T('hero.4') !!}</h1>
            <p class="hero__text">{!! $T('hero.5') !!}</p>
            <div class="hero__actions">
                <?php btn($T('hero.2'), '', null, 'contact-us.php'); ?>
                <?php btn($T('hero.3'), 'btn--stroke'); ?>
            </div>
        </div>

        <p class="hero__caption">{!! $T('hero.6') !!}</p>
    </section>

    <!-- ============ METRICS ============ -->
    <section class="container metrics">
        <?php foreach ($metrics as $i => $m): ?>
            <?php if ($i): ?><span class="metrics__divider"></span><?php endif; ?>
            <div class="metric">
                <span class="metric__icon"><img src="<?= iu('assets/icons/', $m['icon']) ?>" width="24" height="24" alt=""></span>
                <div class="metric__body">
                    <div class="metric__value"><span data-count="<?= $m['value'] ?>"><?= $m['value'] ?></span><span>+</span></div>
                    <span class="metric__label"><?= $m['label'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- ============ DOMAIN EXPERTISE ============ -->
    <section class="expertise">
        <div class="expertise__media"></div>
        <div class="expertise__content">
            <h2 class="h2">{!! $T('domain-expertise.2') !!}</h2>
            <div class="expertise__text">
                <p>{!! $T('domain-expertise.3') !!}</p>
                <p>{!! $T('domain-expertise.4') !!}</p>
                <p>{!! $T('domain-expertise.5') !!}</p>
                <p>{!! $T('domain-expertise.6') !!}</p>
            </div>
            <div><?php btn($T('domain-expertise.1'), '', null, 'about-us.php'); ?></div>
        </div>
    </section>

    <!-- ============ SERVICES ============ -->
    <section class="container services">
        <div class="split-head">
            <h2 class="h2">{!! $T('services.2') !!}</h2>
            <div class="split-head__side">
                <p class="lead">{!! $T('services.3') !!}</p>
                <div><?php btn($T('services.1')); ?></div>
            </div>
        </div>

        <div class="domain-grid">
            <?php foreach ($domains as $d): ?>
                <article class="domain-card" style="--c: <?= $d['color'] ?>">
                    <h3 class="domain-card__title"><?= $d['title'] ?></h3>
                    <div class="domain-card__body">
                        <div class="domain-card__main">
                            <p class="lead lead--left"><?= $d['intro'] ?></p>
                            <ul class="domain-card__list">
                                <?php foreach ($d['items'] as $item): ?>
                                    <li><a href="#"><?= $item ?></a><img src="assets/icons/arrow-red.svg" width="10" height="10" alt=""></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <a href="#" class="pill"><?= $d['cta'] ?><img src="assets/icons/arrow-circle.svg" width="14" height="14" alt=""></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ CTA BAND ============ -->
    <section class="cta-band">
        <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
        <h2 class="cta-title">{!! $T('cta-band.2') !!}</h2>
        <?php btn($T('cta-band.1'), 'btn--cta', 'arrow-cta.svg', 'contact-us.php'); ?>
    </section>

    <!-- ============ OUTSOURCING ============ -->
    <section class="container outsourcing">
        <div class="split-head split-head--center">
            <h2 class="h2">{!! $T('outsourcing.2') !!}</h2>
            <div class="split-head__side">
                <p class="lead">{!! $T('outsourcing.3') !!}</p>
                <p class="lead">{!! $T('outsourcing.4') !!}</p>
            </div>
        </div>

        <div class="steps">
            <div class="steps__line"></div>
            <img class="steps__red-line" src="assets/icons/steps-red-line.svg" width="1526" height="6" alt="">
            <h3 class="steps__badge">{!! $T('outsourcing.5') !!}</h3>
            <div class="steps__row">
                <?php foreach ($outsourcing_steps as $s): ?>
                    <div class="step">
                        <img src="<?= iu('assets/icons/', $s['img']) ?>" width="160" height="161" alt="">
                        <div class="step__text">
                            <h4><?= $s['title'] ?></h4>
                            <p><?= $s['text'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="outsource-grid">
            <?php foreach ($outsourcing_cards as $c): ?>
                <article class="outsource-card <?= $c['class'] ?>">
                    <div class="outsource-card__head">
                        <span class="outsource-card__no"><?= $c['no'] ?></span>
                        <h3 class="h-card"><?= $c['title'] ?></h3>
                    </div>
                    <?php if (!empty($c['lead'])): ?>
                        <p class="small"><?= $c['lead'] ?></p>
                        <ul class="dot-list">
                            <?php foreach ($c['items'] as $item): ?><li><?= $item ?></li><?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="small outsource-card__text"><?= $c['text'] ?></p>
                    <?php endif; ?>
                    <img class="outsource-card__img" src="<?= iu('assets/images/', $c['img']) ?>" alt="">
                </article>
            <?php endforeach; ?>
        </div>

        <div class="whitelabel">
            <div class="whitelabel__left">
                <h3 class="whitelabel__title">{!! $T('outsourcing.6') !!}</h3>
                <div class="whitelabel__sub">
                    <h4 class="h-card">{!! $T('outsourcing.7') !!}</h4>
                    <div class="whitelabel__cols">
                        <ul class="dot-list dot-list--plain">
                            <li>{!! $T('outsourcing.8') !!}</li>
                            <li>{!! $T('outsourcing.9') !!}</li>
                        </ul>
                        <ul class="dot-list dot-list--plain">
                            <li>{!! $T('outsourcing.10') !!}</li>
                            <li>{!! $T('outsourcing.11') !!}</li>
                        </ul>
                    </div>
                </div>
            </div>
            <span class="v-line"></span>
            <div class="whitelabel__right">
                <p class="small">{!! $T('outsourcing.12') !!}</p>
                <div><?php btn($T('outsourcing.1'), 'btn--cta'); ?></div>
            </div>
        </div>
    </section>

    <!-- ============ INDUSTRIES ============ -->
    <section class="container industries">
        <?php section_head($T('industries.1'), $T('industries.2'), 'section-head--wide'); ?>

        <div class="slider" data-slider>
            <div class="slider__track" data-slider-track>
                <?php foreach ($industries as $ind): ?>
                    <article class="industry <?= empty($ind['img']) ? 'industry--plain' : '' ?> <?= (strlen($ind['title']) > 20) ? 'industry--two-line' : '' ?>" tabindex="0">
                        <?php if (!empty($ind['img'])): ?><img src="<?= iu('assets/images/', $ind['img']) ?>" alt="<?= $ind['title'] ?>"><?php endif; ?>
                        <h3 class="industry__title <?= !empty($ind['font']) ? 'is-alt' : '' ?>"><?= $ind['title'] ?></h3>
                        <p class="industry__text"><?= $ind['text'] ?? $industry_text ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="dots" data-slider-dots>
                <?php for ($i = 0; $i < 4; $i++): ?><button type="button" class="dots__dot" aria-label="Go to slide <?= $i + 1 ?>"></button><?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="why">
        <div class="why__inner">
            <h2 class="h2 h2--center h2--white">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="why__grid">
                <?php foreach ($reasons as $i => $r): ?>
                    <article class="reason">
                        <div class="reason__top"><span class="reason__no"><?= sprintf('%02d', $i + 1) ?></span><span class="reason__line"></span></div>
                        <div class="reason__body">
                            <h3 class="h-card h-card--white"><?= $r[0] ?></h3>
                            <p><?= $r[1] ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ PRODUCTS ============ -->
    <section class="products">
        <?php section_head($T('products.2'), $T('products.3'), 'section-head--products'); ?>

        <div class="products__stage" data-products>
            <?php foreach ($products_data as $i => $pr): ?>
                <div class="product <?= $i === 0 ? 'is-active' : '' ?>" id="product-<?= $i ?>" role="tabpanel" aria-hidden="<?= $i === 0 ? 'false' : 'true' ?>">
                    <img class="products__img" src="<?= iu('assets/images/', $pr['img']) ?>" alt="<?= $pr['name'] ?>">
                    <div class="products__info">
                        <div class="products__intro">
                            <div>
                                <h3 class="products__name"><?= $pr['name'] ?></h3>
                                <p class="products__tag"><?= $pr['tag'] ?></p>
                            </div>
                            <p><?= $pr['text'] ?></p>
                            <div><?php btn($T('products.1') . ' ' . $pr['name'], 'btn--cta'); ?></div>
                        </div>
                        <div class="products__features">
                            <h4 class="h-card">{!! $T('products.4') !!}</h4>
                            <div class="feature-grid">
                                <?php foreach ($pr['features'] as $fi => $f): $fid = "feature-$i-$fi"; ?>
                                    <div class="feature-item">
                                        <button type="button" class="feature" aria-expanded="false" aria-controls="<?= $fid ?>">
                                            <span><?= $f[0] ?></span>
                                            <span class="feature__icon"><img src="assets/icons/icon-plus-box.svg" width="20" height="20" alt=""></span>
                                        </button>
                                        <div class="feature-panel" id="<?= $fid ?>" role="region">
                                            <div class="feature-panel__inner">
                                                <p><?= $f[1] ?></p>
                                                <a class="feature-panel__link" href="contact-us.php">Enquire about <?= $pr['name'] ?> &rarr;</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <ul class="products__tabs" role="tablist">
                <?php foreach ($products_data as $i => $pr): ?>
                    <li><button type="button" class="tab <?= $i === 0 ? 'is-active' : '' ?>" role="tab" aria-controls="product-<?= $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"><span><?= $pr['name'] ?></span><span><?= sprintf('%02d', $i + 1) ?></span></button></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ============ PROCESS ============ -->
    <section class="process">
        <span class="process__rule process__rule--1"></span>
        <span class="process__rule process__rule--2"></span>
        <?php section_head($T('process.1'), $T('process.2'), 'process__head'); ?>

        <?php foreach ([1 => $process_row_1, 2 => $process_row_2] as $rowNo => $row): ?>
            <div class="process__row process__row--<?= $rowNo ?>">
                <?php foreach ($row as $p): ?>
                    <article class="process-card" style="--c: <?= $p['color'] ?>">
                        <div class="process-card__bg" style="background: <?= $p['fade'] ?>, url('<?= iu('assets/images/', $p['img']) ?>') center / cover no-repeat"></div>
                        <span class="process-card__flag"><?= $p['no'] ?></span>
                        <div class="process-card__body">
                            <div class="process-card__title">
                                <span class="process-card__icon" style="background: <?= $p['tint'] ?>"><img src="<?= iu('assets/icons/', $p['icon']) ?>" width="21" height="21" alt=""></span>
                                <h3 class="h-card h-card--dark"><?= $p['title'] ?></h3>
                            </div>
                            <p><?= $p['text'] ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <img class="process__flow process__flow--loop" src="assets/icons/connector-loop.svg" alt="">
        <img class="process__flow process__flow--a" src="assets/icons/flow-arrow-a.svg" alt="">
        <img class="process__flow process__flow--b" src="assets/icons/flow-arrow-b.svg" alt="">
        <img class="process__flow process__flow--c" src="assets/icons/flow-arrow-c.svg" alt="">
        <img class="process__flow process__flow--d" src="assets/icons/flow-arrow-d.svg" alt="">
        <img class="process__flow process__flow--e" src="assets/icons/flow-arrow-e.svg" alt="">
    </section>

    @php

@endphp
    @include('site.sections.showcase')

    @include('site.sections.engagement')

    <!-- ============ INSIGHTS ============ -->
    <section class="container insights">
        <h2 class="h2 h2--center">{!! $T('insights.2') !!}</h2>
        <div class="insights__row">
            <?php foreach ($blog_posts as $b): ?>
                <article class="post">
                    <img class="post__img" src="<?= iu('assets/images/', $b['img']) ?>" alt="">
                    <div class="post__body">
                        <span class="small small--dark"><?= $b['date'] ?></span>
                        <div class="post__text">
                            <h3 class="post__title"><?= $b['title'] ?></h3>
                            <div><?php btn($T('insights.1'), 'btn--cta'); ?></div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

</main>


@endsection
