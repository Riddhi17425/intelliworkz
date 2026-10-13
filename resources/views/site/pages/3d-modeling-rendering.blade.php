@extends('site.layout')

@section('content')
@php
$ti = 'assets/images/3d/';   // page images
$tc = 'assets/icons/3d/';    // page icons
$vi = 'assets/images/video/'; // shared with video/magazine pages (offer + engagement images)
$vc = 'assets/icons/video/';  // shared icons
$tm = 'assets/models/3d/';    // 3D model files (.glb)
@endphp

<main class="page ve td{{ !empty($mainExtra) ? ' ' . $mainExtra : '' }}">

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

    {{-- the other sections live in resources/views/site/parts/3d-modeling-rendering; the admin decides which ones are shown and in what order --}}
    @foreach($sectionPlan as $part)
        @include($part['view'], $part['data'])
    @endforeach

</main>


@endsection
