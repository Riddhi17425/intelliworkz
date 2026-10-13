/* Intelliworkz – homepage interactions (vanilla JS, no dependencies) */
(function () {
    'use strict';

    /* Industries slider: pagination dots scroll the card track (4 "slides") */
    document.querySelectorAll('[data-slider]').forEach(function (slider) {
        var track = slider.querySelector('[data-slider-track]');
        var dots = slider.querySelectorAll('.dots__dot');
        if (!track || !dots.length) return;

        var max = function () { return track.scrollWidth - track.clientWidth; };
        var setActive = function (i) {
            dots.forEach(function (d, n) { d.classList.toggle('is-active', n === i); });
        };
        var current = function () {
            return max() > 0 ? Math.round((track.scrollLeft / max()) * (dots.length - 1)) : 0;
        };

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                track.scrollTo({ left: (max() / (dots.length - 1)) * i, behavior: 'smooth' });
                setActive(i);
            });
        });
        track.addEventListener('scroll', function () { setActive(current()); }, { passive: true });

        /* Autoplay: advance one "slide" every few seconds, loop at the end; pauses on hover/focus/touch */
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var delay = parseInt(slider.getAttribute('data-autoplay'), 10) || 3500;
        var timer = null, paused = false;
        var next = function () {
            if (paused || document.hidden) return;
            var i = current() + 1;
            if (i >= dots.length) i = 0;
            track.scrollTo({ left: (max() / (dots.length - 1)) * i, behavior: 'smooth' });
        };
        var play = function () { stop(); if (!reduce) timer = setInterval(next, delay); };
        var stop = function () { clearInterval(timer); timer = null; };
        ['mouseenter', 'focusin', 'touchstart', 'pointerdown'].forEach(function (ev) {
            slider.addEventListener(ev, function () { paused = true; });
        });
        ['mouseleave', 'focusout', 'touchend'].forEach(function (ev) {
            slider.addEventListener(ev, function () { paused = false; });
        });
        play();

        /* Figma shows slide 3 active on load */
        var start = slider.hasAttribute('data-start') ? parseInt(slider.getAttribute('data-start'), 10) || 0 : Math.min(2, dots.length - 1);
        track.scrollLeft = (max() / (dots.length - 1)) * start;
        setActive(start);
    });

    /* Home services: the row shows as many cards as fit, the rest slide in (auto-advance + clickable dots) */
    document.querySelectorAll('[data-cards-slider]').forEach(function (slider) {
        var track = slider.querySelector('[data-cards-track]');
        var dotsWrap = slider.querySelector('[data-cards-dots]');
        var cards = track ? track.children : [];
        if (!track || !dotsWrap || cards.length < 2) return;

        var dots = [], count = 0, paused = false, timer = null;
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var max = function () { return Math.max(0, track.scrollWidth - track.clientWidth); };
        var left = function (i) { return count > 1 ? (max() / (count - 1)) * i : 0; };
        var current = function () { return count > 1 && max() > 0 ? Math.round((track.scrollLeft / max()) * (count - 1)) : 0; };
        var mark = function (i) { dots.forEach(function (d, n) { d.classList.toggle('is-active', n === i); }); };
        var go = function (i) { track.scrollTo({ left: left(i), behavior: 'smooth' }); mark(i); };

        /* one dot per position the row can rest at: cards that do not fit = extra positions */
        var build = function () {
            var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
            var visible = Math.max(1, Math.floor((track.clientWidth + gap + 1) / (cards[0].offsetWidth + gap)));
            count = max() > 2 ? Math.min(cards.length - visible + 1, cards.length) : 1;
            dotsWrap.innerHTML = '';
            dots = [];
            dotsWrap.hidden = count < 2;
            for (var i = 0; i < count; i++) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'dots__dot';
                b.setAttribute('aria-label', 'Show services group ' + (i + 1));
                (function (n) { b.addEventListener('click', function () { go(n); }); })(i);
                dotsWrap.appendChild(b);
                dots.push(b);
            }
            mark(current());
        };

        track.addEventListener('scroll', function () { mark(current()); }, { passive: true });
        window.addEventListener('resize', build);

        var next = function () { if (!paused && !document.hidden && count > 1) go((current() + 1) % count); };
        ['mouseenter', 'focusin', 'touchstart', 'pointerdown'].forEach(function (ev) { slider.addEventListener(ev, function () { paused = true; }); });
        ['mouseleave', 'focusout', 'touchend'].forEach(function (ev) { slider.addEventListener(ev, function () { paused = false; }); });
        if (!reduce) timer = setInterval(next, parseInt(slider.getAttribute('data-autoplay'), 10) || 4500);

        build();
    });

    /* Products: tabs switch the DataNote / Formezy / Docezy panel */
    document.querySelectorAll('[data-products]').forEach(function (stage) {
        var tabs = stage.querySelectorAll('.tab');
        var panels = stage.querySelectorAll('.product');
        var idx = 0;

        var show = function (i) {
            idx = (i + tabs.length) % tabs.length;
            tabs.forEach(function (t, n) {
                t.classList.toggle('is-active', n === idx);
                t.setAttribute('aria-selected', String(n === idx));
            });
            panels.forEach(function (p, n) {
                p.classList.toggle('is-active', n === idx);
                p.setAttribute('aria-hidden', String(n !== idx));
            });
        };
        tabs.forEach(function (tab, i) {
            tab.addEventListener("click", function () { show(i); });
        });
        stage.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') show(idx + 1);
            if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') show(idx - 1);
        });
    });
})();

