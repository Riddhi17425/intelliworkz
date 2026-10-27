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
                    <?php btn($T('hero.5'), 'btn--cta', null, $T('hero.6', '#')); ?>
                    <?php btn($T('hero.7'), 'btn--cta btn--ghost', null, $T('hero.8', '#')); ?>
                </div>
            </div>
            <p class="ab-hero__words" aria-hidden="true">{!! $T('hero.9') !!}</p>
        </div>
    </section>

    <!-- ============ 02 WHO WE ARE ============ -->
    @php
        // the first paragraph of who.3 sits under the heading across the full width; the rest stays beside the pictures
        $whoParas = array_values(array_filter(array_map('trim', preg_split('#(?:<br\s*/?>\s*){2,}#i', (string) $T('who.3')))));
        $whoTop = array_slice($whoParas, 0, 1);
        $whoRest = array_slice($whoParas, 1);
    @endphp
    <section class="ab-section ab-who">
        <div class="ab-wrap">
            <div class="ab-who__intro">
                <h2 class="ab-title" data-reveal style="--d:1">{!! preg_replace('#<br\s*/?>#i', ' ', (string) $T('who.2')) !!}</h2>
                <div class="ab-who__lead">
                    @foreach($whoTop as $i => $para)
                        <p class="ab-p" data-reveal style="--d:{{ $i + 2 }}">{!! $para !!}</p>
                    @endforeach
                </div>
            </div>
            <div class="ab-split">
                <div class="ab-copy">
                    @foreach($whoRest as $para)
                        <p class="ab-p" data-reveal style="--d:2">{!! $para !!}</p>
                    @endforeach
                    <p class="ab-p" data-reveal style="--d:3">{!! $T('who.4') !!}</p>
                    <div data-reveal style="--d:4"><?php btn($T('who.5'), 'btn--cta', null, $T('who.6', '#')); ?></div>
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
        </div>
    </section>

    <!-- ============ 03 NUMBERS ============ -->
    <section class="container metrics ab-metrics">
        <?php foreach ($ab_metrics as $i => $m): ?>
            <?php if ($i): ?><span class="metrics__divider"></span><?php endif; ?>
            <div class="metric">
                <span class="metric__icon"><img src="<?= iu($ac, $m['icon']) ?>" width="24" height="24" alt=""></span>
                <div class="metric__body">
                    <div class="metric__value"><span data-count="<?= $m['value'] ?>"><?= $m['value'] ?></span><span>+</span></div>
                    <span class="metric__label"><?= $m['label'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
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
                <h2 class="ab-title ab-title--light" data-reveal style="--d:1">{!! $T('approach.2') !!}</h2>
                <p class="ab-p ab-p--light" data-reveal style="--d:2">{!! $T('approach.3') !!}</p>
                <div data-reveal style="--d:3"><?php btn($T('approach.4'), 'btn--cta', null, $T('approach.5', '#')); ?></div>
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
                            <rect x="<?= $n['x'] - 122 ?>" y="<?= $n['y'] - 29 ?>" width="244" height="58" rx="29"/>
                            <circle cx="<?= $n['x'] - 92 ?>" cy="<?= $n['y'] ?>" r="20"/>
                            <image href="<?= $ac . $n['icon'] ?>" x="<?= $n['x'] - 103 ?>" y="<?= $n['y'] - 11 ?>" width="22" height="22"/>
                            <text x="<?= $n['x'] - 64 ?>" y="<?= $n['y'] + 6 ?>"><?= $n['label'] ?></text>
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
                <h2 class="ab-title" data-reveal style="--d:1">{!! $T('team.2') !!}</h2>
                <p class="ab-p" data-reveal style="--d:2">{!! $T('team.3') !!}</p>
                <div data-reveal style="--d:3"><?php btn($T('team.4'), 'btn--cta', null, $T('team.5', '#')); ?></div>
            </div>
            <div class="ab-team__pics" data-reveal style="--d:2">
                <img class="ab-pic ab-team__big" src="<?= iu($ai, $T('team.6')) ?>" alt="" loading="lazy">
                <img class="ab-pic ab-team__a" src="<?= iu($ai, $T('team.7')) ?>" alt="" loading="lazy">
                <img class="ab-pic ab-team__b" src="<?= iu($ai, $T('team.8')) ?>" alt="" loading="lazy">
            </div>
        </div>
    </section>

    <!-- ============ 08 OUR COMMITMENT ============ -->
    @if(trim(strip_tags($T('commit.2'))) !== '')
    <section class="ab-section ab-commit">
        <div class="ab-wrap ab-commit__box" data-reveal>
            <div class="ab-commit__head">
                <span class="ab-eyebrow">{!! $T('commit.1') !!}</span>
                <h2 class="ab-title">{!! $T('commit.2') !!}</h2>
            </div>
            <div class="ab-commit__copy">
                <p class="ab-p">{!! $T('commit.3') !!}</p>
                <p class="ab-p">{!! $T('commit.4') !!}</p>
            </div>
        </div>
    </section>
    @endif

    <!-- ============ 07 WHY BUSINESSES CHOOSE US ============ -->
    <section class="ab-section ab-why">
        <div class="ab-wrap">
            <div class="ab-why__head">
                <div>
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
    <section class="cta-band ab-cta-band">
        <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
        <h2 class="cta-title">{!! $T('cta.2') !!}</h2>
        <div class="ab-cta__btns">
            <?php btn($T('cta.3'), 'btn--cta', 'arrow-cta.svg', $T('cta.4', '#')); ?>
            <?php btn($T('cta.5'), 'btn--cta btn--ghost', null, $T('cta.6', '#')); ?>
        </div>
    </section>

</main>


@endsection
