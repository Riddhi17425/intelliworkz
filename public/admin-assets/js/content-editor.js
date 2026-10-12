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
                if (active && active.scrollIntoView) active.scrollIntoView({ block: 'nearest', inline: 'center' });
            });
        }, { rootMargin: '-90px 0px -65% 0px' });
        document.querySelectorAll('.adm-fieldgroup').forEach(function (s) { spy.observe(s); });
    }
})();
