@extends('site.layout')

@section('content')
@php
$ai = 'assets/images/';          // shared work imagery (service pages)
$ac = 'assets/icons/';
$caps = count($ab_caps);

/* Hero constellation geometry (viewBox 760x640) */
$hx = 380; $hy = 320; $hr = 205;
$hero_nodes = [];
foreach ($ab_caps as $i => $cap) {
    $deg = -90 + $i * 60;
    [$x, $y] = ab_pt($hx, $hy, $hr, $deg);
    $hero_nodes[] = ['x' => $x, 'y' => $y, 'deg' => $deg, 'cap' => $cap];
}
$hero_hex   = implode(' ', array_map(fn($n) => $n['x'] . ',' . $n['y'], $hero_nodes));
$hero_red   = sprintf('M%s,%s L%s,%s L%s,%s', $hero_nodes[0]['x'], $hero_nodes[0]['y'], $hx, $hy, $hero_nodes[2]['x'], $hero_nodes[2]['y']);

/* Capability map geometry (viewBox 800x800) */
$mx = 400; $my = 400;
$map_nodes = [];
foreach ($ab_caps as $i => $cap) {
    $deg = -90 + $i * 60;
    [$px, $py] = ab_pt($mx, $my, 290, $deg);
    [$ax, $ay] = ab_pt($mx, $my, 150, $deg);
    [$bx, $by] = ab_pt($mx, $my, 258, $deg);
    $map_nodes[] = ['px' => $px, 'py' => $py, 'ax' => $ax, 'ay' => $ay, 'bx' => $bx, 'by' => $by, 'cap' => $cap];
}
@endphp
<main class="page ab">

    <!-- ============ 01 HERO ============ -->
    <section class="ab-hero">
        <img class="ab-hero__pattern" src="<?= $ac ?>showcase-bg.svg" alt="">
        <div class="ab-hero__grid-bg" aria-hidden="true"></div>

        <div class="ab-hero__top">@include('site.partials.site-header')</div>

        <div class="ab-wrap ab-hero__grid">
            <div class="ab-hero__copy">
                <p class="ab-crumb"><a href="index.php">{!! $T('01-hero.1') !!}</a> / <span>{!! $T('01-hero.2') !!}</span></p>
                <p class="ab-eyebrow ab-eyebrow--light" data-reveal>{!! $T('01-hero.3') !!}</p>
                <h1 class="ab-hero__title" data-reveal style="--d:1">
                    <span>{!! $T('01-hero.4') !!}</span>
                    <span>{!! $T('01-hero.5') !!}</span>
                    <span>{!! $T('01-hero.6') !!}</span>
                    <em>{!! $T('01-hero.7') !!}</em>
                </h1>
                <p class="ab-hero__lead" data-reveal style="--d:2">{!! $T('01-hero.8') !!}</p>
                <p class="ab-hero__text" data-reveal style="--d:3">{!! $T('01-hero.9') !!}</p>
                <div class="ab-hero__btns" data-reveal style="--d:4">
                    <a href="{{ $T('01-hero.11') ?: '#' }}" class="ab-btn ab-btn--red">{!! $T('01-hero.10') !!}</a>
                    <a href="{{ $T('01-hero.13') ?: '#' }}" class="ab-btn ab-btn--ghost">{!! $T('01-hero.12') !!}</a>
                </div>
            </div>

            <!-- Connected capability constellation: one red active connection, softly pulsing nodes -->
            <div class="ab-hero__visual" data-reveal style="--d:2">
                <svg class="ab-orbit" viewBox="0 0 760 640" role="img" aria-label="Brand, Creative, Digital, Technology, Motion and Experiential connected around one partner">
                    <g class="ab-orbit__rings" fill="none">
                        <circle class="ab-spin" cx="<?= $hx ?>" cy="<?= $hy ?>" r="285" stroke-dasharray="2 12"/>
                        <circle cx="<?= $hx ?>" cy="<?= $hy ?>" r="<?= $hr ?>"/>
                        <circle cx="<?= $hx ?>" cy="<?= $hy ?>" r="112"/>
                        <line x1="<?= $hx ?>" y1="20" x2="<?= $hx ?>" y2="620"/>
                        <line x1="40" y1="<?= $hy ?>" x2="720" y2="<?= $hy ?>"/>
                    </g>
                    <polygon class="ab-orbit__hex" points="<?= $hero_hex ?>"/>
                    <?php foreach ($hero_nodes as $n): ?>
                        <line class="ab-orbit__spoke" x1="<?= $hx ?>" y1="<?= $hy ?>" x2="<?= $n['x'] ?>" y2="<?= $n['y'] ?>"/>
                    <?php endforeach; ?>

                    <path class="ab-orbit__red" d="<?= $hero_red ?>"/>
                    <circle r="5" class="ab-orbit__dot"><animateMotion dur="4.5s" repeatCount="indefinite" path="<?= $hero_red ?>"/></circle>

                    <circle class="ab-orbit__core" cx="<?= $hx ?>" cy="<?= $hy ?>" r="9"/>
                    <circle class="ab-pulse" cx="<?= $hx ?>" cy="<?= $hy ?>" r="9"/>
                    <text class="ab-orbit__center" x="<?= $hx ?>" y="<?= $hy + 34 ?>" text-anchor="middle">INTELLIWORKZ</text>

                    <?php foreach ($hero_nodes as $i => $n):
                        $cos = cos(deg2rad($n['deg']));
                        $sin = sin(deg2rad($n['deg']));
                        if ($cos > 0.3)       { $anchor = 'start';  $tx = $n['x'] + 20; $ny = $n['y'] - 4;  $ly = $n['y'] + 17; }
                        elseif ($cos < -0.3)  { $anchor = 'end';    $tx = $n['x'] - 20; $ny = $n['y'] - 4;  $ly = $n['y'] + 17; }
                        elseif ($sin < 0)     { $anchor = 'middle'; $tx = $n['x'];      $ny = $n['y'] - 44; $ly = $n['y'] - 24; }
                        else                  { $anchor = 'middle'; $tx = $n['x'];      $ny = $n['y'] + 32; $ly = $n['y'] + 52; }
                    ?>
                        <g class="ab-orbit__node<?= in_array($i, [0, 2], true) ? ' is-active' : '' ?>">
                            <circle class="ab-pulse" style="animation-delay: <?= $i * .6 ?>s" cx="<?= $n['x'] ?>" cy="<?= $n['y'] ?>" r="8"/>
                            <circle class="ab-orbit__pt" cx="<?= $n['x'] ?>" cy="<?= $n['y'] ?>" r="8"/>
                            <text class="ab-orbit__no" x="<?= $tx ?>" y="<?= $ny ?>" text-anchor="<?= $anchor ?>"><?= $n['cap']['no'] ?></text>
                            <text class="ab-orbit__label" x="<?= $tx ?>" y="<?= $ly ?>" text-anchor="<?= $anchor ?>"><?= strtoupper($n['cap']['name']) ?></text>
                        </g>
                    <?php endforeach; ?>
                </svg>
                <p class="ab-hero__tag">{!! $T('01-hero.14') !!}</p>
            </div>
        </div>

        <div class="ab-ticker" aria-hidden="true">
            <div class="ab-ticker__track">
                <?php for ($k = 0; $k < 4; $k++): foreach ($ab_caps as $cap): ?>
                    <span><?= strtoupper($cap['name']) ?></span><i></i>
                <?php endforeach; endfor; ?>
            </div>
        </div>

        <div class="ab-wrap">
            <dl class="ab-metrics">
                <?php foreach ($ab_metrics as $m): ?>
                    <div class="ab-metric">
                        <dt><span data-count="<?= $m['value'] ?>"><?= $m['value'] ?></span><?= $m['suffix'] ?></dt>
                        <dd><?= $m['label'] ?></dd>
                    </div>
                <?php endforeach; ?>
            </dl>
        </div>
    </section>

    <!-- ============ 02 WHO WE ARE ============ -->
    <section class="ab-section ab-who">
        <div class="ab-wrap">
            <div class="ab-who__grid">
                <div>
                    <p class="ab-eyebrow" data-reveal>{!! $T('02-who-we-are.1') !!}</p>
                    <h2 class="ab-display" data-reveal style="--d:1">{!! $T('02-who-we-are.2') !!}</h2>
                </div>
                <div class="ab-who__copy">
                    <p class="ab-who__big" data-reveal>{!! $T('02-who-we-are.3') !!}</p>
                    <p data-reveal style="--d:1">{!! $T('02-who-we-are.4') !!}</p>
                    <p data-reveal style="--d:2">{!! $T('02-who-we-are.5') !!}</p>
                </div>
            </div>

            <ol class="ab-chain" aria-label="Our six capabilities" data-reveal>
                <?php foreach ($ab_caps as $cap): ?>
                    <li style="--c: <?= $cap['c'] ?>"><span class="ab-chain__no"><?= $cap['no'] ?></span><?= $cap['name'] ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ 03 THE INTELLIWORKZ DIFFERENCE ============ -->
    <section class="ab-section ab-dark ab-diff">
        <div class="ab-wrap ab-diff__grid">
            <div class="ab-diff__copy">
                <p class="ab-eyebrow ab-eyebrow--light" data-reveal>{!! $T('03-the-intelliworkz-difference.1') !!}</p>
                <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('03-the-intelliworkz-difference.2') !!}</h2>
                <ul class="ab-diff__lines">
                    <?php foreach ($ab_diff_lines as $i => $l): ?>
                        <li data-reveal style="--d:<?= $i + 1 ?>"><strong><?= $l[0] ?></strong> <?= $l[1] ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="ab-body" data-reveal>{!! $T('03-the-intelliworkz-difference.3') !!}</p>
                <p class="ab-body" data-reveal style="--d:1">{!! $T('03-the-intelliworkz-difference.4') !!}</p>
            </div>

            <!-- Operating-system style capability map -->
            <div class="ab-map" data-reveal style="--d:2">
                <svg viewBox="0 0 800 800" role="img" aria-label="Your business at the centre, connected to Brand, Creative, Digital, Technology, Motion and Experiential">
                    <g class="ab-map__rings" fill="none">
                        <circle class="ab-spin ab-spin--rev" cx="<?= $mx ?>" cy="<?= $my ?>" r="372" stroke-dasharray="1 9"/>
                        <circle cx="<?= $mx ?>" cy="<?= $my ?>" r="215"/>
                    </g>
                    <polygon class="ab-map__hex" points="<?= implode(' ', array_map(fn($n) => $n['px'] . ',' . $n['py'], $map_nodes)) ?>"/>

                    <?php foreach ($map_nodes as $i => $n):
                        $pw = 232; $ph = 66;
                    ?>
                        <g class="ab-node" tabindex="0" style="--c: <?= $n['cap']['c'] ?>">
                            <line class="ab-node__line" x1="<?= $n['ax'] ?>" y1="<?= $n['ay'] ?>" x2="<?= $n['bx'] ?>" y2="<?= $n['by'] ?>"/>
                            <circle class="ab-node__dot" r="4"><animateMotion dur="3.2s" begin="-<?= $i * .5 ?>s" repeatCount="indefinite" path="M<?= $n['ax'] ?>,<?= $n['ay'] ?> L<?= $n['bx'] ?>,<?= $n['by'] ?>"/></circle>
                            <rect class="ab-node__pill" x="<?= $n['px'] - $pw / 2 ?>" y="<?= $n['py'] - $ph / 2 ?>" width="<?= $pw ?>" height="<?= $ph ?>" rx="<?= $ph / 2 ?>"/>
                            <text class="ab-node__no" x="<?= $n['px'] - $pw / 2 + 26 ?>" y="<?= $n['py'] + 6 ?>"><?= $n['cap']['no'] ?></text>
                            <text class="ab-node__label" x="<?= $n['px'] - 52 ?>" y="<?= $n['py'] + 9 ?>"><?= $n['cap']['name'] ?></text>
                        </g>
                    <?php endforeach; ?>

                    <circle class="ab-map__halo" cx="<?= $mx ?>" cy="<?= $my ?>" r="150"/>
                    <circle class="ab-map__core" cx="<?= $mx ?>" cy="<?= $my ?>" r="132"/>
                    <circle class="ab-pulse ab-pulse--lg" cx="<?= $mx ?>" cy="<?= $my ?>" r="132"/>
                    <text class="ab-map__small" x="<?= $mx ?>" y="<?= $my - 38 ?>" text-anchor="middle">AT THE CENTRE</text>
                    <text class="ab-map__title" x="<?= $mx ?>" y="<?= $my + 4 ?>" text-anchor="middle">YOUR</text>
                    <text class="ab-map__title" x="<?= $mx ?>" y="<?= $my + 44 ?>" text-anchor="middle">BUSINESS</text>
                </svg>
            </div>
        </div>
    </section>

    <!-- ============ 04 HOW WE THINK ============ -->
    <section class="ab-section ab-soft ab-think" data-think>
        <div class="ab-wrap">
            <div class="ab-think__head">
                <div>
                    <p class="ab-eyebrow" data-reveal>{!! $T('04-how-we-think.1') !!}</p>
                    <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('04-how-we-think.2') !!}</h2>
                </div>
                <div class="ab-think__text">
                    <p class="ab-body" data-reveal>{!! $T('04-how-we-think.3') !!}</p>
                    <p class="ab-body" data-reveal style="--d:1">{!! $T('04-how-we-think.4') !!}</p>
                    <p class="ab-body" data-reveal style="--d:2">{!! $T('04-how-we-think.5') !!}</p>
                </div>
            </div>

            <!-- Layered stack: the business is the foundation, every step is built on top of it -->
            <div class="ab-think__panel" data-reveal>
                <div class="ab-stage" aria-hidden="true">
                    <div class="ab-stack">
                        <?php foreach ($ab_think as $i => $t): ?><i class="ab-plate" style="--i:<?= $i ?>"></i><?php endforeach; ?>
                    </div>
                    <span class="ab-axis"></span>
                    <span class="ab-stage__tag">{!! $T('04-how-we-think.6') !!}</span>
                </div>

                <ol class="ab-think__steps">
                    <?php foreach ($ab_think as $i => $t): ?>
                        <li>
                            <button type="button" class="ab-tstep" data-think-step>
                                <span class="ab-tstep__no"><?= sprintf('%02d', $i + 1) ?></span>
                                <span class="ab-tstep__body">
                                    <span class="ab-tstep__word"><?= $t[0] ?></span>
                                    <span class="ab-tstep__sub"><?= $t[1] ?></span>
                                </span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </section>

    <!-- ============ 05 VALUES ============ -->
    <section class="ab-section ab-values">
        <div class="ab-wrap">
            <div class="ab-head">
                <p class="ab-eyebrow" data-reveal>{!! $T('05-values.1') !!}</p>
                <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('05-values.2') !!}</h2>
            </div>
            <div class="ab-values__list">
                <?php foreach ($ab_values as $i => $v): ?>
                    <article class="ab-value" data-reveal style="--d:<?= $i ?>">
                        <div class="ab-value__lead">
                            <span class="ab-value__no"><?= $v['no'] ?></span>
                            <span class="ab-value__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><?= $v['icon'] ?></svg></span>
                        </div>
                        <div class="ab-value__main">
                            <span class="ab-value__tag"><?= $v['tag'] ?></span>
                            <h3><?= $v['title'] ?></h3>
                        </div>
                        <p><?= $v['text'] ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ 06 CAPABILITIES ============ -->
    <section class="ab-section ab-dark ab-cap" id="capabilities" data-caps>
        <div class="ab-wrap">
            <div class="ab-cap__head">
                <div>
                    <p class="ab-eyebrow ab-eyebrow--light" data-reveal>{!! $T('06-capabilities.1') !!}</p>
                    <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('06-capabilities.2') !!}</h2>
                </div>
                <p class="ab-body" data-reveal style="--d:2">{!! $T('06-capabilities.3') !!}</p>
            </div>

            <div class="ab-cap__bar" data-reveal>
                <div class="ab-cap__tabs" role="tablist">
                    <?php foreach ($ab_caps as $i => $cap): ?>
                        <button type="button" role="tab" class="ab-cap__tab" data-caps-tab style="--c: <?= $cap['c'] ?>"><?= $cap['name'] ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="ab-cap__ctrl">
                    <span class="ab-cap__count"><b data-caps-count>01</b> / <?= sprintf('%02d', $caps) ?></span>
                    <button type="button" class="ab-arrow" aria-label="Previous capability" data-caps-prev><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg></button>
                    <button type="button" class="ab-arrow" aria-label="Next capability" data-caps-next><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg></button>
                </div>
            </div>
            <div class="ab-cap__progress"><span data-caps-bar></span></div>
        </div>

        <div class="ab-cap__track" data-caps-track tabindex="0" aria-label="Capabilities, scroll horizontally">
            <?php foreach ($ab_caps as $i => $cap): ?>
                <article class="ab-panel" style="--c: <?= $cap['c'] ?>">
                    <div class="ab-panel__info">
                        <span class="ab-panel__no"><?= $cap['no'] ?></span>
                        <h3><?= $cap['name'] ?></h3>
                        <p><?= $cap['text'] ?></p>
                        <ul class="ab-chips">
                            <?php foreach ($cap['items'] as $it): ?><li><?= $it ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="ab-panel__visual">
                        <?php if ($cap['img']): ?>
                            <img src="<?= iu($ai, $cap['img']) ?>" alt="<?= $cap['alt'] ?>" loading="lazy">
                        <?php else: ?>
                            <svg viewBox="0 0 800 450" role="img" aria-label="Websites, custom software, portals, eCommerce and mobile applications around a browser window">
                                <g stroke="#CFC8BE" stroke-width="1.5" stroke-dasharray="4 6" fill="none">
                                    <path d="M180 160 L262 205"/><path d="M400 122 L400 142"/><path d="M620 160 L538 205"/><path d="M240 300 L290 310"/><path d="M560 300 L510 310"/>
                                </g>
                                <g fill="#C42030"><circle cx="221" cy="182" r="5"/><circle cx="579" cy="182" r="5"/><circle cx="265" cy="305" r="5"/><circle cx="535" cy="305" r="5"/></g>
                                <rect x="260" y="142" width="280" height="196" rx="14" fill="#fff" stroke="#E4DED6" stroke-width="2"/>
                                <path d="M260 156a14 14 0 0 1 14-14h252a14 14 0 0 1 14 14v22H260z" fill="#F1ECE6"/>
                                <circle cx="282" cy="160" r="5" fill="#F0412C"/><circle cx="300" cy="160" r="5" fill="#F6B700"/><circle cx="318" cy="160" r="5" fill="#59B38D"/>
                                <rect x="282" y="198" width="70" height="8" rx="4" fill="#7F68CE"/><rect x="282" y="218" width="120" height="8" rx="4" fill="#E4DED6"/><rect x="282" y="238" width="90" height="8" rx="4" fill="#E4DED6"/><rect x="282" y="258" width="140" height="8" rx="4" fill="#E4DED6"/><rect x="282" y="278" width="60" height="8" rx="4" fill="#C42030"/>
                                <rect x="430" y="198" width="86" height="100" rx="8" fill="#F5F1FC"/>
                                <text x="473" y="258" text-anchor="middle" font-family="Inter, sans-serif" font-size="30" font-weight="600" fill="#7F68CE">&lt;/&gt;</text>
                                <g font-family="Inter, sans-serif" font-size="17" font-weight="600" fill="#14141A" text-anchor="middle">
                                    <circle cx="400" cy="62" r="60" fill="#E6DDF7"/><text x="400" y="58">{!! $T('06-capabilities.4') !!}</text><text x="400" y="80">{!! $T('06-capabilities.5') !!}</text>
                                    <circle cx="130" cy="140" r="62" fill="#DCEBFA"/><text x="130" y="146">{!! $T('06-capabilities.6') !!}</text>
                                    <circle cx="670" cy="140" r="62" fill="#D9F0E4"/><text x="670" y="146">{!! $T('06-capabilities.7') !!}</text>
                                    <circle cx="190" cy="340" r="62" fill="#FBE3D6"/><text x="190" y="336">{!! $T('06-capabilities.8') !!}</text><text x="190" y="358">{!! $T('06-capabilities.9') !!}</text>
                                    <circle cx="610" cy="340" r="62" fill="#FADDE7"/><text x="610" y="336">{!! $T('06-capabilities.10') !!}</text><text x="610" y="358">{!! $T('06-capabilities.11') !!}</text>
                                </g>
                            </svg>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ 07 HOW WE WORK ============ -->
    <section class="ab-section ab-work">
        <div class="ab-wrap">
            <div class="ab-head">
                <p class="ab-eyebrow" data-reveal>{!! $T('07-how-we-work.1') !!}</p>
                <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('07-how-we-work.2') !!}</h2>
            </div>
            <ol class="ab-path">
                <?php foreach ($ab_steps as $i => $s): ?>
                    <li class="ab-step" data-reveal style="--d:<?= $i ?>">
                        <span class="ab-step__node"></span>
                        <?php if ($i === 2): ?><span class="ab-hook" aria-hidden="true"></span><?php endif; ?>
                        <span class="ab-step__no"><?= $s['no'] ?></span>
                        <h3><?= $s['title'] ?></h3>
                        <p><?= $s['text'] ?></p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- ============ 08 TEAM ============ -->
    <section class="ab-section ab-team">
        <div class="ab-wrap ab-team__grid">
            <div class="ab-team__copy">
                <p class="ab-eyebrow" data-reveal>{!! $T('08-team.1') !!}</p>
                <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('08-team.2') !!}</h2>
                <p class="ab-body" data-reveal>{!! $T('08-team.3') !!}</p>
                <p class="ab-body" data-reveal>{!! $T('08-team.4') !!}</p>
                <ul class="ab-chips ab-chips--light" data-reveal>
                    <?php foreach ($ab_team_tags as $t): ?><li><?= $t ?></li><?php endforeach; ?>
                </ul>
            </div>
            <div class="ab-collage">
                <?php foreach ($ab_team_tiles as $i => $t): ?>
                    <figure class="ab-tile <?= $t['cls'] ?>" data-reveal style="--d:<?= $i ?>">
                        <img src="<?= iu($ai, $t['img']) ?>" alt="" loading="lazy">
                        <figcaption><?= $t['label'] ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ 09 ECOSYSTEM ============ -->
    <section class="ab-section ab-eco">
        <div class="ab-wrap">
            <div class="ab-cap__head ab-eco__head">
                <div>
                    <p class="ab-eyebrow" data-reveal>{!! $T('09-ecosystem.1') !!}</p>
                    <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('09-ecosystem.2') !!}</h2>
                </div>
                <p class="ab-body" data-reveal style="--d:2">{!! $T('09-ecosystem.3') !!}</p>
            </div>
            <div class="ab-eco__list">
                <?php foreach ($ab_eco as $i => $e): ?>
                    <article class="ab-company" data-reveal style="--d:<?= $i ?>">
                        <span class="ab-company__no"><?= sprintf('%02d', $i + 1) ?></span>
                        <div class="ab-company__main">
                            <h3><?= $e['name'] ?></h3>
                            <p><?= $e['cat'] ?></p>
                        </div>
                        <ul class="ab-company__tags">
                            <?php foreach ($e['tags'] as $t): ?><li><?= $t ?></li><?php endforeach; ?>
                        </ul>
                        <span class="ab-company__arrow" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M8 7h9v9"/></svg></span>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ 10 WHY CLIENTS STAY ============ -->
    <section class="ab-section ab-dark ab-stay">
        <svg class="ab-stay__loop" viewBox="0 0 600 600" aria-hidden="true" fill="none">
            <circle cx="300" cy="300" r="290" stroke-dasharray="2 10"/>
            <circle cx="300" cy="300" r="210"/>
            <circle cx="300" cy="300" r="130"/>
            <path class="ab-stay__arc" d="M300 10A290 290 0 0 1 590 300"/>
        </svg>
        <div class="ab-wrap ab-stay__layout">
            <div class="ab-stay__intro">
                <p class="ab-eyebrow ab-eyebrow--light" data-reveal>{!! $T('10-why-clients-stay.1') !!}</p>
                <h2 class="ab-h2" data-reveal style="--d:1">{!! $T('10-why-clients-stay.2') !!}</h2>
                <p class="ab-stay__statement" data-reveal style="--d:2">{!! $T('10-why-clients-stay.3') !!}</p>

                <!-- the project is a short segment; the relationship keeps going -->
                <div class="ab-span" data-reveal style="--d:3" aria-hidden="true">
                    <div class="ab-span__track">
                        <span class="ab-span__node"></span>
                        <span class="ab-span__solid"></span>
                        <span class="ab-span__flow"></span>
                        <span class="ab-span__head"></span>
                    </div>
                    <div class="ab-span__labels"><span>{!! $T('10-why-clients-stay.4') !!}</span><span>{!! $T('10-why-clients-stay.5') !!}</span></div>
                </div>
            </div>

            <div class="ab-stay__cards">
                <?php foreach ($ab_stay as $i => $s): ?>
                    <article class="ab-point" data-reveal style="--d:<?= $i ?>">
                        <div class="ab-point__top">
                            <span class="ab-point__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><?= $s['icon'] ?></svg></span>
                            <span class="ab-point__no"><?= sprintf('%02d', $i + 1) ?></span>
                        </div>
                        <h3><?= $s['title'] ?></h3>
                        <p><?= $s['text'] ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ============ 11 FINAL CTA ============ -->
    <section class="ab-cta">
        <img class="ab-cta__grid" src="<?= $ac ?>grid-lines.svg" alt="">
        <div class="ab-wrap ab-cta__inner">
            <div class="ab-cta__copy">
                <p class="ab-eyebrow ab-eyebrow--light" data-reveal>{!! $T('11-final-cta.2') !!}</p>
                <h2 class="ab-display ab-display--light ab-display--sm" data-reveal style="--d:1">{!! $T('11-final-cta.3') !!}</h2>
                <p data-reveal style="--d:2">{!! $T('11-final-cta.4') !!}</p>
            </div>
            <div class="ab-cta__action" data-reveal style="--d:3">
                <?php btn($T('11-final-cta.1'), 'btn--cta', 'arrow-cta.svg', 'contact-us.php'); ?>
            </div>
        </div>
    </section>

</main>


@endsection