/* Header: sub-nav dropdowns + slide-out menu */
(function () {
    'use strict';

    /* Dropdowns – open on hover (desktop) and on click/tap; close on outside click or Esc */
    var items = document.querySelectorAll('[data-subnav] .subnav__item');
    var closeAll = function (except) {
        items.forEach(function (li) {
            if (li === except) return;
            li.classList.remove('is-open');
            li.querySelector('.subnav__btn').setAttribute('aria-expanded', 'false');
        });
    };
    var setOpen = function (li, open) {
        li.classList.toggle('is-open', open);
        li.querySelector('.subnav__btn').setAttribute('aria-expanded', String(open));
    };
    var canHover = window.matchMedia('(hover: hover)').matches;

    items.forEach(function (li) {
        var btn = li.querySelector('.subnav__btn');
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = !li.classList.contains('is-open');
            closeAll(li);
            setOpen(li, open);
        });
        if (canHover) {
            var timer;
            li.addEventListener('mouseenter', function () { clearTimeout(timer); closeAll(li); setOpen(li, true); });
            li.addEventListener('mouseleave', function () { timer = setTimeout(function () { setOpen(li, false); }, 150); });
        }
    });
    document.addEventListener('click', function () { closeAll(); });

    /* Slide-out menu */
    var drawer = document.getElementById('site-menu');
    var backdrop = document.querySelector('.menu-backdrop');
    var toggle = document.querySelector('[data-menu-open]');
    if (!drawer || !backdrop || !toggle) return;

    var setMenu = function (open) {
        if (open) backdrop.hidden = false;
        requestAnimationFrame(function () {
            drawer.classList.toggle('is-open', open);
            backdrop.classList.toggle('is-visible', open);
        });
        if (!open) setTimeout(function () { if (!drawer.classList.contains('is-open')) backdrop.hidden = true; }, 300);
        drawer.setAttribute('aria-hidden', String(!open));
        toggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('menu-locked', open);
        if (open) drawer.querySelector('.menu-drawer__close').focus(); else toggle.focus();
    };

    toggle.addEventListener('click', function () { closeAll(); setMenu(true); });
    document.querySelectorAll('[data-menu-close]').forEach(function (el) {
        el.addEventListener('click', function () { setMenu(false); });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        closeAll();
        if (drawer.classList.contains('is-open')) setMenu(false);
    });
})();

/* Counters: [data-count] numbers count up once when scrolled into view */
(function () {
    'use strict';

    var counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var easeOut = function (t) { return 1 - Math.pow(1 - t, 3); };

    var run = function (el) {
        var target = parseFloat(el.getAttribute('data-count'));
        var duration = parseInt(el.getAttribute('data-duration'), 10) || 2000;
        var start = null;

        var step = function (now) {
            if (start === null) start = now;
            var p = Math.min((now - start) / duration, 1);
            el.textContent = Math.round(target * easeOut(p));
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    var start = function () {
        /* lock the final width so the layout doesn't jump while digits change. It is measured only once the web fonts are
           ready (a width taken with the fallback font is wrong later), always left-aligned, and measured on the final number */
        counters.forEach(function (el) {
            el.style.display = 'inline-block';
            el.style.textAlign = 'left';
            el.style.minWidth = '';
            el.style.minWidth = el.getBoundingClientRect().width + 'px';
            if (!reduce) el.textContent = '0';
        });
        if (reduce) return;

        if (!('IntersectionObserver' in window)) {
            counters.forEach(function (el) { el.textContent = el.getAttribute('data-count'); });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                io.unobserve(entry.target);
                run(entry.target);
            });
        }, { threshold: 0.4 });
        counters.forEach(function (el) { io.observe(el); });
    };

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(start, start);
    } else {
        start();
    }
})();

