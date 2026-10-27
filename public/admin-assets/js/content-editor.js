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
        syncMax(list);
    };

    // a list whose section has room for only so many items: the Add button stops at the limit
    var syncMax = function (list) {
        var max = parseInt(list.getAttribute('data-max') || '0', 10);
        var btn = list.querySelector('[data-act="add-item"]');
        if (!max || !btn) return;
        var full = list.querySelectorAll(':scope > [data-items] > .adm-item').length >= max;
        btn.disabled = full;
        btn.title = full ? 'This section has room for ' + max + ' items. Remove one to add another.' : '';
    };
    form.querySelectorAll('[data-list][data-max]').forEach(syncMax);

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
            var subNode = fromTemplate(subTpl, '__M__');
            rows.appendChild(subNode);
            if (window.AdminUI) window.AdminUI.initImages(subNode);
            var firstSub = subNode.querySelector('input[type=text], textarea');
            if (firstSub) firstSub.focus();
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

    // sections open and close like a dropdown; which ones are open is remembered for this page while the tab stays open
    var sections = Array.prototype.slice.call(form.querySelectorAll('[data-section]'));
    if (sections.length) {
        form.classList.add('has-sections');
        var storeKey = 'adm-open-sections:' + location.pathname;
        var remember = function () {
            try {
                var open = sections.filter(function (s) { return s.classList.contains('is-open'); }).map(function (s) { return s.id; });
                sessionStorage.setItem(storeKey, JSON.stringify(open));
            } catch (err) { /* private mode: just forget */ }
        };
        var setOpen = function (section, open) {
            section.classList.toggle('is-open', open);
            var head = section.querySelector('[data-section-toggle]');
            if (head) head.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        var byId = function (id) { return sections.filter(function (s) { return s.id === id; })[0]; };

        var saved = null;
        try { saved = JSON.parse(sessionStorage.getItem(storeKey) || 'null'); } catch (err) { saved = null; }
        var openIds = Array.isArray(saved) ? saved.filter(byId) : [];
        // a section with an error, or the one named in the address (#sec-portfolio), must be open
        sections.forEach(function (s) { if (s.querySelector('.is-invalid, .invalid-feedback')) openIds.push(s.id); });
        if (location.hash && byId(location.hash.slice(1))) openIds.push(location.hash.slice(1));
        sections.forEach(function (s) { setOpen(s, openIds.indexOf(s.id) !== -1); });

        var toggleHead = function (head) {
            var section = head.closest('[data-section]');
            setOpen(section, !section.classList.contains('is-open'));
            remember();
        };
        form.addEventListener('click', function (e) {
            var head = e.target.closest('[data-section-toggle]');
            if (head && form.contains(head)) toggleHead(head);
        });
        form.addEventListener('keydown', function (e) {
            var head = e.target.closest('[data-section-toggle]');
            if (head && e.target === head && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); toggleHead(head); }
        });
        document.querySelectorAll('[data-sections-all]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var open = btn.getAttribute('data-sections-all') === 'open';
                sections.forEach(function (s) { setOpen(s, open); });
                remember();
            });
        });

        // the tabs above jump to a section and open it
        var goTo = function (id) {
            var s = byId(id);
            if (!s) return false;
            setOpen(s, true);
            remember();
            s.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return true;
        };
        document.querySelectorAll('#sectionTabs a[href^="#sec-"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                if (goTo(a.getAttribute('href').slice(1))) { e.preventDefault(); history.replaceState(null, '', a.getAttribute('href')); }
            });
        });
        window.addEventListener('hashchange', function () { goTo(location.hash.slice(1)); });
        if (location.hash && byId(location.hash.slice(1))) {
            setTimeout(function () { byId(location.hash.slice(1)).scrollIntoView({ block: 'start' }); }, 50);
        }
    }

    // after Save the page reloads: put the editor back where they were working instead of at the top
    var scrollKey = 'adm-scroll:' + location.pathname;
    form.addEventListener('submit', function () {
        try { sessionStorage.setItem(scrollKey, String(window.pageYOffset || document.documentElement.scrollTop)); } catch (err) { /* ignore */ }
    });
    try {
        var savedY = sessionStorage.getItem(scrollKey);
        if (savedY !== null) {
            sessionStorage.removeItem(scrollKey);
            setTimeout(function () { window.scrollTo({ top: parseInt(savedY, 10) || 0, behavior: 'instant' }); }, 120);   // after the #sec- jump above
        }
    } catch (err) { /* ignore */ }

    // "Sections on this page" stays closed until needed (open after add/remove, on errors, or when asked for in the address)
    var layoutCard = document.getElementById('layoutCard');
    if (layoutCard) {
        var layoutHead = layoutCard.querySelector('[data-layout-toggle]');
        var setLayout = function (open) {
            layoutCard.classList.toggle('is-collapsed', !open);
            if (layoutHead) layoutHead.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        setLayout(location.hash === '#layoutCard' || !!layoutCard.querySelector('.is-invalid, .invalid-feedback'));
        if (layoutHead) {
            layoutHead.addEventListener('click', function () { setLayout(layoutCard.classList.contains('is-collapsed')); });
            layoutHead.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setLayout(layoutCard.classList.contains('is-collapsed')); }
            });
        }
    }

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

