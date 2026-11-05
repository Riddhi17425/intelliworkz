/* Enquiry / contact forms: send with fetch(), show validation errors under each field, the result as a toast, fall back to a normal POST without JS. */
(function () {
    'use strict';

    var FIELDS = ['name', 'email', 'phone', 'company', 'subject', 'message'];

    var stack = function () {
        var el = document.querySelector('.iw-toasts');
        if (!el) {
            el = document.createElement('div');
            el.className = 'iw-toasts';
            el.setAttribute('aria-live', 'polite');
            document.body.appendChild(el);
        }
        return el;
    };

    var toast = function (text, ok) {
        if (!text) return;
        var t = document.createElement('div');
        t.className = 'iw-toast ' + (ok ? 'is-ok' : 'is-error');
        t.setAttribute('role', ok ? 'status' : 'alert');
        var msg = document.createElement('span');
        msg.textContent = text;
        var close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Close');
        close.innerHTML = '&times;';
        t.appendChild(msg);
        t.appendChild(close);

        var host = stack();
        while (host.children.length >= 4) host.removeChild(host.firstChild);
        host.appendChild(t);

        var gone = function () {
            t.classList.add('is-leaving');
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 250);
        };
        close.addEventListener('click', gone);
        setTimeout(gone, ok ? 5000 : 6500);
    };

    document.querySelectorAll('form[data-inquiry-form]').forEach(function (form) {
        var button = form.querySelector('button[type=submit]');
        var original = button ? button.innerHTML : '';

        var mark = function (name, bad, text) {
            var input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            var note = input.parentNode.querySelector('.iw-field-error[data-for="' + name + '"]');
            if (note) note.parentNode.removeChild(note);
            if (!bad) { input.removeAttribute('aria-invalid'); return; }
            input.setAttribute('aria-invalid', 'true');
            note = document.createElement('small');
            note.className = 'iw-field-error';
            note.setAttribute('data-for', name);
            note.setAttribute('role', 'alert');
            note.textContent = text;
            input.insertAdjacentElement('afterend', note);
        };

        /* the contact number takes digits, + - ( ) . and spaces only: anything else is removed as it is typed or pasted */
        var phone = form.querySelector('[name="phone"]');
        if (phone) {
            phone.setAttribute('inputmode', 'tel');
            phone.setAttribute('autocomplete', 'tel');
            phone.setAttribute('maxlength', '40');
            phone.addEventListener('input', function () {
                var clean = phone.value.replace(/[^0-9+\-\s().]/g, '');
                if (clean !== phone.value) phone.value = clean;
            });
        }

        form.addEventListener('input', function (e) {
            if (e.target && e.target.name && e.target.getAttribute('aria-invalid')) mark(e.target.name, false);
        });

        form.addEventListener('submit', function (e) {
            if (!window.fetch || !window.FormData) return;     // plain POST fallback
            e.preventDefault();
            FIELDS.forEach(function (n) { mark(n, false); });

            button.disabled = true;
            button.textContent = 'Sending…';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (body) { return { status: res.status, body: body }; });
            }).then(function (r) {
                if (r.status === 200 && r.body.ok) {
                    form.reset();
                    if (r.body.redirect) {
                        window.location.href = r.body.redirect;     // the thank-you page
                        return;
                    }
                    toast(r.body.message || 'Thank you! Your message has been sent.', true);
                } else if (r.status === 422 && r.body.errors) {
                    // the message appears under the field that needs attention
                    var shown = 0;
                    FIELDS.concat(Object.keys(r.body.errors)).forEach(function (k, i, all) {
                        if (all.indexOf(k) !== i || !r.body.errors[k]) return;
                        if (!form.querySelector('[name="' + k + '"]')) { toast(r.body.errors[k][0], false); shown++; return; }
                        mark(k, true, r.body.errors[k][0]);
                        shown++;
                    });
                    if (!shown) toast('Please check the form and try again.', false);
                    var bad = form.querySelector('[aria-invalid="true"]');
                    if (bad && bad.focus) bad.focus({ preventScroll: true });
                } else if (r.status === 429) {
                    toast('Too many messages in a short time. Please wait a minute and try again.', false);
                } else {
                    toast('Sorry, something went wrong. Please try again in a moment.', false);
                }
            }).catch(function () {
                toast('Could not reach the server. Please check your connection and try again.', false);
            }).then(function () {
                button.disabled = false;
                button.innerHTML = original;
            });
        });
    });
})();
