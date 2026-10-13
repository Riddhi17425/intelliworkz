/* Content editor: collapsible list cards, add / remove / reorder, repeater rows, live titles, section spy. */
(function () {
    'use strict';

    var form = document.querySelector('[data-content-form]');
    if (!form) return;

    var seq = Date.now() % 100000;
    var uid = function () { return 'n' + (++seq); };

    var renumber = function (list) {
        var items = list.querySelectorAll(':scope > [data-items] > [data-item]');
        items.forEach(function (it, i) {
            var no = it.querySelector('[data-no]');
            if (no) no.textContent = i + 1;
        });
        var count = list.querySelector('[data-count]');
        if (count) count.textContent = list.querySelectorAll(':scope > [data-items] > .adm-item').length;
    };

    var fromTemplate = function (tpl, token) {
        var wrap = document.createElement('div');
        wrap.innerHTML = tpl.innerHTML.split(token).join(uid());
        return wrap.firstElementChild;
    };

    form.addEventListener('click', function (e) {
        var t = e.target.closest('[data-act]');
        if (!t || !form.contains(t)) return;
        var act = t.getAttribute('data-act');
        var item = t.closest('.adm-item');
        var list = t.closest('[data-list]');

        if (act === 'toggle') {
            if (e.target.closest('.adm-item__tools')) return;
            item.classList.toggle('is-open');
        } else if (act === 'add-item') {
            var tpl = list.querySelector(':scope > template[data-template]');
            var box = list.querySelector(':scope > [data-items]');
            var node = fromTemplate(tpl, '__N__');
            box.appendChild(node);
            node.classList.add('is-open');
            if (window.AdminUI) window.AdminUI.initImages(node);
            renumber(list);
            node.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var first = node.querySelector('input[type=text], textarea');
            if (first) first.focus({ preventScroll: true });
        } else if (act === 'remove') {
            item.remove();
            renumber(list);
            form.dispatchEvent(new Event('input', { bubbles: true }));
        } else if (act === 'up' || act === 'down') {
            var sib = act === 'up' ? item.previousElementSibling : item.nextElementSibling;
            if (sib && sib.classList.contains('adm-item')) {
                if (act === 'up') item.parentNode.insertBefore(item, sib);
                else item.parentNode.insertBefore(sib, item);
                renumber(list);
                form.dispatchEvent(new Event('input', { bubbles: true }));
            }
        } else if (act === 'add-sub') {
            var rep = t.closest('[data-repeater]');
            var rows = rep.querySelector('[data-rows]');
            var subTpl = rep.querySelector('template[data-sub-template]');
            rows.appendChild(fromTemplate(subTpl, '__M__'));
        } else if (act === 'remove-sub') {
            t.closest('[data-sub]').remove();
            form.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    // card heading follows whatever is typed into the field that names it
    form.addEventListener('input', function (e) {
        var src = e.target.closest('[data-title-source]');
        if (!src) return;
        var title = src.closest('[data-item]').querySelector('[data-title]');
        if (title) title.textContent = src.value.trim() || 'New item';
    });

    // section tabs highlight as you scroll
    var tabs = document.querySelectorAll('[data-spy]');
    if ('IntersectionObserver' in window && tabs.length) {
        var spy = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                tabs.forEach(function (a) { a.classList.toggle('active', a.getAttribute('data-spy') === en.target.id); });
                var active = document.querySelector('[data-spy].active');
                // scroll only the tab strip sideways; scrollIntoView() would drag the whole page back up to the strip
                var strip = active && active.parentElement;
                if (strip && strip.scrollWidth > strip.clientWidth) {
                    strip.scrollTo({ left: active.offsetLeft - (strip.clientWidth - active.offsetWidth) / 2, behavior: 'smooth' });
                }
            });
        }, { rootMargin: '-160px 0px -60% 0px' });
        document.querySelectorAll('.adm-fieldgroup').forEach(function (s) { spy.observe(s); });
    }

    // formatting toolbar above paragraph boxes (works for rows added later too: one delegated listener)
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-fmt-act]');
        if (!btn) return;
        var bar = btn.closest('.adm-fmt');
        var ta = bar && bar.nextElementSibling;
        if (!ta || ta.tagName !== 'TEXTAREA') return;
        e.preventDefault();

        var start = ta.selectionStart, end = ta.selectionEnd;
        var sel = ta.value.slice(start, end);
        var out, cursorFrom, cursorTo;
        var wrap = function (open, close, fallback) {
            var inner = sel || fallback;
            out = open + inner + close;
            cursorFrom = start + open.length;
            cursorTo = cursorFrom + inner.length;
        };

        switch (btn.getAttribute('data-fmt-act')) {
            case 'strong': wrap('<strong>', '</strong>', 'bold text'); break;
            case 'em': wrap('<em>', '</em>', 'italic text'); break;
            case 'link':
                var url = window.prompt('Link address (https://…, mailto:, tel: or a page like contact-us.php)', 'https://');
                if (!url || url === 'https://') return;
                wrap('<a href="' + url.replace(/"/g, '&quot;') + '">', '</a>', 'link text');
                break;
            case 'ul':
            case 'ol':
                var tag = btn.getAttribute('data-fmt-act');
                var lines = (sel || 'List item').split(/\n+/).map(function (l) { return l.trim(); }).filter(Boolean);
                out = '<' + tag + '>\n' + lines.map(function (l) { return '<li>' + l + '</li>'; }).join('\n') + '\n</' + tag + '>';
                cursorFrom = start; cursorTo = start + out.length;
                break;
            case 'br': out = '<br>'; cursorFrom = cursorTo = start + out.length; break;
            case 'clear':
                if (sel) { out = sel.replace(/<[^>]*>/g, ''); }
                else { ta.value = ta.value.replace(/<[^>]*>/g, ''); ta.dispatchEvent(new Event('input', { bubbles: true })); ta.focus(); return; }
                cursorFrom = start; cursorTo = start + out.length;
                break;
            default: return;
        }

        ta.value = ta.value.slice(0, start) + out + ta.value.slice(end);
        ta.focus();
        ta.setSelectionRange(cursorFrom, cursorTo);
        ta.dispatchEvent(new Event('input', { bubbles: true }));   // live titles + unsaved-changes guard
    });

    // "Sections on this page": move rows up/down, grey out hidden ones, remove an added section
    var layoutList = document.querySelector('[data-layout-list]');
    if (layoutList) {
        var reindex = function () {
            layoutList.querySelectorAll('[data-layout-row]').forEach(function (row, i) {
                row.querySelectorAll('input[name^="layout["]').forEach(function (input) {
                    input.name = input.name.replace(/^layout\[\d+\]/, 'layout[' + i + ']');
                });
            });
        };
        layoutList.addEventListener('click', function (e) {
            var move = e.target.closest('[data-layout-move]');
            if (move) {
                var row = move.closest('[data-layout-row]');
                var dir = parseInt(move.getAttribute('data-layout-move'), 10);
                var other = dir < 0 ? row.previousElementSibling : row.nextElementSibling;
                if (!other || !other.hasAttribute('data-layout-row')) return;     // the hero row stays first
                if (dir < 0) layoutList.insertBefore(row, other); else layoutList.insertBefore(other, row);
                reindex();
                row.classList.add('is-moved');
                setTimeout(function () { row.classList.remove('is-moved'); }, 600);
                var form = layoutList.closest('form');
                if (form) form.dispatchEvent(new Event('input', { bubbles: true }));    // unsaved-changes guard
                return;
            }
            var rm = e.target.closest('[data-remove-section]');
            if (rm) {
                var f = document.getElementById('removeSectionForm');
                if (!f) return;
                f.action = rm.getAttribute('data-remove-section');
                f.setAttribute('data-confirm', '“' + rm.getAttribute('data-remove-name') + '” and the text you wrote in it will be deleted from this page. This cannot be undone.');
                f.requestSubmit ? f.requestSubmit() : f.submit();
            }
        });
        layoutList.addEventListener('change', function (e) {
            var sw = e.target.closest('[data-layout-show]');
            if (sw) sw.closest('[data-layout-row]').classList.toggle('is-off', !sw.checked);
        });
    }
})();
