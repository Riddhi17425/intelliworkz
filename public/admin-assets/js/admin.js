/* Intelliworkz Admin — shared behaviour (vanilla JS on top of Bootstrap's bundle) */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

    /* ---------- Sidebar (mobile) ---------- */
    $$('[data-nav-toggle]').forEach(function (b) {
        b.addEventListener('click', function () { document.body.classList.toggle('adm-nav-open'); });
    });
    var backdrop = $('.adm-backdrop');
    if (backdrop) backdrop.addEventListener('click', function () { document.body.classList.remove('adm-nav-open'); });

    /* ---------- Toasts ---------- */
    $$('.adm-toast').forEach(function (t) {
        var close = function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 300); };
        var btn = $('button', t);
        if (btn) btn.addEventListener('click', close);
        if (!t.classList.contains('error')) setTimeout(close, 5200);
    });

    /* ---------- Confirmation dialog for destructive forms: <form data-confirm="Delete this?"> ---------- */
    var modalEl = $('#confirmModal');
    if (modalEl && window.bootstrap) {
        var modal = new bootstrap.Modal(modalEl);
        var pending = null;
        var msg = $('[data-confirm-message]', modalEl);
        var title = $('[data-confirm-title]', modalEl);
        var ok = $('[data-confirm-ok]', modalEl);
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form.matches || !form.hasAttribute('data-confirm') || form.dataset.confirmed === '1') return;
            e.preventDefault();
            pending = form;
            msg.textContent = form.getAttribute('data-confirm');
            title.textContent = form.getAttribute('data-confirm-title') || 'Are you sure?';
            ok.textContent = form.getAttribute('data-confirm-ok') || 'Yes, delete';
            modal.show();
        }, true);
        ok.addEventListener('click', function () {
            if (!pending) return;
            pending.dataset.confirmed = '1';
            modal.hide();
            pending.requestSubmit ? pending.requestSubmit() : pending.submit();
        });
        modalEl.addEventListener('hidden.bs.modal', function () { pending = null; });
    }

    /* ---------- Image inputs ---------- */
    $$('.js-image').forEach(function (box) {
        var file = $('input[type=file]', box);
        var preview = $('.adm-img__preview', box);
        var name = $('.adm-img__name', box);
        var remove = $('input[type=checkbox][data-remove]', box);
        var original = preview ? preview.innerHTML : '';
        var originalName = name ? name.textContent : '';
        if (!file) return;
        file.addEventListener('change', function () {
            var f = file.files && file.files[0];
            if (!f) { preview.innerHTML = original; if (name) name.textContent = originalName; return; }
            if (remove) remove.checked = false;
            var url = URL.createObjectURL(f);
            preview.innerHTML = '<img alt="">';
            $('img', preview).src = url;
            if (name) name.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB) — will be uploaded on save';
        });
        if (remove) remove.addEventListener('change', function () {
            if (remove.checked) {
                preview.innerHTML = '<i class="bi bi-image"></i>';
                file.value = '';
                if (name) name.textContent = 'Image will be removed on save';
            } else {
                preview.innerHTML = original;
                if (name) name.textContent = originalName;
            }
        });
    });

    /* ---------- Slug helper: <input data-slug-from="#title"> ---------- */
    var slugify = function (s) {
        return s.toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '').replace(/&/g, ' and ')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 90);
    };
    $$('[data-slug-from]').forEach(function (input) {
        var src = $(input.getAttribute('data-slug-from'));
        if (!src) return;
        var touched = input.value !== '';
        input.addEventListener('input', function () { touched = input.value !== ''; });
        src.addEventListener('input', function () { if (!touched) input.value = slugify(src.value); });
    });

    /* ---------- Rich text (Quill) ---------- */
    $$('textarea.js-rich').forEach(function (ta) {
        if (!window.Quill) return;
        var holder = document.createElement('div');
        holder.innerHTML = ta.value;
        ta.parentNode.insertBefore(holder, ta);
        ta.style.display = 'none';
        var q = new Quill(holder, {
            theme: 'snow',
            modules: { toolbar: [[{ header: [2, 3, 4, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link'], ['clean']] }
        });
        var sync = function () { ta.value = q.root.innerHTML === '<p><br></p>' ? '' : q.root.innerHTML; };
        q.on('text-change', sync);
        ta.form.addEventListener('submit', sync);
    });

    /* ---------- Warn about unsaved changes on long forms ---------- */
    $$('form[data-dirty-guard]').forEach(function (form) {
        var dirty = false;
        form.addEventListener('input', function () { dirty = true; });
        form.addEventListener('change', function () { dirty = true; });
        form.addEventListener('submit', function () { dirty = false; });
        window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    });

    /* ---------- Disable the submit button once, to stop double posts ---------- */
    $$('form[data-once]').forEach(function (form) {
        form.addEventListener('submit', function () {
            var b = $('button[type=submit]', form);
            if (b) setTimeout(function () { b.disabled = true; b.classList.add('disabled'); }, 0);
        });
    });
})();