/* Showcase slider: centred active card, dots, autoplay, swipe/drag and click-a-side-card */
(function () {
    'use strict';

    var sec = document.querySelector('[data-showcase]');
    if (!sec) return;
    var row = sec.querySelector('[data-showcase-row]');
    var cards = Array.prototype.slice.call(row.children);
    var dots = sec.querySelectorAll('[data-showcase-dots] .dots__dot');
    var n = cards.length;
    var idx = 1;                                  /* Figma: second card centred */
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var offset = function (i) {
        var w = cards[0].offsetWidth;
        var gap = parseFloat(getComputedStyle(row).columnGap) || 56;
        return (sec.clientWidth - w) / 2 - i * (w + gap);
    };
    var render = function (extra) {
        row.style.transform = 'translateX(' + (offset(idx) + (extra || 0)) + 'px)';
        dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); });
        cards.forEach(function (c, i) { c.classList.toggle('is-active', i === idx); });
    };
    var go = function (i) {
        idx = (i + n) % n;
        row.classList.add('is-animated');
        render();
    };

    render();
    requestAnimationFrame(function () { row.classList.add('is-animated'); });
    window.addEventListener('resize', function () { row.classList.remove('is-animated'); render(); });

    dots.forEach(function (d, i) { d.addEventListener('click', function () { go(i); }); });

    /* Autoplay */
    var delay = parseInt(sec.getAttribute('data-autoplay'), 10) || 4500;
    var paused = false;
    if (!reduce) {
        setInterval(function () { if (!paused && !document.hidden) go(idx + 1); }, delay);
    }
    sec.addEventListener('mouseenter', function () { paused = true; });
    sec.addEventListener('mouseleave', function () { paused = false; });

    /* Swipe / drag */
    var startX = null, dx = 0, moved = false;
    row.addEventListener('pointerdown', function (e) {
        if (e.pointerType === 'mouse' && e.button !== 0) return;
        startX = e.clientX; dx = 0; moved = false; paused = true;
        row.classList.remove('is-animated');
        row.classList.add('is-dragging');
    });
    window.addEventListener('pointermove', function (e) {
        if (startX === null) return;
        dx = e.clientX - startX;
        if (Math.abs(dx) > 5) moved = true;
        render(dx);
    });
    window.addEventListener('pointerup', function () {
        if (startX === null) return;
        var d = dx;
        startX = null;
        row.classList.remove('is-dragging');
        row.classList.add('is-animated');
        if (d < -60) go(idx + 1); else if (d > 60) go(idx - 1); else render();
        paused = sec.matches(':hover');
    });

    /* Click a side card to bring it to the centre (and don't follow its link) */
    cards.forEach(function (c, i) {
        c.addEventListener('click', function (e) {
            if (moved) { e.preventDefault(); return; }
            if (i !== idx) { e.preventDefault(); go(i); }
        });
    });
})();

/* Accordions (services list + FAQs): one item open at a time, animated height */
(function () {
    'use strict';

    document.querySelectorAll('[data-accordion]').forEach(function (acc) {
        var items = acc.querySelectorAll('.acc-item');

        /* The open/closed height comes purely from CSS (grid-template-rows 0fr/1fr), so this
           never needs to re-measure — it stays correct even after webfonts swap in and reflow. */
        var setOpen = function (item, open) {
            var head = item.querySelector('.acc-head');
            item.classList.toggle('is-open', open);
            if (head) head.setAttribute('aria-expanded', String(open));
        };

        items.forEach(function (item) {
            var head = item.querySelector('.acc-head');
            if (!head || item.classList.contains('is-static')) return;
            head.addEventListener('click', function () {
                var open = !item.classList.contains('is-open');
                items.forEach(function (other) { if (other !== item) setOpen(other, false); });
                setOpen(item, open);
            });
        });
    });
})();

