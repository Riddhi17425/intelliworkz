/* Intelliworkz – gallery lightbox (vanilla JS).
   Any element with data-lightbox='[{"type":"image","src":"..."},{"type":"video","src":"..."}]' opens it.
   Pictures slide on their own (pause with the button or by hovering); a video stops the slideshow while it is shown.
   Videos: YouTube / Vimeo links or a direct .mp4 / .webm / .mov address. Keys: Esc, left / right, space. */
(function () {
    'use strict';

    var DELAY = 4500;
    var root, stage, textEl, thumbs, titleEl, countEl, bar, playBtn, prevBtn, nextBtn;
    var cardText = '', items = [], at = 0, timer = null, playing = true, hover = false, lastFocus = null;
    var groupEls = [], groupAt = -1, grpPrev, grpNext;   /* cards that belong to the same group (e.g. the industry cards): the popup can jump from one card to the next */

    var youtube = function (u) {
        var m = u.match(/(?:youtube(?:-nocookie)?\.com\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([\w-]{6,})/);
        return m ? m[1] : null;
    };
    var vimeo = function (u) {
        var m = u.match(/vimeo\.com\/(?:video\/)?(\d+)/);
        return m ? m[1] : null;
    };

    var build = function () {
        root = document.createElement('div');
        root.className = 'lb';
        root.hidden = true;
        root.setAttribute('role', 'dialog');
        root.setAttribute('aria-modal', 'true');
        root.setAttribute('aria-label', 'Gallery');
        root.innerHTML =
            '<div class="lb__backdrop" data-lb-close></div>' +
            '<div class="lb__box">' +
                '<div class="lb__top"><span class="lb__title"></span><span class="lb__count" aria-live="polite"></span>' +
                    '<button type="button" class="lb__grp lb__grp--prev" hidden><svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg><span></span></button>' +
                    '<button type="button" class="lb__grp lb__grp--next" hidden><span></span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' +
                    '<button type="button" class="lb__btn lb__play" aria-label="Pause slideshow"></button>' +
                    '<button type="button" class="lb__btn lb__close" aria-label="Close" data-lb-close>&times;</button></div>' +
                '<div class="lb__main">' +
                    '<button type="button" class="lb__nav lb__nav--prev" aria-label="Previous"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' +
                    '<div class="lb__stage"></div>' +
                    '<button type="button" class="lb__nav lb__nav--next" aria-label="Next"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' +
                '</div>' +
                '<div class="lb__bar"><span></span></div>' +
                '<p class="lb__text" hidden></p>' +
                '<div class="lb__thumbs"></div>' +
            '</div>';
        document.body.appendChild(root);

        stage = root.querySelector('.lb__stage');
        thumbs = root.querySelector('.lb__thumbs');
        textEl = root.querySelector('.lb__text');
        titleEl = root.querySelector('.lb__title');
        countEl = root.querySelector('.lb__count');
        bar = root.querySelector('.lb__bar span');
        playBtn = root.querySelector('.lb__play');
        prevBtn = root.querySelector('.lb__nav--prev');
        nextBtn = root.querySelector('.lb__nav--next');
        grpPrev = root.querySelector('.lb__grp--prev');
        grpNext = root.querySelector('.lb__grp--next');
        grpPrev.addEventListener('click', function () { goGroup(-1); });
        grpNext.addEventListener('click', function () { goGroup(1); });

        root.querySelectorAll('[data-lb-close]').forEach(function (el) { el.addEventListener('click', close); });
        prevBtn.addEventListener('click', function () { go(at - 1); });
        nextBtn.addEventListener('click', function () { go(at + 1); });
        playBtn.addEventListener('click', function () { playing = !playing; schedule(); });
        stage.addEventListener('mouseenter', function () { hover = true; schedule(); });
        stage.addEventListener('mouseleave', function () { hover = false; schedule(); });

        // swipe
        var x0 = null;
        stage.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
        stage.addEventListener('touchend', function (e) {
            if (x0 === null) return;
            var d = e.changedTouches[0].clientX - x0;
            x0 = null;
            if (Math.abs(d) > 50) go(at + (d < 0 ? 1 : -1));
        });

        document.addEventListener('keydown', function (e) {
            if (root.hidden) return;
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowLeft') go(at - 1);
            else if (e.key === 'ArrowRight') go(at + 1);
            else if (e.key === ' ' && e.target === document.body) { e.preventDefault(); playing = !playing; schedule(); }
        });
    };

    var icon = function (on) {
        return on
            ? '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z" fill="currentColor"/></svg>'
            : '<svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l11-6.5a1 1 0 0 0 0-1.72l-11-6.5A1 1 0 0 0 8 5.5z" fill="currentColor"/></svg>';
    };

    /* the slideshow runs only while it is on, the pointer is away and the current slide is a picture */
    var schedule = function () {
        clearTimeout(timer);
        bar.style.transition = 'none';
        bar.style.width = '0';
        playBtn.innerHTML = icon(playing);
        playBtn.setAttribute('aria-label', playing ? 'Pause slideshow' : 'Play slideshow');
        playBtn.hidden = items.length < 2;
        if (!playing || hover || items.length < 2 || items[at].type !== 'image') return;
        void bar.offsetWidth;   // restart the progress bar
        bar.style.transition = 'width ' + DELAY + 'ms linear';
        bar.style.width = '100%';
        timer = setTimeout(function () { go(at + 1); }, DELAY);
    };

    var thumb = function (it) {
        var src = it.src, glyph = '';
        if (it.type === 'video') {
            var y = youtube(it.src);
            src = y ? 'https://img.youtube.com/vi/' + y + '/mqdefault.jpg' : '';
            glyph = '<i class="lb__thumb-play"></i>';
        }
        return (src ? '<img src="' + src.replace(/"/g, '&quot;') + '" alt="">' : '') + glyph;
    };

    var render = function () {
        var it = items[at];
        stage.innerHTML = '';
        var el;
        if (it.type === 'video') {
            var y = youtube(it.src), v = vimeo(it.src);
            if (y || v) {
                el = document.createElement('iframe');
                el.src = y ? 'https://www.youtube.com/embed/' + y + '?autoplay=1&rel=0' : 'https://player.vimeo.com/video/' + v + '?autoplay=1';
                el.allow = 'autoplay; fullscreen; picture-in-picture';
                el.allowFullscreen = true;
                el.title = titleEl.textContent;
            } else {
                el = document.createElement('video');
                el.src = it.src;
                el.controls = true;
                el.autoplay = true;
                el.playsInline = true;
            }
        } else {
            el = document.createElement('img');
            el.src = it.src;
            el.alt = titleEl.textContent;
        }
        el.className = 'lb__media is-' + it.type;
        stage.appendChild(el);

        /* a video can carry its own title / description; otherwise the card's text stays */
        var cap = it.title || it.text ? [it.title, it.text].filter(Boolean).join(' — ') : cardText;
        textEl.textContent = cap;
        textEl.hidden = !cap;

        countEl.textContent = items.length > 1 ? (at + 1) + ' / ' + items.length : '';
        prevBtn.hidden = nextBtn.hidden = items.length < 2;
        thumbs.hidden = items.length < 2;
        Array.prototype.forEach.call(thumbs.children, function (t, i) { t.classList.toggle('is-active', i === at); });
        schedule();
    };

    var go = function (n) {
        at = (n + items.length) % items.length;
        render();
    };

    /* read the pictures / videos of a card element */
    var listOf = function (el) {
        var l;
        try { l = JSON.parse(el.getAttribute('data-lightbox')); } catch (err) { return []; }
        return (l || []).filter(function (it) { return it && it.src; });
    };

    var goGroup = function (d) {
        var n = groupEls.length;
        if (n < 2) return;
        var el = groupEls[(groupAt + d + n) % n], list = listOf(el);
        if (list.length) open(list, el.getAttribute('data-title'), el, el.getAttribute('data-text'), true);
    };

    var open = function (list, title, from, text, keepFocus) {
        if (!root) build();
        var grp = from && from.getAttribute ? from.getAttribute('data-lightbox-group') : null;
        groupEls = grp ? [].filter.call(document.querySelectorAll('[data-lightbox-group="' + grp + '"]'), function (el) { return listOf(el).length; }) : [];
        groupAt = groupEls.indexOf(from);
        if (groupAt < 0) groupEls = [];
        var showGrp = groupEls.length > 1;
        grpPrev.hidden = grpNext.hidden = !showGrp;
        if (showGrp) {
            var nm = function (d) { var e = groupEls[(groupAt + d + groupEls.length) % groupEls.length]; return e.getAttribute('data-title') || ''; };
            grpPrev.querySelector('span').textContent = nm(-1);
            grpNext.querySelector('span').textContent = nm(1);
            grpPrev.setAttribute('aria-label', 'Previous: ' + nm(-1));
            grpNext.setAttribute('aria-label', 'Next: ' + nm(1));
        }
        items = list;
        at = 0;
        playing = true;
        hover = false;
        if (!keepFocus) lastFocus = from || null;
        titleEl.textContent = title || '';
        cardText = text || '';
        thumbs.innerHTML = '';
        items.forEach(function (it, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'lb__thumb is-' + it.type;
            b.setAttribute('aria-label', 'Show ' + (it.type === 'video' ? 'video' : 'picture') + ' ' + (i + 1));
            b.innerHTML = thumb(it);
            b.addEventListener('click', function () { go(i); });
            thumbs.appendChild(b);
        });
        root.hidden = false;
        document.body.classList.add('modal-open');
        render();
        root.querySelector('.lb__close').focus();
    };

    function close() {
        clearTimeout(timer);
        root.hidden = true;
        stage.innerHTML = '';   // stops any playing video
        document.body.classList.remove('modal-open');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest ? e.target.closest('[data-lightbox]') : null;
        if (!t) return;
        var list = listOf(t);
        if (!list.length) return;
        e.preventDefault();
        e.stopPropagation();
        open(list, t.getAttribute('data-title'), t, t.getAttribute('data-text'));
    });
})();
