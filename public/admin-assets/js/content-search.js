/* Search inside one page of the content editor: finds fields, lists and list items by their name or by the text
   typed into them, opens the section that holds the match and jumps to it. Nothing is hidden or removed.
   Enter / the arrows go to the next or previous match. Nothing is sent to the server. */
(function () {
    'use strict';

    var box = document.querySelector('[data-content-search]');
    var form = document.querySelector('[data-content-form]');
    if (!box || !form) return;

    var input = box.querySelector('input');
    var count = box.querySelector('.adm-search__count');
    var clear = box.querySelector('[data-search-clear]');
    var prev = box.querySelector('[data-search-prev]');
    var next = box.querySelector('[data-search-next]');
    var sections = Array.prototype.slice.call(form.querySelectorAll('[data-section]'));

    var matches = [];
    var at = -1;
    var timer = null;

    var norm = function (s) { return (s || '').toLowerCase().replace(/\s+/g, ' '); };

    // everything a person can read in a row: its label plus the current value of every box inside it
    var textOf = function (el) {
        var t = [];
        el.querySelectorAll('label, .adm-item__title, .adm-type').forEach(function (n) { t.push(n.textContent); });
        el.querySelectorAll('input[type=text], textarea').forEach(function (n) { t.push(n.value); });
        el.querySelectorAll('select option:checked').forEach(function (n) { t.push(n.textContent); });
        return norm(t.join(' '));
    };

    var setOpen = function (section, open) {
        section.classList.toggle('is-open', open);
        var head = section.querySelector('[data-section-toggle]');
        if (head) head.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    var unmark = function () {
        form.querySelectorAll('.is-hit, .is-current').forEach(function (n) { n.classList.remove('is-hit', 'is-current'); });
    };

    // what can match: a plain field, a list item card, or a list without cards (strings / maps)
    var collect = function (q) {
        var found = [];
        sections.forEach(function (section) {
            var sectionName = norm(section.querySelector('h2') ? section.querySelector('h2').textContent : '');
            var rows = section.querySelectorAll('.adm-card__body > .adm-fieldrow');
            var before = found.length;
            rows.forEach(function (row) {
                if (row.hasAttribute('data-list')) {
                    var items = row.querySelectorAll(':scope > [data-items] > .adm-item');
                    if (!items.length) {
                        if (textOf(row).indexOf(q) !== -1) found.push({ el: row, section: section });
                        return;
                    }
                    var listName = norm(row.querySelector('label') ? row.querySelector('label').textContent : '');
                    var hit = false;
                    items.forEach(function (it) {
                        if (textOf(it).indexOf(q) !== -1) { found.push({ el: it, section: section, item: true }); hit = true; }
                    });
                    if (!hit && listName.indexOf(q) !== -1) found.push({ el: row, section: section });
                } else if (textOf(row).indexOf(q) !== -1) {
                    found.push({ el: row, section: section });
                }
            });
            // only the section's name matches: jump to the section itself
            if (found.length === before && sectionName.indexOf(q) !== -1) found.push({ el: section, section: section, whole: true });
        });
        return found;
    };

    var show = function (i) {
        if (!matches.length) return;
        at = (i + matches.length) % matches.length;
        form.querySelectorAll('.is-current').forEach(function (n) { n.classList.remove('is-current'); });
        var m = matches[at];
        setOpen(m.section, true);
        if (m.item) m.el.classList.add('is-open');
        m.el.classList.add('is-current');
        m.el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        count.textContent = (at + 1) + ' / ' + matches.length;
    };

    var run = function () {
        var q = norm(input.value.trim());
        unmark();
        matches = [];
        at = -1;
        clear.hidden = !q;
        prev.hidden = next.hidden = true;
        if (!q) { count.textContent = ''; return; }
        matches = collect(q);
        if (!matches.length) { count.textContent = 'No matches'; return; }
        matches.forEach(function (m) {
            if (!m.whole) m.el.classList.add('is-hit');
            setOpen(m.section, true);   // every section with a hit is open, so the highlights are all visible
        });
        prev.hidden = next.hidden = matches.length < 2;
        show(0);
    };

    // typing here must not count as an edit of the page (the unsaved-changes warning listens on the form)
    ['input', 'change'].forEach(function (ev) { box.addEventListener(ev, function (e) { e.stopPropagation(); }); });
    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(run, 150); });
    next.addEventListener('click', function () { show(at + 1); });
    prev.addEventListener('click', function () { show(at - 1); });
    clear.addEventListener('click', function () { input.value = ''; run(); input.focus(); });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { input.value = ''; run(); }
        if (e.key === 'Enter') {
            e.preventDefault();                       // never submit the big content form from the search box
            clearTimeout(timer);
            if (!matches.length) run(); else show(at + (e.shiftKey ? -1 : 1));
        }
    });

    // "/" jumps to the search box unless the person is typing somewhere
    document.addEventListener('keydown', function (e) {
        if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
        var t = e.target;
        if (t && (/^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName) || t.isContentEditable)) return;
        e.preventDefault();
        input.focus();
        input.select();
    });

    input.removeAttribute('name');
})();