/* Video portfolio: filter tabs, rotating prev/next slider, click-to-play popup */
(function () {
    'use strict';

    var tabsWrap = document.querySelector('[data-filter-tabs]');
    var track = document.querySelector('[data-pf-track]');
    if (!tabsWrap || !track) return;
    var tabs = tabsWrap.querySelectorAll('.filter-tab');
    var prev = document.querySelector('[data-pf-prev]');
    var next = document.querySelector('[data-pf-next]');
    var busy = false;

    var cards = function () {
        return Array.prototype.filter.call(track.children, function (c) { return !c.hidden; });
    };
    var gap = function () { return parseFloat(getComputedStyle(track).columnGap) || 44; };
    var stepPx = function () { var c = cards()[0]; return c ? c.offsetWidth + gap() : 0; };

    /* Filters */
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-filter');
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            Array.prototype.forEach.call(track.children, function (card) {
                card.hidden = !(key === 'all' || card.getAttribute('data-cats').split(' ').indexOf(key) > -1);
            });
        });
    });

    /* Endless slider: animate the row by one card, then re-order the DOM so it can go on forever */
    var slide = function (dir) {
        var list = cards();
        if (busy || list.length < 2) return;
        busy = true;
        var step = stepPx();
        track.classList.remove('is-sliding');

        if (dir > 0) {
            track.style.transform = 'translateX(0)';
            void track.offsetWidth;
            track.classList.add('is-sliding');
            track.style.transform = 'translateX(' + (-step) + 'px)';
        } else {
            track.insertBefore(list[list.length - 1], list[0]);
            track.style.transform = 'translateX(' + (-step) + 'px)';
            void track.offsetWidth;
            track.classList.add('is-sliding');
            track.style.transform = 'translateX(0)';
        }

        var finished = false;
        var done = function () {
            if (finished) return;
            finished = true;
            track.removeEventListener('transitionend', done);
            track.classList.remove('is-sliding');
            if (dir > 0) track.appendChild(list[0]);
            track.style.transform = 'translateX(0)';
            busy = false;
        };
        track.addEventListener('transitionend', done);
        setTimeout(done, 700);   /* fallback if the transition event never fires */
    };
    if (prev) prev.addEventListener('click', function () { slide(-1); });
    if (next) next.addEventListener('click', function () { slide(1); });

    /* Video popup */
    var modal = document.getElementById('video-modal');
    if (!modal) return;
    var stage = modal.querySelector('.video-modal__stage');
    var titleEl = modal.querySelector('.video-modal__title');
    var lastFocus = null;

    var embedUrl = function (url) {
        var m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([\w-]{6,})/);
        if (m) return 'https://www.youtube.com/embed/' + m[1] + '?autoplay=1&rel=0';
        m = url.match(/vimeo\.com\/(?:video\/)?(\d+)/);
        if (m) return 'https://player.vimeo.com/video/' + m[1] + '?autoplay=1';
        return null;
    };

    var open = function (card) {
        var src = card.getAttribute('data-video');
        if (!src) return;
        lastFocus = document.activeElement;
        titleEl.textContent = card.getAttribute('data-title') || '';
        stage.innerHTML = '';

        var embed = embedUrl(src), el;
        if (embed) {
            el = document.createElement('iframe');
            el.src = embed;
            el.allow = 'autoplay; fullscreen; picture-in-picture';
            el.allowFullscreen = true;
        } else {
            el = document.createElement('video');
            el.src = src;
            el.controls = true;
            el.autoplay = true;
            el.playsInline = true;
        }
        stage.appendChild(el);
        modal.hidden = false;
        document.body.classList.add('modal-open');
        modal.querySelector('.video-modal__close').focus();
    };

    var close = function () {
        if (modal.hidden) return;
        stage.innerHTML = '';                 /* removing the player stops playback */
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        if (lastFocus) lastFocus.focus();
    };

    track.addEventListener('click', function (e) {
        var media = e.target.closest('[data-play]');
        if (media) open(media.closest('.pf-card'));
    });
    modal.querySelectorAll('[data-video-close]').forEach(function (el) { el.addEventListener('click', close); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();

/* "What we offer" slider: cards rotate endlessly, dots show which card leads, autoplay pauses on hover */
(function () {
    'use strict';

    var wrap = document.querySelector('[data-offers]');
    if (!wrap) return;
    var track = wrap.querySelector('[data-offers-track]');
    var dots = document.querySelectorAll('[data-offers-dots] .dots__dot');
    var n = track.children.length;
    var busy = false, paused = false;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var lead = function () { return parseInt(track.children[0].getAttribute('data-i'), 10); };
    var stepPx = function () {
        var gap = parseFloat(getComputedStyle(track).columnGap) || 40;
        return track.children[0].offsetWidth + gap;
    };
    var mark = function (i) { dots.forEach(function (d, k) { d.classList.toggle('is-active', k === i); }); };

    /* move the row forward by k cards, then re-order the DOM so it can go on forever */
    var forward = function (k) {
        if (busy || k < 1) return;
        busy = true;
        var moving = Array.prototype.slice.call(track.children, 0, k);
        mark((lead() + k) % n);
        track.classList.add('is-sliding');
        track.style.transform = 'translateX(' + (-k * stepPx()) + 'px)';

        var finished = false;
        var done = function () {
            if (finished) return;
            finished = true;
            track.removeEventListener('transitionend', done);
            track.classList.remove('is-sliding');
            moving.forEach(function (c) { track.appendChild(c); });
            track.style.transform = 'translateX(0)';
            busy = false;
        };
        track.addEventListener('transitionend', done);
        setTimeout(done, 800);
    };

    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { forward((i - lead() + n) % n); });
    });

    mark(lead());
    if (!reduce) {
        setInterval(function () { if (!paused && !document.hidden) forward(1); }, 4000);
    }
    wrap.addEventListener('mouseenter', function () { paused = true; });
    wrap.addEventListener('mouseleave', function () { paused = false; });
})();

