@extends('site.layout')

@section('content')
@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
<main class="page ve{{ !empty($mainExtra) ? ' ' . $mainExtra : '' }}">

    <!-- ============ HERO ============ -->
    <section class="ve-hero">
        <img class="ve-hero__bg" src="{{ $T('hero.1') }}" alt="">
        <img class="ve-hero__pattern" src="assets/icons/showcase-bg.svg" alt="">
        <img class="ve-hero__floor" src="{{ $T('hero.2') }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container ve-hero__cols">
                <div class="ve-hero__copy">
                    <p class="breadcrumb">{!! $T('hero.3') !!}</p>

                    <div class="ve-hero__stack">
                        <div class="ve-hero__group">
                        <div class="ve-hero__intro">
                            <h1 class="h1">{!! $T('hero.4') !!}</h1>
                            <p>{!! $T('hero.5') !!}</p>
                        </div>
                        <ul class="ve-benefits">
                            <?php foreach ($ve_benefits as $b): ?><li><?= $b ?></li><?php endforeach; ?>
                        </ul>
                        </div>
                        <div class="ve-pm">
                            <span class="ve-pm__icon"><img src="<?= $vc ?>pm-users.svg" width="24" height="24" alt=""></span>
                            <div>
                                <strong>{!! $T('hero.6') !!}</strong>
                                <span>{!! $T('hero.7') !!}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form class="lead-form" action="{{ route('inquiries.store') }}" method="post" data-inquiry-form novalidate>
@csrf
<input type="hidden" name="source" value="{{ $page->slug }}">
<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
                    <div class="lead-form__head">
                        <h2>{!! $T('hero.8') !!}</h2>
                        <p>{!! $T('hero.9') !!}</p>
                    </div>
                    <div class="lead-form__fields">
                        <?php foreach (array_chunk($ve_form_fields, 2) as $row): ?>
                            <div class="lead-form__row">
                                <?php foreach ($row as $f): ?>
                                    <label class="field"><span><?= $f[0] ?></span><input type="<?= $f[2] ?>" name="<?= ($f[3] ?? strtolower(str_replace(' ', '_', $f[0]))) ?>" placeholder="<?= $f[1] ?>"></label>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <label class="field"><span>{!! $T('hero.10') !!}</span><input type="text" name="subject" placeholder="{{ $G('form.2', 'Enter your subject') }}"></label>
                        <label class="field"><span>{!! $T('hero.11') !!}</span><textarea name="message" rows="1" placeholder="{{ $G('form.3', 'Tell us about your requirements') }}"></textarea></label>
                    </div>
                    <p class="form-status" data-form-status role="status" aria-live="polite"></p>
                    <button type="submit" class="btn btn--cta btn--block">{!! $T('hero.12') !!}</button>
                    <p class="lead-form__privacy"><img src="<?= $vc ?>lock.svg" width="14" height="14" alt="">{{ $G('form.1', 'We respect your privacy. Your information is safe with us.') }}</p>
                </form>
            </div>
        </div>
    </section>

    {{-- the other sections live in resources/views/site/parts/video-editing; the admin decides which ones are shown and in what order --}}
    @foreach($sectionPlan as $part)
        @include($part['view'], $part['data'])
    @endforeach

</main>

<!-- Video popup -->
<div class="video-modal" id="video-modal" role="dialog" aria-modal="true" aria-label="Video player" hidden>
    <div class="video-modal__backdrop" data-video-close></div>
    <button type="button" class="video-modal__nav video-modal__nav--prev" aria-label="Previous video" data-video-nav="-1" hidden><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    <button type="button" class="video-modal__nav video-modal__nav--next" aria-label="Next video" data-video-nav="1" hidden><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
    <div class="video-modal__box">
        <button type="button" class="video-modal__close" aria-label="Close video" data-video-close>&times;</button>
        <div class="video-modal__title"></div>
        <div class="video-modal__stage"></div>
        <div class="video-modal__count" aria-live="polite"></div>
    </div>
</div>


@endsection
