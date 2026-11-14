<header class="site-header">
    <div class="topbar">
        <div class="container topbar__inner">
            <button class="menu-toggle" type="button" aria-controls="site-menu" aria-expanded="false" data-menu-open>
                <img src="assets/icons/menu.svg" width="16" height="16" alt="">
                <span>{!! $G('header.1') !!}</span>
            </button>
            <a href="{{ url('/') }}" class="logo"><img src="{{ $settings['logo_header'] ?? 'assets/icons/logo.svg' }}" width="297" height="64" alt="{{ $settings['company_name'] ?? 'Intelliworkz' }}"></a>
            <?php btn($G('header.2'), 'btn--header', 'arrow-right.svg', $G('header.3', '#')); ?>
        </div>
    </div>
    <nav class="subnav">
        <ul class="container subnav__list" data-subnav>
            @php
                // the page being viewed: its menu group and its link are marked active (links to a spot on another page, like services#svc-1, never count)
                // menu links come as "logo-design.php", "/logo-design" or a full address on this site: compare the bare page name
                $bare = function (string $path): string {
                    $p = preg_replace('~\.php$~i', '', trim($path, '/'));
                    return $p === 'index' ? '' : strtolower($p);
                };
                $hereNow = $bare(request()->path());
                $isHere = function ($item) use ($hereNow, $bare) {
                    $href = (string) $item->href();
                    if ($href === '' || str_contains($href, '#')) return false;
                    $host = parse_url($href, PHP_URL_HOST);
                    if ($host && $host !== request()->getHost()) return false;
                    return $bare((string) parse_url($href, PHP_URL_PATH)) === $hereNow;
                };
            @endphp
            @foreach($headerMenu as $group)
                @php $groupHere = $group->items->contains(fn ($i) => $isHere($i)); @endphp
                <li class="subnav__item{{ $groupHere ? ' is-current' : '' }}">
                    <button type="button" class="subnav__btn" aria-expanded="false" aria-haspopup="true">{{ $group->title }}<img src="assets/icons/chevron-down.svg" width="9" height="5" alt=""></button>
                    <ul class="dropdown">
                        @foreach($group->items as $item)
                            <li><a href="{{ $item->href() }}"@if($isHere($item)) class="is-current" aria-current="page"@endif @if($item->new_tab) target="_blank" rel="noopener"@endif>{{ $item->label }}</a></li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>
    </nav>
</header>

