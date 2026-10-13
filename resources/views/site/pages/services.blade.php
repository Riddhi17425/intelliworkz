@extends('site.layout')

@section('content')
@php
$ic = url('assets/icons') . '/';
// small outline icons already used elsewhere on the site, repeated through the lists
$rowIcons = ['card-clipboard.svg', 'card-check.svg', 'card-search.svg', 'card-users.svg', 'card-chart.svg', 'card-code.svg', 'icon-briefcase.svg', 'icon-globe.svg', 'icon-send.svg', 'icon-users.svg'];
$parse = function ($line) {
    [$label, $href] = array_pad(array_map('trim', explode('|', (string) $line, 2)), 2, '');
    return [$label, $href !== '' ? $href : 'contact-us.php'];
};
@endphp
<main class="page ve svc">

    <!-- ============ HERO ============ -->
    <section class="ve-hero ve-hero--plain svc-hero">
        <img class="svc-hero__bg" src="{{ iu('assets/images/', $T('hero.1')) }}" alt="">

        <div class="ve-hero__content">
            @include('site.partials.site-header')

            <div class="container svc-hero__copy">
                <p class="breadcrumb breadcrumb--light">{!! $T('hero.2') !!}</p>
                <div class="svc-hero__text">
                    <h1 class="h1">{!! $T('hero.3') !!}</h1>
                    <p>{!! $T('hero.4') !!}</p>
                </div>
                <div class="svc-hero__btns">
                    <?php btn($T('hero.5'), 'btn--cta', 'arrow-link.svg', $T('hero.6', '#')); ?>
                    <?php btn($T('hero.7'), 'btn--cta btn--ghost', 'chevron-down.svg', $T('hero.8', '#')); ?>
                </div>
            </div>
            <p class="svc-hero__words">{!! $T('hero.9') !!}</p>
        </div>
    </section>

    <!-- ============ TAB BAR ============ -->
    <div class="container svc-tabs-wrap">
        <nav class="svc-tabs" aria-label="Service groups" data-svc-tabs>
            <?php foreach ($svc_sections as $i => $s): ?>
                <a href="#svc-<?= $i + 1 ?>" class="svc-tab <?= $i === 0 ? 'is-active' : '' ?>">
                    <span class="svc-ico" style="--ico: url('<?= $ic . ($s['ico'] ?? 'icon-briefcase.svg') ?>')"></span>
                    <span><?= $s['tab'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- ============ SERVICE GROUPS ============ -->
    <div class="svc-body">
        <?php foreach ($svc_sections as $i => $s):
            $items = array_values(array_filter((array) ($s['items'] ?? []), fn ($x) => trim((string) $x) !== ''));
            $cols = max(1, (int) ($s['cols'] ?? 2));
            $cards = ($s['layout'] ?? 'rows') === 'cards';
            $rows = (int) ceil(count($items) / $cols);
        ?>
            <section class="svc-block" id="svc-<?= $i + 1 ?>" style="--c: <?= $s['c'] ?? 'var(--c-red)' ?>">
                <div class="svc-block__banner">
                    <img class="svc-block__img" src="<?= iu('assets/images/', $s['img'] ?? '') ?>" alt="">
                    <div class="container svc-block__copy">
                        <div class="svc-block__tag"><span class="svc-block__no"><?= $s['no'] ?? sprintf('%02d', $i + 1) ?></span><span><?= $s['tag'] ?? '' ?></span></div>
                        <h2 class="h2"><?= $s['title'] ?></h2>
                        <p><?= $s['text'] ?></p>
                        <div><a href="<?= e(($s['href'] ?? '') ?: 'contact-us.php') ?>" class="svc-block__btn"><?= $s['btn'] ?? 'Explore Services' ?></a></div>
                    </div>
                </div>

                <div class="container">
                    <ul class="svc-list <?= $cards ? 'svc-list--cards' : 'svc-list--rows' ?>" style="--cols: <?= $cols ?>; --rows: <?= $rows ?>">
                        <?php foreach ($items as $k => $line): [$label, $href] = $parse($line);
                            $first = $cards ? false : ($k % $rows === 0); ?>
                            <li class="<?= $first ? 'is-first' : '' ?>">
                                <a href="<?= e($href) ?>" class="svc-item">
                                    <span class="svc-ico svc-item__ico" style="--ico: url('<?= $ic . $rowIcons[$k % count($rowIcons)] ?>')"></span>
                                    <span class="svc-item__label"><?= e($label) ?></span>
                                    <span class="svc-ico svc-item__go" style="--ico: url('<?= $ic ?>arrow-link.svg')"></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endforeach; ?>
    </div>

    <!-- ============ CALL TO ACTION ============ -->
    <section class="cta-band svc-cta">
        <img class="cta-band__grid" src="assets/icons/grid-lines.svg" alt="">
        <div class="cta-band__copy">
            <p>{!! $T('cta.1') !!}</p>
            <h2 class="cta-title">{!! $T('cta.2') !!}</h2>
        </div>
        <div class="svc-cta__btns">
            <?php btn($T('cta.3'), 'btn--cta', 'arrow-cta.svg', $T('cta.4', '#')); ?>
            <?php btn($T('cta.5'), 'btn--cta btn--ghost', null, $T('cta.6', '#')); ?>
        </div>
    </section>

</main>

@push('scripts')
<script>
/* Services: the tab bar highlights the group that is on screen. */
(function () {
    var tabs = document.querySelectorAll('[data-svc-tabs] .svc-tab');
    var blocks = document.querySelectorAll('.svc-block');
    if (!tabs.length || !('IntersectionObserver' in window)) return;
    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            tabs.forEach(function (t) { t.classList.toggle('is-active', t.getAttribute('href') === '#' + e.target.id); });
        });
    }, { rootMargin: '-35% 0px -55% 0px' });
    blocks.forEach(function (b) { io.observe(b); });
})();
</script>
@endpush
@endsection
