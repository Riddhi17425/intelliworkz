/* Enquiry / contact forms: send with fetch(), show the result under the form, fall back to a normal POST without JS. */
(function () {
    'use strict';

    document.querySelectorAll('form[data-inquiry-form]').forEach(function (form) {
        var status = form.querySelector('[data-form-status]');
        var button = form.querySelector('button[type=submit]');
        var original = button ? button.innerHTML : '';

        var say = function (text, ok) {
            if (!status) return;
            status.textContent = text;
            status.className = 'form-status ' + (ok ? 'is-ok' : 'is-error');
        };
        var mark = function (name, bad) {
            var input = form.querySelector('[name="' + name + '"]');
            if (input) {
                if (bad) input.setAttribute('aria-invalid', 'true'); else input.removeAttribute('aria-invalid');
            }
        };

        form.addEventListener('submit', function (e) {
            if (!window.fetch || !window.FormData) return;     // plain POST fallback
            e.preventDefault();
            ['name', 'email', 'phone', 'company', 'subject', 'message'].forEach(function (n) { mark(n, false); });
            say('', true);

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
                    say(r.body.message || 'Thank you! Your message has been sent.', true);
                } else if (r.status === 422 && r.body.errors) {
                    var first = null;
                    Object.keys(r.body.errors).forEach(function (k) { mark(k, true); first = first || r.body.errors[k][0]; });
                    say(first || 'Please check the form and try again.', false);
                } else if (r.status === 429) {
                    say('Too many messages in a short time. Please wait a minute and try again.', false);
                } else {
                    say('Sorry, something went wrong. Please try again in a moment.', false);
                }
            }).catch(function () {
                say('Could not reach the server. Please check your connection and try again.', false);
            }).then(function () {
                button.disabled = false;
                button.innerHTML = original;
            });
        });
    });
})();
