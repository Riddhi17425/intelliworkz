@php
$mi = 'assets/images/magazine/';   // page images
$mc = 'assets/icons/magazine/';    // page icons
$vi = 'assets/images/video/';      // images shared with the video page (offer cards)
$vc = 'assets/icons/video/';       // icons shared with the video page
@endphp
    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
$showcase_lead = $T('dark-showcase-shared.2') ?: null;
@endphp
    @include('site.sections.showcase')
