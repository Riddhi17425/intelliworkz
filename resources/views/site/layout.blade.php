<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $meta['title'] }}</title>
    @if($meta['description'])<meta name="description" content="{{ $meta['description'] }}">@endif
    @if($meta['keywords'])<meta name="keywords" content="{{ $meta['keywords'] }}">@endif
    @if($meta['robots'])<meta name="robots" content="{{ $meta['robots'] }}">@endif
    <link rel="canonical" href="{{ $meta['canonical'] }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $settings['company_name'] ?? 'Intelliworkz' }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    @if($meta['description'])<meta property="og:description" content="{{ $meta['description'] }}">@endif
    <meta property="og:url" content="{{ $meta['canonical'] }}">
    @if($meta['image'])<meta property="og:image" content="{{ $meta['image'] }}">@endif

    <link rel="icon" type="image/png" sizes="100x100" href="{{ $settings['favicon'] ?? 'assets/images/favicon.png' }}">
    <link rel="apple-touch-icon" href="{{ $settings['favicon'] ?? 'assets/images/favicon.png' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@500&family=Inter+Tight:wght@400;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @unless($cfg['no_zoom'] ?? false)
    <script>
    /* Desktop scaling: these pages are built on a 1920px canvas. From 1200px to 1919px the whole canvas is scaled
       proportionally (CSS zoom), so the design stays exactly as drawn. Below 1200px the stacked layout in
       responsive.css takes over. --z is exposed for the few rules that need the scale (viewport units, fixed overlays). */
    (function () {
        var d = document.documentElement;
        function fit() {
            var w = window.innerWidth, k = (w >= 1200 && w < 1920) ? w / 1920 : 1;
            d.style.zoom = k === 1 ? '' : k;
            d.style.setProperty('--z', k);
        }
        fit();
        window.addEventListener('resize', fit);
    })();
    </script>
    @endunless
    <link rel="stylesheet" href="{{ asset_url('assets/css/style.css') }}">
    @foreach(($cfg['extra_css'] ?? []) as $css)<link rel="stylesheet" href="{{ asset_url($css) }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset_url('assets/css/effects.css') }}">
    <link rel="stylesheet" href="{{ asset_url('assets/css/responsive.css') }}">
</head>
<body class="{{ $cfg['body_class'] ?? '' }}">
@if($preview ?? false)
    <div style="position:fixed;z-index:99999;left:50%;bottom:16px;transform:translateX(-50%);padding:8px 16px;font:600 13px/1.4 Inter,sans-serif;color:#fff;background:#C42030;border-radius:30px;box-shadow:0 8px 24px rgba(0,0,0,.3)">
        Draft preview — visitors cannot see this page yet ·
        <a href="{{ route('admin.pages.edit', $page) }}" style="color:#fff;text-decoration:underline">Back to admin</a>
    </div>
@endif

{{-- result of an enquiry sent without JavaScript --}}
@if(session('inquiry_status'))
    <div id="form-result" class="form-flash" role="status">{{ session('inquiry_status') }}</div>
@elseif($errors->any() && old('source') !== null)
    <div id="form-result" class="form-flash is-error" role="alert">{{ $errors->first() }}</div>
@endif

@yield('content')

@include('site.partials.footer')

<script src="{{ asset_url('assets/js/main.js') }}"></script>
<script src="{{ asset_url('assets/js/effects.js') }}" defer></script>
<script src="{{ asset_url('assets/js/inquiry.js') }}" defer></script>
</body>
</html>
