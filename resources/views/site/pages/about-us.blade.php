@extends('site.layout')

@section('content')
@php
$ac = 'assets/icons/';
$ai = 'assets/images/';

/* Approach diagram: six capability circles around the business (viewBox 820x640) */
$cx = 410; $cy = 320; $orbit = 218; $core = 92;
$nodes = [];
foreach ($ab_nodes as $i => $nd) {
    // top, upper right, lower right, bottom, lower left, upper left
    [$x, $y] = ab_pt($cx, $cy, $orbit, -90 + $i * 60);
    $nodes[] = ['x' => $x, 'y' => $y, 'label' => $nd['label'], 'icon' => $nd['icon'] ?? 'icon-briefcase.svg'];
}

@endphp
<main class="page ab">

    <!-- ============ 01 HERO ============ -->
    <section class="ab-hero">
        <img class="ab-hero__bg" src="<?= iu($ai, $T('hero.1')) ?>" alt="">
        <div class="ab-hero__top">@include('site.partials.site-header')</div>

        <div class="ab-wrap ab-hero__grid">
            <div class="ab-hero__copy">
                <p class="ab-crumb">{!! $T('hero.2') !!}</p>
                <h1 class="ab-hero__title" data-reveal>{!! $T('hero.3') !!}</h1>
                <p class="ab-hero__lead" data-reveal style="--d:1">{!! $T('hero.4') !!}</p>
                <div class="ab-hero__btns" data-reveal style="--d:2">
                    <?php btn($T('hero.5'), 'btn--cta', 'arrow-link.svg', $T('hero.6', '#')); ?>
                    <?php btn($T('hero.7'), 'btn--cta btn--ghost', 'arrow-link.svg', $T('hero.8', '#')); ?>
                </div>
            </div>
            <p class="ab-hero__words" aria-hidden="true">{!! $T('hero.9') !!}</p>
        </div>
    </section>

    <!-- ============ 02 WHO WE ARE ============ -->
    <section class="ab-section ab-who">
        <div class="ab-wrap ab-split">
            <div class="ab-copy">
                <p class="ab-eyebrow" data-reveal>{!! $T('who.1') !!}</p>
                <h2 class="ab-title" data-reveal style="--d:1">{!! $T('who.2') !!}</h2>
                <p class="ab-p" data-reveal style="--d:2">{!! $T('who.3') !!}</p>
                <p class="ab-p" data-reveal style="--d:3">{!! $T('who.4') !!}</p>
                <div data-reveal style="--d:4"><?php btn($T('who.5'), 'btn--cta', 'arrow-link.svg', $T('who.6', '#')); ?></div>
            </div>
            <div class="ab-who__pics" data-reveal style="--d:2">
                <img class="ab-pic ab-who__big" src="<?= iu($ai, $T('who.7')) ?>" alt="The Intelliworkz team at work" loading="lazy">
                <img class="ab-pic ab-who__small" src="<?= iu($ai, $T('who.8')) ?>" alt="" loading="lazy">
                <div class="ab-statcard">
                    <span class="metric__icon"><img src="<?= $ac ?>icon-globe.svg" width="24" height="24" alt=""></span>
                    <strong>{!! $T('who.9') !!}</strong>
                    <span>{!! $T('who.10') !!}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ 03 NUMBERS ============ -->
    <section class="ab-stats">
        <div class="ab-wrap">
            <dl class="ab-stats__row">
                <?php foreach ($ab_metrics as $m): ?>
                    <div class="ab-stat" data-reveal>
                        <span class="metric__icon"><img src="<?= iu($ac, $m['icon']) ?>" width="24" height="24" alt=""></span>
                        <dt><span data-count="<?= $m['value'] ?>"><?= $m['value'] ?></span> +</dt>
                        <dd><?= $m['label'] ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </section>

    <!-- ============ 04 OUR PURPOSE ============ -->
    <section class="ab-section ab-purpose">
        <div class="ab-wrap ab-split ab-split--rev">
            <div class="ab-purpose__pics" data-reveal>
                <span class="ab-zoom ab-purpose__big"><img class="ab-pic" src="<?= iu($ai, $T('purpose.4')) ?>" alt="" loading="lazy"></span>
                <div class="ab-purpose__idea" aria-hidden="true">
                    <svg viewBox="0 0 48 48" width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17 33c0-2-1-3-3-5a11 11 0 1 1 20 0c-2 2-3 3-3 5v2H17z"/><path d="M19 40h10M21 44h6"/><path d="M24 3v4M5 24H1M47 24h-4M9 9l3 3M39 9l-3 3"/></svg>
                </div>
                <span class="ab-zoom ab-purpose__wide"><img class="ab-pic" src="<?= iu($ai, $T('purpose.5')) ?>" alt="" loading="lazy"></span>
            </div>
            <div class="ab-copy">
                <p class="ab-eyebrow" data-reveal>{!! $T('purpose.1') !!}</p>
                <h2 class="ab-title" data-reveal style="--d:1">{!! $T('purpose.2') !!}</h2>
                <p class="ab-p" data-reveal style="--d:2">{!! $T('purpose.3') !!}</p>
                <ul class="ab-points">
                    <?php foreach ($ab_purpose as $i => $pt): ?>
                        <li data-reveal style="--d:<?= $i + 2 ?>">
                            <span class="ab-ico"><img src="<?= iu($ac, $pt['icon']) ?>" width="26" height="26" alt=""></span>
                            <strong><?= $pt['title'] ?></strong>
                            <span><?= $pt['text'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <!-- ============ 05 OUR APPROACH ============ -->
    <section class="ab-section ab-approach">
        <div class="ab-wrap ab-split">
            <div class="ab-copy">
                <p class="ab-eyebrow" data-reveal>{!! $T('approach.1') !!}</p>
                <h2 class="ab-title ab-title--light" data-reveal style="--d:1">{!! $T('approach.2') !!}</h2>
                <p class="ab-p ab-p--light" data-reveal style="--d:2">{!! $T('approach.3') !!}</p>
                <div data-reveal style="--d:3"><?php btn($T('approach.4'), 'btn--cta', 'arrow-link.svg', $T('approach.5', '#')); ?></div>
            </div>
            <div class="ab-orbit" data-reveal style="--d:2">
                <svg viewBox="0 0 820 640" role="img" aria-label="Your business growth at the centre, connected to <?= e(implode(', ', array_column($nodes, 'label'))) ?>">
                    <circle class="ab-orbit__ring" cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $orbit ?>"/>
                    <?php foreach ($nodes as $n): ?>
                        <line class="ab-orbit__link" x1="<?= $cx ?>" y1="<?= $cy ?>" x2="<?= $n['x'] ?>" y2="<?= $n['y'] ?>"/>
                    <?php endforeach; ?>
                    <circle class="ab-orbit__core" cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $core ?>"/>
                    <?php foreach (explode('<br>', (string) $T('approach.6')) as $li => $line): ?>
                        <text class="ab-orbit__center" x="<?= $cx ?>" y="<?= $cy - 12 + $li * 24 ?>" text-anchor="middle"><?= strtoupper(trim(strip_tags($line))) ?></text>
                    <?php endforeach; ?>
                    <?php foreach ($nodes as $n): ?>
                        <g class="ab-orbit__node">
                            <rect x="<?= $n['x'] - 96 ?>" y="<?= $n['y'] - 29 ?>" width="192" height="58" rx="29"/>
                            <circle cx="<?= $n['x'] - 62 ?>" cy="<?= $n['y'] ?>" r="20"/>
                            <image href="<?= $ac . $n['icon'] ?>" x="<?= $n['x'] - 73 ?>" y="<?= $n['y'] - 11 ?>" width="22" height="22"/>
                            <text x="<?= $n['x'] - 32 ?>" y="<?= $n['y'] + 6 ?>"><?= $n['label'] ?></text>
                        </g>
                    <?php endforeach; ?>
                </svg>
            </div>
        </div>
    </section>

    <!-- ============ 06 OUR TEAM ============ -->
    <section class="ab-section ab-team" id="team">
        <div class="ab-wrap ab-split">
            <div class="ab-copy">
                <p class="ab-eyebrow" data-reveal>{!! $T('team.1') !!}</p>
                <h2 class="ab-title" data-reveal style="--d:1">{!! $T('team.2') !!}</h2>
                <p class="ab-p" data-reveal style="--d:2">{!! $T('team.3') !!}</p>
                <div data-reveal style="--d:3"><?php btn($T('team.4'), 'btn--cta', 'arrow-link.svg', $T('team.5', '#')); ?></div>
            </div>
            <div class="ab-team__pics" data-reveal style="--d:2">
                <img class="ab-pic ab-team__big" src="<?= iu($ai, $T('team.6')) ?>" alt="" loading="lazy">
                <img class="ab-pic ab-team__a" src="<?= iu($ai, $T('team.7')) ?>" alt="" loading="lazy">
                <img class="ab-pic ab-team__b" src="<?= iu($ai, $T('team.8')) ?>" alt="" loading="lazy">
            </div>
        </div>
    </section>

    <!-- ============ 07 WHY BUSINESSES CHOOSE US ============ -->
    <section class="ab-section ab-why">
        <div class="ab-wrap">
            <div class="ab-why__head">
                <div>
                    <p class="ab-eyebrow" data-reveal>{!! $T('why.1') !!}</p>
                    <h2 class="ab-title" data-reveal style="--d:1">{!! $T('why.2') !!}</h2>
                </div>
                <p class="ab-p" data-reveal style="--d:2">{!! $T('why.3') !!}</p>
            </div>
            <div class="ab-why__grid">
                <?php foreach ($ab_reasons as $i => $r): ?>
                    <article class="ab-reason" data-reveal style="--d:<?= $i ?>">
                        <span class="ab-ico"><img src="<?= iu($ac, $r['icon']) ?>" width="26" height="26" alt=""></span>
                        <div>
                            <h3><?= $r['title'] ?></h3>
                            <p><?= $r['text'] ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ 08 CALL TO ACTION ============ -->
    <section class="ab-cta">
        <svg class="ab-cta__swoosh" viewBox="0 0 700 260" preserveAspectRatio="none" aria-hidden="true">
            <path d="M-20,230 C140,250 260,160 420,120 C520,95 620,70 720,10"/>
            <path d="M-20,200 C130,215 250,140 410,100 C510,76 610,52 720,-8"/>
            <path d="M-20,258 C150,270 280,190 440,150 C540,124 640,100 720,40"/>
        </svg>
        <div class="ab-wrap ab-cta__inner">
            <div class="ab-cta__copy">
                <p class="ab-eyebrow" data-reveal>{!! $T('cta.1') !!}</p>
                <h2 class="ab-title ab-title--light" data-reveal style="--d:1">{!! $T('cta.2') !!}</h2>
            </div>
            <div class="ab-cta__btns" data-reveal style="--d:2">
                <?php btn($T('cta.3'), 'btn--cta', 'arrow-link.svg', $T('cta.4', '#')); ?>
                <?php btn($T('cta.5'), 'btn--cta btn--ghost', 'arrow-link.svg', $T('cta.6', '#')); ?>
            </div>
        </div>
    </section>

</main>


@endsection
