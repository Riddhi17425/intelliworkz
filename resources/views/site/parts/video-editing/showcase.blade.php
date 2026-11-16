@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
$showcase_lead = $T('dark-showcase-shared.2') ?: null;
    $showcase_decor = [
        // film strip: full width along the bottom, in its own proportions, fading out upwards and at both ends (no hard edges behind the cards)
        ['src' => $vi . 'floor-glow.png',      'class' => 'showcase__decor--film', 'style' => 'left:0;bottom:0;width:1920px;height:auto;opacity:.22'],
        // sound wave: soft, centred behind the dots
        ['src' => $vi . 'showcase-deco.png',   'class' => 'showcase__decor--wave', 'style' => 'left:50%;bottom:6px;width:1100px;height:auto;margin-left:-550px;opacity:.28'],
    ];
@endphp
    @include('site.sections.showcase')
