@php
$si = 'assets/images/seo/';    // page images
$sc = 'assets/icons/seo/';     // page icons
$vi = 'assets/images/video/';  // shared with video/magazine pages (offer image 4)
$vc = 'assets/icons/video/';   // shared icons
@endphp
    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
$showcase_lead = $T('dark-showcase-shared.2') ?: null;
@endphp
    @include('site.sections.showcase')