/* Fields: hide / show any text or picture, delete added paragraphs, add paragraphs (saved with the form). */
(function () {
    'use strict';

    var form = document.querySelector('[data-content-form]');
    if (!form) return;

    var changed = function () { form.dispatchEvent(new Event('input', { bubbles: true })); };

    form.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-fld-act]');
        if (!btn || !form.contains(btn)) return;
        var act = btn.getAttribute('data-fld-act');
        var row = btn.closest('[data-field]');

        if (act === 'hide' && row) {
            var input = row.querySelector('[data-fld-hidden]');
            var off = input.value !== '1';
            input.value = off ? '1' : '0';
            row.classList.toggle('is-off', off);
            var isBtn = btn.hasAttribute('data-is-btn');
            btn.querySelector('span').textContent = off ? (isBtn ? 'Bring button back' : 'Show') : (isBtn ? 'Remove button' : 'Hide');
            btn.querySelector('i').className = 'bi ' + (off ? 'bi-arrow-counterclockwise' : (isBtn ? 'bi-trash3' : 'bi-eye-slash'));
            changed();
        } else if ((act === 'delete' || act === 'undo') && row) {
            var del = act === 'delete';
            row.querySelector('[data-fld-delete]').value = del ? '1' : '0';
            row.classList.toggle('is-deleting', del);
            row.querySelector('[data-fld-act="delete"]').classList.toggle('d-none', del);
            row.querySelector('[data-fld-act="undo"]').classList.toggle('d-none', !del);
            changed();
        } else if (act === 'add') {
            var box = btn.closest('[data-fld-add-box]');
            var wrap = document.createElement('div');
            wrap.className = 'adm-newfield';
            var field = box.getAttribute('data-kind') === 'text'
                ? '<input type="text" maxlength="500" class="form-control" placeholder="Write the new line…">'
                : '<textarea rows="3" class="form-control" placeholder="Write the new paragraph… (you can use &lt;strong&gt;, &lt;em&gt;, &lt;a href&gt;)"></textarea>';
            wrap.innerHTML = '<span class="adm-newfield__tag">New</span>' + field +
                '<button type="button" class="adm-fieldtool text-danger" data-fld-act="remove-new" title="Remove"><i class="bi bi-x-lg"></i></button>';
            var control = wrap.querySelector('input, textarea');
            control.name = 'new_fields[' + box.getAttribute('data-base') + '][]';
            box.insertBefore(wrap, btn);
            control.focus();
            changed();
        } else if (act === 'remove-new') {
            btn.closest('.adm-newfield').remove();
            changed();
        }
    });
})();
