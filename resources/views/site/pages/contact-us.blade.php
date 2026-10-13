@extends('site.layout')

@section('content')
@php
$ni = 'assets/images/contact/';   // page images
$nc = 'assets/icons/contact/';    // page icons
$vc = 'assets/icons/video/';      // shared icons
@endphp
<main class="page ve ct">

    <!-- ============ HERO ============ -->
    <section class="ve-hero ve-hero--plain ct-hero">
        <img class="ct-hero__photo" src="{{ $T('hero.1') }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container mf-hero__copy ct-hero__copy">
                <p class="breadcrumb breadcrumb--light">{!! $T('hero.2') !!}</p>
                <div class="ct-hero__text">
                    <h1 class="h1">{!! $T('hero.3') !!}</h1>
                    <p>{!! $T('hero.4') !!}</p>
                </div>
                <div class="mf-hero__btns">
                    <a href="{{ $T('hero.6') ?: '#' }}" class="mf-btn mf-btn--red">{!! $T('hero.5') !!}</a>
                    <a href="{{ $T('hero.8') ?: '#' }}" class="mf-btn mf-btn--ghost">{!! $T('hero.7') !!}</a>
                </div>
            </div>
        </div>
    </section>

    <div class="ct-body">

        <!-- ============ FORM ============ -->
        <section class="container ct-form" id="contact-form">
            <div class="ct-form__copy">
                <h2 class="h2">{!! $T('form.2') !!}</h2>
                <p>{!! $T('form.3') !!}</p>
                <img class="ct-form__outline" src="{{ $T('form.1') }}" alt="">
            </div>

            <form class="ct-card" action="{{ route('inquiries.store') }}" method="post" data-inquiry-form novalidate>
@csrf
<input type="hidden" name="source" value="{{ $page->slug }}">
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
                <div class="ct-card__head">
                    <h2>{!! $T('form.4') !!}</h2> <!-- kept exactly as written in Figma; looks like a leftover from the Packaging page -->
                    <p>{!! $T('form.5') !!}</p>
                </div>
                <div class="ct-card__fields">
                    <?php foreach (array_chunk($ve_form_fields, 2) as $row): ?>
                        <div class="ct-card__row">
                            <?php foreach ($row as $f): ?>
                                <label class="ct-field"><span><?= $f[0] ?></span><input type="<?= $f[2] ?>" name="<?= ($f[3] ?? strtolower(str_replace(' ', '_', $f[0]))) ?>" placeholder="<?= $f[1] ?>"></label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    <label class="ct-field"><span>{!! $T('form.6') !!}</span><input type="text" name="subject" placeholder="Enter your subject"></label>
                    <label class="ct-field"><span>{!! $T('form.7') !!}</span><textarea name="message" rows="1" placeholder="Tell us about your requirements"></textarea></label>
                </div>
                <p class="form-status" data-form-status role="status" aria-live="polite"></p>
                <button type="submit" class="btn btn--cta btn--block">{!! $T('form.8') !!}</button>
                <p class="ct-card__privacy"><img src="<?= $vc ?>lock.svg" width="14" height="14" alt="">We respect your privacy. Your information is safe with us.</p>
            </form>
        </section>

        <!-- ============ OFFICES (dark) ============ -->
        <section class="ct-dark">
            <div class="ct-dark__head">
                <h2 class="h2">{!! $T('offices-dark.1') !!}</h2>
                <p>{!! $T('offices-dark.2') !!}</p>
            </div>

            <img class="ct-dark__map" src="<?= $nc ?>world-map.svg" alt="">
            <?php foreach ($ct_pins as $p): ?>
                <span class="ct-pin" style="left: <?= $p[1] ?>px; top: <?= $p[2] ?>px"></span>
                <span class="ct-pin__label" style="left: <?= $p[3] ?>px; top: <?= $p[4] ?>px"><?= $p[0] ?></span>
            <?php endforeach; ?>

            <div class="ct-offices" data-offices>
                <div class="ct-offices__tabs">
                    <?php $n = 0; foreach ($ct_offices as $key => $o): ?>
                        <button type="button" class="ct-offices__tab <?= $n++ === 0 ? 'is-active' : '' ?>" data-office="<?= $key ?>"><?= $o['tab'] ?></button>
                    <?php endforeach; ?>
                </div>
                <?php $n = 0; foreach ($ct_offices as $key => $o): ?>
                    <div class="ct-office" data-office-panel="<?= $key ?>" <?= $n++ === 0 ? '' : 'hidden' ?>>
                        <div class="ct-office__id"><strong><?= $o['country'] ?></strong><span><?= $o['city'] ?></span></div>
                        <hr class="ct-office__rule">
                        <div class="ct-office__cols">
                            <?php foreach ($o['blocks'] as $row): foreach ($row as $b): ?>
                                <div class="ct-office__block"><span class="ct-office__label"><?= $b[0] ?></span><p><?= map_link($b[1], $o['map'] ?? null) ?></p></div>
                            <?php endforeach; endforeach; ?>
                            <div class="ct-office__block ct-office__block--contact">
                                <div><span class="ct-office__label">{!! $T('offices-dark.3') !!}</span><p><?= contact_html($o['phone']) ?></p></div>
                                <div><span class="ct-office__label">{!! $T('offices-dark.4') !!}</span><p><?= contact_html($o['email']) ?></p></div>
                            </div>
                        </div>
                        <div class="ct-office__actions">
                            <a href="<?= $o['map'] ?>" target="_blank" rel="noopener" class="ct-office__btn ct-office__btn--ghost">View on Map ↗</a>
                            <a href="tel:<?= $o['call'] ?>" class="ct-office__btn ct-office__btn--red">Call Office →</a>
                        </div>
                        <?php if ($o['blocks2']): ?>
                            <hr class="ct-office__rule">
                            <div class="ct-office__cols">
                                <?php foreach ($o['blocks2'] as $b): ?>
                                    <div class="ct-office__block"><span class="ct-office__label"><?= $b[0] ?></span><p><?= map_link($b[1]) ?></p></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="ct-office__note"><span>{!! $T('offices-dark.5') !!}</span><span>{!! $T('offices-dark.6') !!}</span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ============ FAQs ============ -->
        <section class="container ve-faq">
            <h2 class="h2 h2--center">{!! $T('faqs.1') !!}</h2>
            <div class="acc acc--faq" data-accordion>
                <?php foreach ($ct_faqs as $i => $f): ?>
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

    </div>

</main>


@endsection
