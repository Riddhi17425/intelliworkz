@extends('site.layout')

@section('content')
@php
$ti = 'assets/images/3d/';   // page images
$tc = 'assets/icons/3d/';    // page icons
$vi = 'assets/images/video/'; // shared with video/magazine pages (offer + engagement images)
$vc = 'assets/icons/video/';  // shared icons
$tm = 'assets/models/3d/';    // 3D model files (.glb)
@endphp
<!-- <model-viewer>: real drag-to-rotate 3D viewer (Google's web component, loads a .glb). -->
<script type="module" src="https://cdn.jsdelivr.net/npm/@google/model-viewer@3.5.0/dist/model-viewer.min.js"></script>

<main class="page ve td">

    <!-- ============ HERO ============ -->
    <section class="ve-hero">
        <img class="ve-hero__pattern" src="assets/icons/showcase-bg.svg" alt="">
        <img class="ve-hero__floor" src="{{ $T('hero.1') }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container ve-hero__cols">
                <div class="ve-hero__copy">
                    <p class="breadcrumb">{!! $T('hero.2') !!}</p>

                    <div class="ve-hero__stack">
                        <div class="ve-hero__group">
                            <div class="ve-hero__intro">
                                <h1 class="h1">{!! $T('hero.3') !!}</h1>
                                <p>{!! $T('hero.4') !!}</p>
                            </div>
                            <ul class="ve-benefits">
                                <?php foreach ($td_benefits as $b): ?><li><?= $b ?></li><?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="ve-pm">
                            <span class="ve-pm__icon"><img src="<?= $vc ?>pm-users.svg" width="24" height="24" alt=""></span>
                            <div>
                                <strong>{!! $T('hero.5') !!}</strong>
                                <span>{!! $T('hero.6') !!}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="lead-form" action="{{ route('inquiries.store') }}" method="post" data-inquiry-form novalidate>
@csrf
<input type="hidden" name="source" value="{{ $page->slug }}">
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
                    <div class="lead-form__head">
                        <h2>{!! $T('hero.7') !!}</h2>
                        <p>{!! $T('hero.8') !!}</p>
                    </div>
                    <div class="lead-form__fields">
                        <?php foreach (array_chunk($ve_form_fields, 2) as $row): ?>
                            <div class="lead-form__row">
                                <?php foreach ($row as $f): ?>
                                    <label class="field"><span><?= $f[0] ?></span><input type="<?= $f[2] ?>" name="<?= ($f[3] ?? strtolower(str_replace(' ', '_', $f[0]))) ?>" placeholder="<?= $f[1] ?>"></label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <label class="field"><span>{!! $T('hero.9') !!}</span><input type="text" name="subject" placeholder="Enter your subject"></label>
                        <label class="field"><span>{!! $T('hero.10') !!}</span><textarea name="message" rows="1" placeholder="Tell us about your requirements"></textarea></label>
                    </div>
                    <p class="form-status" data-form-status role="status" aria-live="polite"></p>
                    <button type="submit" class="btn btn--cta btn--block">{!! $T('hero.11') !!}</button>
                    <p class="lead-form__privacy"><img src="<?= $vc ?>lock.svg" width="14" height="14" alt="">We respect your privacy. Your information is safe with us.</p>
                </form>
            </div>
        </div>
    </section>

    <!-- ============ 360 VIEWER + SELECTED WORK ============ -->
    <!-- Figma keeps the heading, filter tabs, gallery strip, the "Flexibel" panel and the
         Exploded View button all inside ONE 1920x920 scene (node 6:179), overlaid on the same
         grid-floor photo, not stacked as separate blocks. Every left/top below is that node's
         exact coordinate. -->
    <section class="td-viewer">
        <img class="td-viewer__bg" src="{{ $T('360-viewer-selected-work.1') }}" alt="">
        <img class="td-viewer__texture" src="{{ $T('360-viewer-selected-work.2') }}" alt="">

        <h2 class="h2 h2--center td-viewer__heading">{!! $T('360-viewer-selected-work.3') !!}</h2>
        <div class="filter-tabs td-viewer__tabs">
            <?php foreach ($td_filters as $key => $label): ?>
                <button type="button" class="filter-tab <?= $key === 'all' ? 'is-active' : '' ?>" data-filter="<?= $key ?>"><?= $label ?></button>
            <?php endforeach; ?>
        </div>

        <div class="td-rings">
            <span class="td-rings__ellipse td-rings__ellipse--shadow"></span>
            <span class="td-rings__ellipse td-rings__ellipse--outer-dash"></span>
            <span class="td-rings__ellipse td-rings__ellipse--outer"></span>
            <span class="td-rings__ellipse td-rings__ellipse--mid"></span>
            <span class="td-rings__ellipse td-rings__ellipse--inner"></span>
            <span class="td-rings__dot"></span>
            <!-- Real, drag-to-rotate 3D model (glTF/.glb via <model-viewer>). Loads assembled by
                 default; the Exploded View button below swaps the actual .glb src to the "-open"
                 file, not a camera trick. -->
            <model-viewer
                class="td-rings__product"
                src="<?= $tm ?>expansion-joint.glb"
                poster="<?= $ti ?>product.png"
                alt="Belt type fabric expansion joint — 3D model"
                camera-controls
                disable-pan
                touch-action="pan-y"
                interaction-prompt="none"
                shadow-intensity="0"
                exposure="1.2"
                auto-rotate
                rotation-per-second="18deg"
                camera-orbit="25deg 75deg 85%"
                field-of-view="30deg"
                min-camera-orbit="auto 55deg auto"
                max-camera-orbit="auto 100deg auto"
                min-field-of-view="20deg"
                max-field-of-view="45deg"
                data-src-normal="<?= $tm ?>expansion-joint.glb"
                data-src-exploded="<?= $tm ?>expansion-joint-open.glb"
                reveal="auto"
            ></model-viewer>
            <img class="td-rings__cursor" src="<?= $tc ?>rotate-cursor.svg" alt="">
        </div>
        <div class="td-cue">
            <strong>360°</strong><span>{!! $T('360-viewer-selected-work.4') !!}</span>
        </div>

        <div class="td-viewer__panel">
            <h3>{!! $T('360-viewer-selected-work.5') !!}</h3> <!-- kept exactly as written in Figma; looks like a typo for "Flexible" -->
            <p class="td-viewer__panel-sub">{!! $T('360-viewer-selected-work.6') !!}</p>
            <p class="td-viewer__panel-text">{!! $T('360-viewer-selected-work.7') !!}</p>
        </div>
        <button type="button" class="td-viewer__btn" data-explode-toggle aria-pressed="false">
            <span data-explode-label>{!! $T('360-viewer-selected-work.8') !!}</span>
            <span class="td-viewer__btn-icon"><img src="<?= $tc ?>exploded-view.svg" width="20" height="20" alt=""></span>
        </button>

        <div class="td-gallery">
            <button type="button" class="round-arrow" aria-label="Previous" data-gallery-prev><img src="<?= $vc ?>slider-arrow-left.svg" width="52" height="52" alt=""></button>
            <div class="td-gallery__viewport" data-gallery>
                <div class="td-gallery__track" data-gallery-track>
                    <?php foreach ($td_gallery as $i => $g): ?>
                        <button type="button" class="td-gallery__tile <?= $i === 5 ? 'is-featured' : '' ?>" data-cats="<?= $g['cats'] ?>">
                            <img src="<?= iu($ti, $g['img']) ?>" alt="3D render example">
                            <span class="td-gallery__badge"><?= sprintf('%02d', $i + 1) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="button" class="round-arrow" aria-label="Next" data-gallery-next><img src="<?= $vc ?>slider-arrow-right.svg" width="52" height="52" alt=""></button>
        </div>
    </section>

    <!-- ============ STATS ============ -->
    <section class="container ve-stats td-stats">
        <img class="td-stats__bg" src="{{ $T('stats.1') }}" alt="">
        <div class="ve-stats__content ve-stats__content--center">
            <?php foreach ([$td_stats_top, $td_stats_bottom] as $n => $row): ?>
                <?php if ($n): ?><hr class="ve-stats__rule"><?php endif; ?>
                <div class="ve-stats__row">
                    <?php foreach ($row as $i => $s): ?>
                        <?php if ($i): ?><span class="ve-stats__divider"></span><?php endif; ?>
                        <div class="ve-stat">
                            <div class="ve-stat__value"><span data-count="<?= $s[0] ?>"><?= $s[0] ?></span><span>+</span></div>
                            <span class="ve-stat__label"><?= $s[1] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ WHY INTELLIWORKZ ============ -->
    <section class="container ve-why">
        <div class="ve-split">
            <h2 class="h2">{!! $T('why-intelliworkz.1') !!}</h2>
            <div class="ve-split__side">
                <p>{!! $T('why-intelliworkz.2') !!}</p>
                <p>{!! $T('why-intelliworkz.3') !!}</p>
                <p>{!! $T('why-intelliworkz.4') !!}</p>
                <div><a href="{{ $T('why-intelliworkz.6') ?: '#' }}" class="btn btn--enquire">{!! $T('why-intelliworkz.5') !!}</a></div>
            </div>
        </div>
        <div class="td-benefits">
            <?php foreach ($td_benefit_cards as $c): ?>
                <article class="td-benefit <?= $c['raised'] ? 'is-raised' : '' ?>" style="--c: <?= $c['color'] ?? $c['ring'] ?>">
                    <span class="td-benefit__deco"></span>
                    <span class="td-benefit__no" style="color: <?= $c['noc'] ?>66"><?= $c['no'] ?></span>
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

    <!-- ============ TOOLS STRIP ============ -->
    <section class="ve-tools td-tools">
        <img class="ve-tools__bg" src="{{ $T('tools-strip.1') }}" alt="">
        <ul class="ve-tools__row">
            <?php foreach ($td_tools as $i => $t): ?>
                <?php if ($i): ?><li class="ve-tools__divider"></li><?php endif; ?>
                <?php $is_ps = $t[0] === 'tool-photoshop.svg'; ?>
                <li class="ve-tool <?= $t[0] === 'tool-3dsmax.png' || $is_ps ? '' : 've-tool--mark' ?> <?= $t[0] === 'tool-blender.svg' ? 'td-tool--lg' : '' ?>">
                    <?php if ($is_ps): ?>
                        <span class="td-tool__tile"><img src="<?= iu($tc, $t[0]) ?>" alt=""></span>
                    <?php else: ?>
                        <img src="<?= iu((str_ends_with($t[0], '.svg') ? $tc : $ti), $t[0]) ?>" alt="">
                    <?php endif; ?>
                    <div><strong><?= $t[1] ?></strong><span><?= $t[2] ?></span></div>
                </li>
            <?php endforeach; ?>
            <li class="ve-tools__divider"></li>
            <li class="ve-tool td-tool-formats">
                <img class="td-tool-formats__img" src="{{ $T('tools-strip.2') }}" alt="STP, FBX, STL and OBJ file icons">
                <div><strong><?= $td_tool_formats_label[0] ?></strong><span><?= $td_tool_formats_label[1] ?></span></div>
            </li>
            <li class="ve-tools__divider"></li> <!-- Figma ends the row with a trailing divider -->
        </ul>
    </section>

    <!-- ============ SERVICES + WORKFLOW ============ -->
    <section class="ve-services-flow">
        <div class="container ve-services">
            <div class="ve-split ve-split--center">
                <h2 class="h2">{!! $T('services-workflow.2') !!}</h2>
                <div class="ve-split__side">
                    <p>{!! $T('services-workflow.3') !!}</p>
                    <div><a href="{{ $T('services-workflow.5') ?: '#' }}" class="btn btn--enquire">{!! $T('services-workflow.4') !!}</a></div>
                </div>
            </div>

            <div class="ve-services__content">
                <div class="acc acc--services" data-accordion>
                    <?php foreach ($td_services as $i => $s): ?>
                        <?php if ($i): ?><hr class="acc__rule"><?php endif; ?>
                        <div class="acc-item <?= !empty($s['open']) ? 'is-open' : '' ?>">
                            <button type="button" class="acc-head" aria-expanded="<?= !empty($s['open']) ? 'true' : 'false' ?>">
                                <span class="acc-title"><?= $s['title'] ?></span>
                                <span class="acc-icons"><img class="acc-icon acc-icon--closed" src="<?= $vc ?>acc-icon-2.svg" width="42" height="42" alt=""><img class="acc-icon acc-icon--open" src="<?= $vc ?>acc-icon-1.svg" width="42" height="42" alt=""></span>
                            </button>
                            <div class="acc-panel"><div class="acc-panel__inner">
                                <ul class="dot-list dot-list--plain acc-points">
                                    <?php foreach ($s['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                                </ul>
                            </div></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <img class="td-services__img" src="{{ $T('services-workflow.1') }}" alt="3D rendering services overview">
            </div>
        </div>

        <div class="td-workflow">
            <?php foreach ($td_workflow as $w): ?>
                <article class="td-step" style="--c: <?= $w['color'] ?>">
                    <img class="td-step__img" src="<?= iu($ti, $w['img']) ?>" alt="">
                    <div class="td-step__no"><?= $w['no'] ?><span></span></div>
                    <h3><?= $w['title'] ?></h3>
                    <p><?= $w['text'] ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
@endphp
    @include('site.sections.showcase')

    <!-- ============ WHAT WE OFFER + CTA + ENGAGEMENT ============ -->
    <div class="ve-offer-engagement">
        <section class="ve-offers">
            <div class="ve-offers__head">
                <h2 class="h2 h2--center">{!! $T('what-we-offer-cta-engagement.2') !!}</h2>
                <p>{!! $T('what-we-offer-cta-engagement.3') !!}</p>
            </div>
            <div class="container offer-viewport" data-offers>
                <div class="offer-cards" data-offers-track>
                    <?php foreach ($td_offers as $k => $o): ?>
                        <article class="offer-card" data-i="<?= $k ?>" style="background: <?= $o['bg'] ?>">
                            <div class="offer-card__top">
                                <span class="offer-card__no" style="color: <?= $o['noc'] ?>"><?= $o['no'] ?></span>
                                <a href="#" aria-label="<?= $o['title'] ?>"><img src="<?= iu($vc, $o['arrow']) ?>" width="48" height="48" alt=""></a>
                            </div>
                            <div class="offer-card__body">
                                <h3><?= $o['title'] ?></h3>
                                <p><?= $o['text'] ?></p>
                            </div>
                            <img class="offer-card__img" src="<?= iu($vi, $o['img']) ?>" alt="" style="left: <?= $o['x'] ?>px; top: <?= $o['y'] ?>px; width: <?= $o['w'] ?>px; height: <?= $o['h'] ?>px">
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="dots dots--navy dots--center" data-offers-dots>
                <?php foreach ($td_offers as $o): ?><button type="button" class="dots__dot" aria-label="Show <?= $o['title'] ?>"></button><?php endforeach; ?>
            </div>
        </section>

        <section class="cta-band">
            <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
            <h2 class="cta-title">{!! $T('what-we-offer-cta-engagement.4') !!}</h2>
            <?php btn($T('what-we-offer-cta-engagement.1'), 'btn--cta', 'arrow-cta.svg'); ?>
        </section>

        @include('site.sections.engagement')
    </div>

    <!-- ============ FAQs ============ -->
    <section class="container ve-faq">
        <h2 class="h2 h2--center">{!! $T('faqs.1') !!}</h2>
        <div class="acc acc--faq" data-accordion>
            <?php foreach ($td_faqs as $i => $f): ?>
                <div class="acc-item <?= $i === 0 ? 'is-open' : '' ?>">
                    <button type="button" class="acc-head" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>">
                        <span class="acc-chevron"><img src="<?= $vc ?>faq-chevron.svg" width="20" height="20" alt=""></span>
                        <span class="acc-title"><?= $f[0] ?></span>
                    </button>
                    <div class="acc-panel"><div class="acc-panel__inner"><p><?= $f[1] ?></p></div></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>


@endsection
