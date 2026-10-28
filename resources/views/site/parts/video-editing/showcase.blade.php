@php
$vi = 'assets/images/video/';   // page images
$vc = 'assets/icons/video/';    // page icons
@endphp
    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
$showcase_lead = $T('dark-showcase-shared.2') ?: null;
    $showcase_decor = [
        ['src' => $vi . 'floor-glow.png',      'style' => 'left:1145.53px;top:508.46px;width:969.91px;height:599.17px;opacity:.5'],
        ['src' => $vi . 'showcase-deco.png',   'style' => 'left:-111.5px;top:766px;width:699.31px;height:208.5px;opacity:.4'],
    ];
@endphp
    @include('site.sections.showcase')
