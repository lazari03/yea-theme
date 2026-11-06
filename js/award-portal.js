/* Progressive enhancement only: CF7 owns validation, transport and integrations. */
(function () {
    'use strict';
    function init() {
        document.querySelectorAll('.award-portal .wpcf7 form').forEach(function (form) {
            const steps = Array.from(form.querySelectorAll('.award-step'));
            if (steps.length < 2 || form.dataset.awardReady) return;
            let current = 0;
            const progress = document.createElement('ol');
            progress.className = 'award-progress';
            progress.setAttribute('aria-label', 'Hapat e regjistrimit');
            const buttons = steps.map(function (step, index) {
                const li = document.createElement('li');
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = (index + 1) + '. ' + step.dataset.stepTitle;
                button.setAttribute('aria-controls', step.id);
                button.addEventListener('click', function () { go(index); });
                li.appendChild(button); progress.appendChild(li);
                return button;
            });
            form.querySelector('.yea-award-form').prepend(progress);
            const controls = document.createElement('div'); controls.className = 'award-controls';
            const back = document.createElement('button'); back.type = 'button'; back.className = 'award-back'; back.textContent = 'Kthehu';
            const next = document.createElement('button'); next.type = 'button'; next.className = 'award-next'; next.textContent = 'Vazhdo →';
            const error = document.createElement('p'); error.className = 'award-step-error'; error.setAttribute('role', 'alert');
            back.addEventListener('click', function () { go(current - 1); });
            next.addEventListener('click', function () { go(current + 1); });
            controls.append(back, next); steps[steps.length - 1].after(error, controls);
            function valid(index) {
                let invalid = null;
                steps[index].querySelectorAll('input:not([type=hidden]):not([type=submit]), select, textarea').forEach(function (field) {
                    const required = field.required || field.getAttribute('aria-required') === 'true';
                    const missing = required && !field.value.trim();
                    const bad = missing || (field.value && !field.validity.valid);
                    field.setAttribute('aria-invalid', String(Boolean(bad)));
                    if (bad && !invalid) invalid = field;
                });
                if (!invalid) return true;
                show(index, false);
                error.textContent = 'Plotësoni fushat e detyrueshme dhe kontrolloni të dhënat e shënuara.';
                invalid.focus(); return false;
            }
            function show(index, focus) {
                current = index; error.textContent = '';
                steps.forEach(function (step, i) { step.hidden = i !== index; });
                buttons.forEach(function (button, i) { if (i === index) button.setAttribute('aria-current', 'step'); else button.removeAttribute('aria-current'); });
                back.hidden = index === 0; next.hidden = index === steps.length - 1;
                if (focus) { const legend = steps[index].querySelector('legend'); legend.tabIndex = -1; legend.focus(); }
            }
            function go(index) {
                if (index < 0 || index >= steps.length) return;
                if (index > current) for (let i = 0; i < index; i++) if (!valid(i)) return;
                show(index, true);
            }
            form.addEventListener('submit', function (event) {
                if (current < steps.length - 1) { event.preventDefault(); event.stopImmediatePropagation(); go(current + 1); return; }
                for (let i = 0; i < steps.length; i++) if (!valid(i)) { event.preventDefault(); event.stopImmediatePropagation(); return; }
            }, true);
            const wrapper = form.closest('.wpcf7');
            wrapper.addEventListener('wpcf7invalid', function (event) {
                const fields = event.detail && event.detail.apiResponse && event.detail.apiResponse.invalid_fields || [];
                const first = fields.map(function (item) { return Array.from(form.elements).find(function (field) { return field.name === item.field; }); }).find(Boolean);
                if (first) { const index = steps.indexOf(first.closest('.award-step')); if (index >= 0) show(index, false); first.focus(); }
            });
            wrapper.addEventListener('wpcf7mailsent', function () {
                const panel = document.querySelector('.award-success');
                if (panel) { panel.hidden = false; panel.tabIndex = -1; panel.focus(); }
                // CF7 resets the original form after this event; never simulate a success.
                show(0, false);
            });
            form.dataset.awardReady = 'true'; show(0, false);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
