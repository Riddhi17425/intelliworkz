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
            @foreach($headerMenu as $group)
                <li class="subnav__item">
                    <button type="button" class="subnav__btn" aria-expanded="false" aria-haspopup="true">{{ $group->title }}<img src="assets/icons/chevron-down.svg" width="9" height="5" alt=""></button>
                    <ul class="dropdown">
                        @foreach($group->items as $item)
                            <li><a href="{{ $item->href() }}"@if($item->new_tab) target="_blank" rel="noopener"@endif>{{ $item->label }}</a></li>
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
    <div class="menu-drawer__grid">
        {{-- Industries column hidden for now: add 'industries' back after 'services' to show it again --}}
        @foreach(['services', 'products', 'company', 'insights'] as $handle)
            @php $col = $footerMenu[$handle] ?? null; @endphp
            @if($col)
                <div class="menu-drawer__col">
                    <h3>{{ $col->title }}</h3>
                    <ul>
                        @foreach($col->items as $item)
                        @php $sub = $handle === 'services' ? ($footerMenu['cat-' . ($loop->iteration)] ?? null) : null; @endphp
                            @if($sub && $sub->items->count())
                            <li class="has-sub">
                                <a href="{{ $item->href() }}" data-sub-toggle aria-expanded="false">{{ $item->label }}</a>
                                <ul class="menu-drawer__sub">
                                    @foreach($sub->items as $si)<li><a href="{{ $si->href() }}">{{ $si->label }}</a></li>@endforeach
                                    <li class="menu-drawer__all"><a href="{{ $item->href() }}">View all {{ $item->label }} &rarr;</a></li>
                                </ul>
                            </li>
                            @else
                            <li><a href="{{ $item->href() }}"@if($item->new_tab) target="_blank" rel="noopener"@endif>{{ $item->label }}</a></li>
                            @endif
                        @endforeach
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