<!-- Slide-out menu -->
<div class="menu-backdrop" data-menu-close hidden></div>
<aside class="menu-drawer" id="site-menu" aria-label="Site menu" aria-hidden="true">
    <div class="menu-drawer__top">
        <a href="{{ url('/') }}"><img src="{{ $settings['logo_header'] ?? 'assets/icons/logo.svg' }}" width="198" height="43" alt="{{ $settings['company_name'] ?? 'Intelliworkz' }}"></a>
        <button type="button" class="menu-drawer__close" aria-label="Close menu" data-menu-close>&times;</button>
    </div>
    @php
        // a small line icon in front of every top-level drawer entry, picked from words in its label (admin-added entries get one too; anything unknown gets the arrow)
        $drawerIcon = function (string $label): string {
            $icons = [
                'home' => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
                'about' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18.5 20a6.5 6.5 0 0 0-3-5.5"/>',
                'blog' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/>',
                'contact' => '<rect x="2.5" y="4.5" width="19" height="15" rx="2"/><path d="m3 6 9 7 9-7"/>',
                'brand' => '<path d="M12 2.5a9.5 9.5 0 1 0 0 19c1 0 1.6-.7 1.6-1.6 0-.5-.2-.8-.4-1.1-.3-.3-.4-.6-.4-1.1 0-.9.7-1.6 1.6-1.6h1.9a5.3 5.3 0 0 0 5.2-5.3c0-4.6-4.3-8.3-9.5-8.3z"/><circle cx="7.5" cy="11.5" r="1"/><circle cx="10" cy="7" r="1"/><circle cx="15" cy="7.5" r="1"/>',
                'market' => '<path d="m2.5 17 6.5-6.5 4.5 4.5 8-8"/><path d="M16 7h5.5v5.5"/>',
                'print' => '<path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2zM22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/>',
                'post' => '<rect x="2.5" y="2.5" width="19" height="19" rx="2"/><path d="M7.5 2.5v19M16.5 2.5v19M2.5 12h19M2.5 7.5h5M2.5 16.5h5M16.5 7.5h5M16.5 16.5h5"/>',
                'anim' => '<circle cx="12" cy="12" r="9.5"/><path d="m10 8.5 5.5 3.5-5.5 3.5z"/>',
                'software' => '<rect x="3" y="3" width="7.5" height="9" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="5" rx="1.5"/><rect x="13.5" y="11" width="7.5" height="10" rx="1.5"/><rect x="3" y="15" width="7.5" height="6" rx="1.5"/>',
                'career' => '<rect x="2.5" y="7" width="19" height="13.5" rx="2"/><path d="M8.5 7V5a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v2M2.5 12.5h19"/>',
                'case' => '<rect x="2.5" y="7" width="19" height="13.5" rx="2"/><path d="M8.5 7V5a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v2M2.5 12.5h19"/>',
                'industr' => '<path d="M2.5 21V10l6 3.5V10l6 3.5V5h3l1 6h3v10z"/>',
            ];
            $label = strtolower(strip_tags($label));
            $path = '<path d="M5 12h14M13 6l6 6-6 6"/>';
            foreach ($icons as $word => $svg) {
                if (str_contains($label, $word)) { $path = $svg; break; }
            }
            return '<span class="menu-drawer__ic" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg></span>';
        };
    @endphp
    <div class="menu-drawer__grid">
        {{-- Industries column hidden for now: add 'industries' back after 'services' to show it again --}}
        {{-- Products sits inside the Services column as its last fold-out entry (see below), not as a column of its own --}}
        @foreach(['services', 'company', 'insights'] as $handle)
            @php $col = $footerMenu[$handle] ?? null; @endphp
            @if($col)
                <div class="menu-drawer__col">
                    <h3>{{ $col->title }}</h3>
                    <ul>
                        @foreach($col->items as $item)
                        @php $sub = $handle === 'services' ? ($footerMenu['cat-' . ($loop->iteration)] ?? null) : null; @endphp
                            @if($sub && $sub->items->count())
                            @php $subHere = $sub->items->contains(fn ($i) => $isHere($i)); @endphp
                            {{-- the section holding the page being viewed starts open, with that page marked --}}
                            <li class="has-sub{{ $subHere ? ' is-open is-current' : '' }}">
                                <a href="{{ $item->href() }}" data-sub-toggle aria-expanded="{{ $subHere ? 'true' : 'false' }}"><span class="menu-drawer__lbl">{!! $drawerIcon($item->label) !!}{{ $item->label }}</span></a>
                                <ul class="menu-drawer__sub">
                                    @foreach($sub->items as $si)<li><a href="{{ $si->href() }}"@if($isHere($si)) class="is-current" aria-current="page"@endif>{{ $si->label }}</a></li>@endforeach
                                    <li class="menu-drawer__all"><a href="{{ $item->href() }}">View all {{ $item->label }} &rarr;</a></li>
                                </ul>
                            </li>
                            @else
                            <li><a href="{{ $item->href() }}"@if($isHere($item)) class="is-current" aria-current="page"@endif @if($item->new_tab) target="_blank" rel="noopener"@endif><span class="menu-drawer__lbl">{!! $drawerIcon($item->label) !!}{{ $item->label }}</span></a></li>
                            @endif
                        @endforeach
                        @php $products = $handle === 'services' ? ($footerMenu['products'] ?? null) : null; @endphp
                        @if($products && $products->items->count())
                            @php $prodHere = $products->items->contains(fn ($i) => $isHere($i)); @endphp
                            <li class="has-sub{{ $prodHere ? ' is-open is-current' : '' }}">
                                <a href="{{ $products->items->first()->href() }}" data-sub-toggle aria-expanded="{{ $prodHere ? 'true' : 'false' }}"><span class="menu-drawer__lbl">{!! $drawerIcon($products->title) !!}{{ $products->title }}</span></a>
                                <ul class="menu-drawer__sub">
                                    @foreach($products->items as $si)<li><a href="{{ $si->href() }}"@if($isHere($si)) class="is-current" aria-current="page"@endif @if($si->new_tab) target="_blank" rel="noopener"@endif>{{ $si->label }}</a></li>@endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        @endforeach
    </div>
    <div class="menu-drawer__bottom">
        <?php btn($G('header.4'), 'btn--cta', null, $G('header.3', '#')); ?>
        <div class="menu-drawer__social">
            <span>Follow us</span>
            @foreach(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn'] as $k => $label)
                @if(!empty($settings['social_' . $k]))<a href="{{ $settings['social_' . $k] }}" target="_blank" rel="noopener">{{ $label }}</a>@endif
            @endforeach
        </div>
    </div>
</aside>
