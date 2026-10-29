/* Motion layer (vanilla JS): scroll reveals, hero entrance, scroll progress bar, back-to-top.
   Elements are tagged automatically from the selector lists below, so pages need no extra markup. */
(function () {
    'use strict';

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Scroll progress + back-to-top (every page) ---------- */
    var bar = document.createElement('div');
    bar.className = 'fx-progress';
    bar.setAttribute('aria-hidden', 'true');
    var top = document.createElement('button');
    top.type = 'button';
    top.className = 'fx-top';
    top.setAttribute('aria-label', 'Back to top');
    top.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>';
    document.body.appendChild(bar);
    document.body.appendChild(top);

    var ticking = false;
    var update = function () {
        ticking = false;
        var doc = document.documentElement;
        var max = doc.scrollHeight - window.innerHeight;
        var y = window.pageYOffset || doc.scrollTop;
        bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(y / max, 1) : 0) + ')';
        top.classList.toggle('is-visible', y > 700);
    };
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
    window.addEventListener('resize', update);
    top.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); });
    update();

    /* The About page runs its own reveal system (data-reveal). Everything below is for the other pages. */
    if (reduce || !('IntersectionObserver' in window) || document.body.classList.contains('page-about')) return;

    /* ---------- Hero entrance ---------- */
    var heroSel = '.hero__content > *, .hero__caption, .ve-hero__copy > *, .ve-hero .lead-form, .mf-hero__copy > *, .mf-hero__text > *, .ct-hero__text > *';
    document.querySelectorAll(heroSel).forEach(function (el, i) {
        el.classList.add('fx-hero');
        el.style.setProperty('--fx-d', Math.min(i, 8));
    });

    /* ---------- Scroll reveal ---------- */
    /* [selector, direction]. Anything inside a slider/track is skipped (those move by transform). */
    var groups = [
        ['.h1, .h2, .cta-title, .lead, .section-head, .ve-split__side > *, .split-head__side > *', 'fx-up'],
        ['.metrics, .metric, .ve-stat, .showcase__head, .steps__badge, .step, .whitelabel, .domain-card, .outsource-card, .reason, .process-card', 'fx-up'],
        ['.model, .post, .pillar, .pre-footer__inner > *, .cta-band, .ve-cta, .ve-offers__head, .ve-benefits, .ve-pm', 'fx-up'],
        ['.seo-why__card, .mg-why__card, .td-benefit, .cl-why__card, .pk-why__card, .pp-why__card, .mf-card', 'fx-up'],
        ['.wstep, .seo-step, .td-step, .pk-step, .pp-step, .cl-flow__text, .cl-flow__img, .mg-flow, .pk-flow-head > *', 'fx-up'],
        ['.ve-tool, .mg-tool, .seo-tool, .acc--faq .acc-item, .acc--services .acc-item, .ve-faq > .h2', 'fx-up'],
        ['.ct-form__copy > *, .ct-dark__head > *, .mf-buyers__copy > *, .mf-support__body > *, .mf-dark__head > *, .expertise__media, .pk-pf__head > *, .seo-pf__head > *, .cl-pf__head > *, .ve-portfolio__head > *', 'fx-up'],
        ['.expertise__content > *, .mf-buyers__img, .ct-card, .ct-offices, .ct-dark__map, .seo-pf__panel, .td-viewer__panel', 'fx-up'],
        ['.ve-services__img, .mg-services__img, .td-services__img, .seo-services__img, .pk-services__img, .mf-dark__shot, .seo-pf__main, .ppt-scene__main', 'fx-zoom'],
        // blog pages
        ['.bx-bar, .bx-card, .bx-byline .bx-who, .bx-prose > h2, .bx-prose > p:has(> img:only-child), .bx-topics, .bx-author, .bx-art-foot__share, .bx-band__box, .lg-prose > h2, .lg-help, .lg-tabs .lg-wrap', 'fx-up'],
        ['.bx-cover img', 'fx-zoom']
    ];
    var directional = { '.expertise__media': 'fx-left', '.expertise__content > *': 'fx-right', '.mf-buyers__img': 'fx-right', '.ct-card': 'fx-right', '.ct-offices': 'fx-right', '.ct-form__copy > *': 'fx-left' };
    var skipInside = '[data-showcase], .offer-viewport, [data-slider], .mg-viewport, [data-examples], .pf-cards, .seo-gallery, .td-gallery, .ppt-gallery, .menu-drawer, .video-modal, .site-footer, .products__stage, .ve-hero, .hero';

    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            var el = entry.target;
            io.unobserve(el);
            el.classList.add('fx-in');
            /* hand the element back to its normal CSS once the entrance has played, so hover transitions work again */
            var wait = 1100 + (parseFloat(el.style.getPropertyValue('--fx-d')) || 0) * 85;
            setTimeout(function () {
                el.classList.remove('fx', 'fx-up', 'fx-left', 'fx-right', 'fx-zoom', 'fx-in');
                el.style.removeProperty('--fx-d');
            }, wait);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

    var seen = new Set();
    var count = new Map();
    groups.forEach(function (g) {
        g[0].split(/,\s*(?![^()]*\))/).forEach(function (sel) {
            sel = sel.trim();
            document.querySelectorAll(sel).forEach(function (el) {
                if (seen.has(el) || el.closest(skipInside)) return;
                if (el.offsetParent === null && getComputedStyle(el).position !== 'fixed') return;   // not rendered at this width
                if (getComputedStyle(el).transform !== 'none') return;   // already positioned by a transform (e.g. centred badges)
                seen.add(el);
                /* stagger siblings that match together */
                var parent = el.parentElement;
                var n = count.get(parent) || 0;
                count.set(parent, n + 1);
                el.classList.add('fx', directional[sel] || g[1]);
                el.style.setProperty('--fx-d', Math.min(n, 6));
                io.observe(el);
            });
        });
    });
})();