/* "Our Examples" strip (Figma bleed layout): autoplays endlessly, drag/swipe overrides it,
   pauses on hover/focus/touch. The track is rendered twice server-side so there's always a
   next tile queued up. */
(function () {
    'use strict';

    var section = document.querySelector('[data-examples]');
    if (!section) return;
    var viewport = section.querySelector('.mg-viewport');
    var track = section.querySelector('[data-examples-track]');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var busy = false, paused = false;

    var gap = function () { return parseFloat(getComputedStyle(track).columnGap) || 40; };
    var stepPx = function () {
        var first = Array.prototype.filter.call(track.children, function (t) { return !t.hidden; })[0] || track.children[0];
        return first.offsetWidth + gap();
    };

    var forward = function (dir) {
        if (busy) return;
        busy = true;
        var step = stepPx();
        track.classList.add('is-sliding');

        if (dir > 0) {
            track.style.transform = 'translateX(' + (-step) + 'px)';
        } else {
            var last = track.lastElementChild;
            track.insertBefore(last, track.firstElementChild);
            track.style.transform = 'translateX(' + (-step) + 'px)';
            void track.offsetWidth;
            track.classList.add('is-sliding');
            track.style.transform = 'translateX(0)';
        }

        var finished = false;
        var done = function () {
            if (finished) return;
            finished = true;
            track.removeEventListener('transitionend', done);
            track.classList.remove('is-sliding');
            if (dir > 0) track.appendChild(track.firstElementChild);
            track.style.transform = 'translateX(0)';
            busy = false;
        };
        track.addEventListener('transitionend', done);
        setTimeout(done, 700);
    };

    /* Optional header arrows ([data-examples-prev/next]) and filter tabs ([data-filter] + tile data-cats) */
    section.querySelectorAll('[data-examples-prev]').forEach(function (b) { b.addEventListener('click', function () { forward(-1); }); });
    section.querySelectorAll('[data-examples-next]').forEach(function (b) { b.addEventListener('click', function () { forward(1); }); });
    var fTabs = section.querySelectorAll('.filter-tab');
    fTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-filter');
            fTabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            Array.prototype.forEach.call(track.children, function (tile) {
                var cats = (tile.getAttribute('data-cats') || '').split(' ');
                tile.hidden = key !== 'all' && cats.indexOf(key) === -1;
            });
        });
    });

    /* Autoplay */
    var delay = parseInt(section.getAttribute('data-autoplay'), 10) || 2600;
    if (!reduce) {
        setInterval(function () { if (!paused && !document.hidden) forward(1); }, delay);
    }
    ['mouseenter', 'focusin', 'touchstart'].forEach(function (ev) {
        section.addEventListener(ev, function () { paused = true; });
    });
    ['mouseleave', 'focusout', 'touchend'].forEach(function (ev) {
        section.addEventListener(ev, function () { paused = false; });
    });

    /* Drag / swipe (also pauses autoplay while held, via the listeners above) */
    var startX = null, dx = 0, moved = false;
    track.addEventListener('pointerdown', function (e) {
        if (busy || (e.pointerType === 'mouse' && e.button !== 0)) return;
        startX = e.clientX; dx = 0; moved = false;
        track.classList.remove('is-sliding');
        viewport.classList.add('is-dragging');
    });
    window.addEventListener('pointermove', function (e) {
        if (startX === null) return;
        dx = e.clientX - startX;
        if (Math.abs(dx) > 5) moved = true;
        track.style.transform = 'translateX(' + dx + 'px)';
    });
    window.addEventListener('pointerup', function () {
        if (startX === null) return;
        var d = dx;
        startX = null;
        viewport.classList.remove('is-dragging');
        track.classList.add('is-sliding');
        if (d < -60) { track.style.transform = 'translateX(0)'; forward(1); }
        else if (d > 60) { track.style.transform = 'translateX(0)'; forward(-1); }
        else track.style.transform = 'translateX(0)';
    });
    track.addEventListener('click', function (e) { if (moved) e.preventDefault(); });
})();

