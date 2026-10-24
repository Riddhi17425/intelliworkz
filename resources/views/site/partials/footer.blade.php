@php
    $showPrefooter = $cfg['show_prefooter'] ?? true;
    $footerClass = $cfg['footer_class'] ?? '';
    $phoneIcon = $cfg['phone_icon'] ?? 'icon-phone.svg';
    $column = function ($handle) use ($footerMenu) { return $footerMenu[$handle] ?? null; };
@endphp
@if($showPrefooter)
<section class="pre-footer">
    <div class="pre-footer__inner">
        <div class="pre-footer__head">
            <h2 class="pre-footer__title">{!! $G('footer.1') !!}</h2>
            <p class="pre-footer__text">{!! $G('footer.2') !!}</p>
        </div>
        <div class="pre-footer__actions">
            <a href="{{ $G('footer.4', '#') ?: '#' }}" class="btn btn--cta">{!! $G('footer.3') !!}</a>
            <a href="{{ $G('footer.6', '#') ?: '#' }}" class="btn btn--cta btn--ghost">{!! $G('footer.5') !!}</a>
        </div>
    </div>
</section>
@endif

<footer class="site-footer {{ $footerClass }}">
    <div class="site-footer__inner">
        <div class="site-footer__top">
            <div class="footer-brand">
                <div class="footer-brand__about">
                    <img src="{{ $settings['logo_footer'] ?? 'assets/icons/footer-logo.svg' }}" width="381" height="80" alt="{{ $settings['company_name'] ?? 'Intelliworkz' }}">
                    <p>{!! nl2br(e($settings['footer_about'] ?? '')) !!}</p>
                    <div class="footer-social">
                        @if(!in_array(trim($settings['social_facebook'] ?? ''), ['', '#'], true))<a href="{{ $settings['social_facebook'] }}" aria-label="Facebook" target="_blank" rel="noopener"><img src="assets/icons/social-facebook.svg" width="32" height="32" alt=""></a>@endif
                        @if(!in_array(trim($settings['social_instagram'] ?? ''), ['', '#'], true))<a href="{{ $settings['social_instagram'] }}" aria-label="Instagram" target="_blank" rel="noopener"><img src="assets/icons/social-2.svg" width="32" height="32" alt=""></a>@endif
                        @if(!in_array(trim($settings['social_linkedin'] ?? ''), ['', '#'], true))<a href="{{ $settings['social_linkedin'] }}" aria-label="LinkedIn" target="_blank" rel="noopener"><img src="assets/icons/social-3.svg" width="32" height="32" alt=""></a>@endif
                    </div>
                </div>
                <hr class="footer-brand__line">
                <div class="footer-iso">
                    <img src="{{ $settings['iso_image'] ?? 'assets/icons/iso.svg' }}" width="78" height="80" alt="ISO 27001">
                    <p>{!! $G('footer.7') !!}</p>
                </div>
                {{-- Quick Links column hidden for now: restore this line to show it again
                @include('site.partials.footer-col', ['col' => $column('company')])
                --}}
            </div>

            <div class="footer-links">
                {{-- Industries column hidden for now: restore these two lines to show it again
                @include('site.partials.footer-col', ['col' => $column('industries')])
                <span class="v-line"></span>
                --}}
                @foreach([1, 2, 3, 4, 5] as $n)
                    @if($n > 1)<span class="v-line"></span>@endif
                    @include('site.partials.footer-col', ['col' => $column('cat-' . $n)])
                @endforeach
            </div>
        </div>

        <div class="offices">
            @foreach($offices as $o)
                <article class="office">
                    <img class="office__map" src="<?= iu('assets/images/', $o['map'] ?? '') ?>" alt="" style="opacity:{{ $o['map_opacity'] ?? '.4' }}">
                    <div class="office__body">
                        <div class="office__row">
                            <img src="<?= iu('assets/icons/', $o['flag'] ?? '') ?>" width="70" height="70" alt="">
                            <div class="office__info">
                                <h3>{!! $o['name'] ?? '' !!}</h3>
                                <p>{!! map_link($o['address'] ?? '') !!}</p>
                            </div>
                        </div>
                        <div class="office__phone">
                            <img src="assets/icons/{{ $phoneIcon }}" width="40" height="40" alt="">
                            <span>{!! contact_html($o['phone'] ?? '') !!}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="bottom-bar">
            <p>{{ $settings['copyright'] ?? '' }}</p>
            <ul class="bottom-bar__links">
                @php $policies = optional($footerMenu['policies'] ?? null)->items ?? collect(); @endphp
                @foreach($policies as $i => $item)
                    @if($i)<li class="v-line v-line--sm"></li>@endif
                    <li><a href="{{ $item->href() }}">{{ $item->label }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>
