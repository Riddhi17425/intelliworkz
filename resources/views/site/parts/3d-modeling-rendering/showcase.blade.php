@php
$ti = 'assets/images/3d/';   // page images
$tc = 'assets/icons/3d/';    // page icons
$vi = 'assets/images/video/'; // shared with video/magazine pages (offer + engagement images)
$vc = 'assets/icons/video/';  // shared icons
$tm = 'assets/models/3d/';    // 3D model files (.glb)
@endphp
    <!-- ============ DARK SHOWCASE (shared) ============ -->
    @php
$showcase_title = $T('dark-showcase-shared.1');
$showcase_lead = $T('dark-showcase-shared.2') ?: null;
@endphp
    @include('site.sections.showcase')