/* 3D page: "Selected Work" gallery — filter tabs, endless prev/next slider, click-to-feature,
   and the Exploded View toggle on the <model-viewer>. */
(function () {
    'use strict';

    var section = document.querySelector('.td-viewer');
    if (!section) return;
    var track = section.querySelector('[data-gallery-track]');
    var viewport = section.querySelector('[data-gallery]');
    var prev = section.querySelector('[data-gallery-prev]');
    var next = section.querySelector('[data-gallery-next]');
    var tabs = section.querySelectorAll('.filter-tab');
    var tiles = track ? Array.prototype.slice.call(track.children) : [];

    /* Filter tabs: show only tiles tagged with the picked category (data-cats is a space list) */
    var applyFilter = function (key) {
        tiles.forEach(function (tile) {
            var cats = (tile.getAttribute('data-cats') || '').split(' ');
            tile.hidden = key !== 'all' && cats.indexOf(key) === -1;
        });
    };
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            applyFilter(tab.getAttribute('data-filter'));
        });
    });

    /* Slider: endless — reorders the strip by one tile per click, same mechanic as the other
       sliders on the site, so prev/next always has somewhere to go even though the strip is
       exactly as wide as its viewport at rest. */
    var busy = false;
    var stepPx = function () {
        var gap = parseFloat(getComputedStyle(track).columnGap) || 24;
        var visible = tiles.filter(function (t) { return !t.hidden; });
        return (visible[0] ? visible[0].offsetWidth : 106.67) + gap;
    };
    var slide = function (dir) {
        if (busy || !track) return;
        busy = true;
        var step = stepPx();
        track.classList.add('is-sliding');

        if (dir > 0) {
            track.style.transform = 'translateX(' + (-step) + 'px)';
        } else {
            var last = track.lastElementChild;
            track.insertBefore(last, track.firstElementChild);
            track.style.transform = 'translateX(' + (-step) + 'px)';
            void track.offsetWidth;
            track.classList.add('is-sliding');
            track.style.transform = 'translateX(0)';
        }

        var finished = false;
        var done = function () {
            if (finished) return;
            finished = true;
            track.removeEventListener('transitionend', done);
            track.classList.remove('is-sliding');
            if (dir > 0) track.appendChild(track.firstElementChild);
            track.style.transform = 'translateX(0)';
            busy = false;
        };
        track.addEventListener('transitionend', done);
        setTimeout(done, 600);
    };
    if (prev) prev.addEventListener('click', function () { slide(-1); });
    if (next) next.addEventListener('click', function () { slide(1); });

    /* Click a tile to feature it (bigger, red border, numbered badge) */
    tiles.forEach(function (tile) {
        tile.addEventListener('click', function () {
            tiles.forEach(function (t) { t.classList.toggle('is-featured', t === tile); });
        });
    });

    /* Exploded View: swaps the <model-viewer> to the real "-open" .glb (an actual separate model
       with the parts pulled apart), not a camera trick. Keeps the current camera angle across
       the swap so it doesn't snap to a default view. */
    var toggleBtn = section.querySelector('[data-explode-toggle]');
    var viewer = section.querySelector('.td-rings__product');
    if (toggleBtn && viewer && window.customElements) {
        var label = toggleBtn.querySelector('[data-explode-label]');
        var exploded = false;
        var setState = function (on) {
            exploded = on;
            viewer.classList.toggle('is-exploded', on);
            toggleBtn.setAttribute('aria-pressed', String(on));
            toggleBtn.disabled = true;
            if (label) label.textContent = on ? 'Loading…' : 'Loading…';
            var src = viewer.getAttribute(on ? 'data-src-exploded' : 'data-src-normal');
            var orbit = viewer.cameraOrbit; // keep whatever angle the visitor left it at
            var settle = function () {
                clearTimeout(safety);
                viewer.removeEventListener('load', settle);
                viewer.removeEventListener('error', settle);
                viewer.cameraOrbit = orbit;
                viewer.jumpCameraToGoal && viewer.jumpCameraToGoal();
                toggleBtn.disabled = false;
                if (label) label.textContent = on ? 'Assembled View' : 'Exploded View';
            };
            /* Failsafe: a big .glb on a slow connection (or a load/error event that never fires)
               shouldn't leave the button stuck on "Loading…" forever. */
            var safety = setTimeout(settle, 8000);
            viewer.addEventListener('load', settle);
            viewer.addEventListener('error', settle);
            if (src) viewer.src = src;
        };
        toggleBtn.addEventListener('click', function () { setState(!exploded); });
    }
})();

/* SEO page: "Selected SEO Work" strip — filter tabs, endless prev/next (header + strip arrows),
   and click-to-feature. Same mechanic as the 3D page gallery above. */
(function () {
    'use strict';

    var section = document.querySelector('.seo-pf, .ppt-pf');
    if (!section) return;
    var track = section.querySelector('[data-gallery-track]');
    var tabs = section.querySelectorAll('.filter-tab');
    var tiles = track ? Array.prototype.slice.call(track.children) : [];
    if (!track) return;

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-filter');
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            tiles.forEach(function (tile) {
                var cats = (tile.getAttribute('data-cats') || '').split(' ');
                tile.hidden = key !== 'all' && cats.indexOf(key) === -1;
            });
        });
    });

    var busy = false;
    var slide = function (dir) {
        if (busy) return;
        busy = true;
        var gap = parseFloat(getComputedStyle(track).columnGap) || 24;
        var first = Array.prototype.filter.call(track.children, function (t) { return !t.hidden; })[0];
        var step = (first ? first.offsetWidth : 106.67) + gap;
        track.classList.add('is-sliding');

        if (dir > 0) {
            track.style.transform = 'translateX(' + (-step) + 'px)';
        } else {
            track.insertBefore(track.lastElementChild, track.firstElementChild);
            track.classList.remove('is-sliding');
            track.style.transform = 'translateX(' + (-step) + 'px)';
            void track.offsetWidth;
            track.classList.add('is-sliding');
            track.style.transform = 'translateX(0)';
        }

        var finished = false;
        var done = function () {
            if (finished) return;
            finished = true;
            track.removeEventListener('transitionend', done);
            track.classList.remove('is-sliding');
            if (dir > 0) track.appendChild(track.firstElementChild);
            track.style.transform = 'translateX(0)';
            busy = false;
        };
        track.addEventListener('transitionend', done);
        setTimeout(done, 600);
    };
    section.querySelectorAll('[data-gallery-prev]').forEach(function (b) { b.addEventListener('click', function () { slide(-1); }); });
    section.querySelectorAll('[data-gallery-next]').forEach(function (b) { b.addEventListener('click', function () { slide(1); }); });

    tiles.forEach(function (tile) {
        tile.addEventListener('click', function () {
            tiles.forEach(function (t) { t.classList.toggle('is-featured', t === tile); });
        });
    });
})();

/* Packaging page: every filter tab swaps the picture in each card of the grid (cards carry data-img-<tab key>) */
(function () {
    'use strict';

    var tabsWrap = document.querySelector('[data-pk-tabs]');
    var grid = document.querySelector('[data-pk-grid]');
    if (!tabsWrap || !grid) return;
    var tabs = tabsWrap.querySelectorAll('.filter-tab');
    var imgs = grid.querySelectorAll('.pk-tile img');

    // load every tab's pictures up front so switching is instant
    imgs.forEach(function (img) {
        Array.prototype.forEach.call(img.attributes, function (a) {
            if (a.name.indexOf('data-img-') === 0) { new Image().src = a.value; }
        });
    });

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-filter');
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            imgs.forEach(function (img) {
                var src = img.getAttribute('data-img-' + key);
                if (!src || img.getAttribute('src') === src) return;
                img.classList.add('is-swapping');
                setTimeout(function () { img.setAttribute('src', src); img.classList.remove('is-swapping'); }, 180);
            });
        });
    });
})();
/* Contact page: INDIA / USA / DUBAI office selector */
(function () {
    'use strict';

    var wrap = document.querySelector('[data-offices]');
    if (!wrap) return;
    var tabs = wrap.querySelectorAll('[data-office]');
    var panels = wrap.querySelectorAll('[data-office-panel]');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var key = tab.getAttribute('data-office');
            tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            panels.forEach(function (p) { p.hidden = p.getAttribute('data-office-panel') !== key; });
        });
    });
})();

/* About page: scroll reveal + capabilities slider (tabs, arrows, progress, snap sync) */
(function () {
    'use strict';

    var root = document.querySelector('.ab');
    if (!root) return;

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.body.classList.add('ab-js');

    /* Reveal: add .is-in once an element scrolls into view */
    var items = root.querySelectorAll('[data-reveal]');
    if (reduce || !('IntersectionObserver' in window)) {
        items.forEach(function (el) { el.classList.add('is-in'); });
    } else {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-in');
                io.unobserve(entry.target);
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
        items.forEach(function (el) { io.observe(el); });
    }

    /* SMIL travellers in the SVG maps don't respond to CSS, so stop them for reduced motion */
    if (reduce) {
        root.querySelectorAll('svg').forEach(function (svg) {
            if (svg.pauseAnimations) svg.pauseAnimations();
        });
    }

    /* How we think: the stack builds up plate by plate while the matching step lights.
       Hover / focus / click a step to jump to it; autoplay only runs while the section is on screen. */
    var think = root.querySelector('[data-think]');
    if (think) {
        var plates = think.querySelectorAll('.ab-plate');
        var tsteps = think.querySelectorAll('[data-think-step]');
        var cur = 0, timer = null;

        var setStep = function (i) {
            cur = i;
            plates.forEach(function (p, k) { p.classList.toggle('is-active', k === i); p.classList.toggle('is-done', k < i); });
            tsteps.forEach(function (s, k) { s.classList.toggle('is-active', k === i); s.classList.toggle('is-done', k < i); });
        };
        var stopPlay = function () { clearInterval(timer); timer = null; };
        var startPlay = function () {
            if (reduce || timer) return;
            timer = setInterval(function () { setStep((cur + 1) % plates.length); }, 2300);
        };

        tsteps.forEach(function (s, i) {
            ['mouseenter', 'focus', 'click'].forEach(function (ev) {
                s.addEventListener(ev, function () { stopPlay(); setStep(i); });
            });
            s.addEventListener('mouseleave', startPlay);
            s.addEventListener('blur', startPlay);
        });

        setStep(0);
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) startPlay(); else stopPlay(); });
            }, { threshold: 0.3 }).observe(think);
        } else {
            startPlay();
        }
    }

    /* Capabilities slider */
    var cap = root.querySelector('[data-caps]');
    if (!cap) return;
    var track = cap.querySelector('[data-caps-track]');
    var panels = Array.prototype.slice.call(track.children);
    var tabs = cap.querySelectorAll('[data-caps-tab]');
    var prev = cap.querySelector('[data-caps-prev]');
    var next = cap.querySelector('[data-caps-next]');
    var bar = cap.querySelector('[data-caps-bar]');
    var count = cap.querySelector('[data-caps-count]');
    var active = -1;

    var pad = function () { return parseFloat(getComputedStyle(track).paddingLeft) || 0; };
    var nearest = function () {
        var left = track.scrollLeft + pad();
        var best = 0, min = Infinity;
        panels.forEach(function (p, i) {
            var d = Math.abs(p.offsetLeft - left);
            if (d < min) { min = d; best = i; }
        });
        /* at the very end the last panel can't reach the snap point: still count it as current */
        if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 2) best = panels.length - 1;
        return best;
    };
    var go = function (i) {
        i = Math.max(0, Math.min(panels.length - 1, i));
        track.scrollTo({ left: panels[i].offsetLeft - pad(), behavior: reduce ? 'auto' : 'smooth' });
    };
    var sync = function () {
        var i = nearest();
        if (i === active) return;
        active = i;
        panels.forEach(function (p, k) { p.classList.toggle('is-active', k === i); });
        tabs.forEach(function (t, k) {
            t.classList.toggle('is-active', k === i);
            t.setAttribute('aria-selected', k === i ? 'true' : 'false');
        });
        count.textContent = ('0' + (i + 1)).slice(-2);
        bar.style.width = ((i + 1) / panels.length * 100) + '%';
        prev.disabled = i === 0;
        next.disabled = i === panels.length - 1;
    };

    var ticking = false;
    track.addEventListener('scroll', function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () { ticking = false; sync(); });
    }, { passive: true });
    window.addEventListener('resize', function () { active = -1; sync(); });

    prev.addEventListener('click', function () { go(active - 1); });
    next.addEventListener('click', function () { go(active + 1); });
    tabs.forEach(function (t, i) { t.addEventListener('click', function () { go(i); }); });
    track.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') { e.preventDefault(); go(active + 1); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); go(active - 1); }
    });

    sync();
})();
