/* Progressive enhancement only: CF7 owns validation, transport and integrations. */
(function () {
    'use strict';
    function init() {
        document.querySelectorAll('.award-portal .wpcf7 form').forEach(function (form) {
            const steps = Array.from(form.querySelectorAll('.award-step'));
            if (steps.length < 2 || form.dataset.awardReady) return;
            let current = 0;
            form.querySelectorAll('input[type=file]').forEach(function (input) {
                const status = document.createElement('p'); status.className = 'award-file-meta'; status.setAttribute('aria-live', 'polite');
                input.after(status);
                input.addEventListener('change', function () {
                    const file = input.files[0];
                    status.textContent = file ? file.name + ' · ' + (file.size / 1048576).toFixed(1) + ' MB' : '';
                });
            });
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
                    const file = field.type === 'file' && field.files[0];
                    const badFile = file && (!/\.pdf$/i.test(file.name) || file.size > 10 * 1024 * 1024);
                    const bad = missing || badFile || (field.value && !field.validity.valid);
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
            wrapper.addEventListener('wpcf7mailsent', function (event) {
                const panel = document.querySelector('.award-success');
                if (panel) {
                    const response = event.detail && event.detail.apiResponse;
                    if (response && response.registration_code) {
                        const code = panel.querySelector('.award-registration-code');
                        if (code) code.textContent = response.registration_code;
                        const project = panel.querySelector('.award-registered-project');
                        if (project) project.textContent = response.project_name || '';
                        const email = panel.querySelector('.award-email-status');
                        if (email) email.textContent = response.email_confirmation === false ? 'Emaili nuk u konfirmua. Ruajeni kodin nga kjo faqe.' : 'Kodi ju dërgohet edhe në emailin e koordinatorit.';
                        const download = panel.querySelector('.award-save-code');
                        if (download) download.onclick = function () {
                            const blob = new Blob(['YEA! Awards\nProjekti: ' + (response.project_name || '') + '\nKodi: ' + response.registration_code + '\nDorëzimi: ' + (response.delivery_url || '')], {type: 'text/plain;charset=utf-8'});
                            const url = URL.createObjectURL(blob); const link = document.createElement('a'); link.href = url; link.download = 'YEA-registration-code.txt'; link.click(); setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
                        };
                    }
                    panel.hidden = false; panel.tabIndex = -1; panel.focus();
                }
                // CF7 resets the original form after this event; never simulate a success.
                form.hidden = true;
            });
            form.dataset.awardReady = 'true'; show(0, false);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();

(function () {
    'use strict';
    async function initDelivery() {
        const portal = document.querySelector('.award-delivery');
        if (!portal || !window.yeaAward) return;
        const root = window.yeaAward.deliveryRoot;
        const status = portal.querySelector('.award-delivery-status');
        const errors = portal.querySelector('.award-delivery-error');
        const codeForm = portal.querySelector('.award-code-form');
        const unlocked = portal.querySelector('.award-unlocked');
        const fileForm = portal.querySelector('.award-delivery-files');
        let token = null;
        async function request(path, options) {
            const response = await fetch(root + path, Object.assign({cache: 'no-store', credentials: 'omit'}, options));
            const data = await response.json();
            if (!response.ok) { const error = new Error(data.message || 'Lidhja nuk u konfirmua. Provoni përsëri.'); error.status = response.status; throw error; }
            return data;
        }
        try {
            const result = await request('status');
            status.textContent = result.open ? 'Dorëzimi është i hapur. Vendosni kodin e regjistrimit.' : 'Dorëzimi i projekteve nuk është hapur ende. Ruajeni kodin tuaj; do ta përdorni këtu kur të hapet.';
            codeForm.hidden = !result.open;
        } catch (error) { status.textContent = 'Statusi nuk u kontrollua. Rifreskoni faqen për të provuar përsëri.'; }
        codeForm.addEventListener('submit', async function (event) {
            event.preventDefault(); errors.textContent = '';
            const button = codeForm.querySelector('button'); button.disabled = true;
            try {
                const result = await request('unlock', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({code: codeForm.elements.code.value})});
                token = result.token; codeForm.reset(); codeForm.hidden = true; unlocked.hidden = false;
                const welcome = portal.querySelector('.award-project-welcome'); welcome.textContent = 'Mirë se vini, “' + result.project + '”.'; welcome.focus();
                status.textContent = 'Projekti u verifikua. Tani mund të dorëzoni dokumentet.';
            } catch (error) { errors.textContent = error.message; }
            finally { button.disabled = false; }
        });
        const documents = fileForm.querySelector('input[type=file]');
        documents.addEventListener('change', function () {
            const list = portal.querySelector('.award-selected-files'); list.replaceChildren();
            Array.from(documents.files).forEach(function (file) { const li = document.createElement('li'); li.textContent = file.name + ' · ' + (file.size / 1048576).toFixed(1) + ' MB'; list.append(li); });
        });
        fileForm.addEventListener('submit', async function (event) {
            event.preventDefault(); errors.textContent = '';
            const files = Array.from(documents.files);
            if (!files.length || files.length > 2 || files.some(function (file) { return !/\.(pptx?|pdf)$/i.test(file.name) || file.size < 1 || file.size > 20 * 1048576; })) {
                errors.textContent = 'Zgjidhni një ose dy dokumente PDF / PowerPoint, deri në 20 MB secili.'; documents.focus(); return;
            }
            const button = fileForm.querySelector('button'); button.disabled = true; button.textContent = 'Po dërgohen dokumentet…';
            try {
                const result = await request('files', {method: 'POST', headers: {'X-YEA-Delivery-Token': token}, body: new FormData(fileForm)});
                if (!result.received) throw new Error('Dorëzimi nuk u konfirmua. Provoni përsëri.');
                unlocked.hidden = true; status.textContent = ''; fileForm.reset();
                const success = portal.querySelector('.award-delivery-success'); success.hidden = false;
                success.querySelector('.award-delivery-receipt').textContent = 'Referenca e dorëzimit: ' + result.receipt; success.focus();
            } catch (error) {
                errors.textContent = error.message;
                if (error.status === 401 || error.status === 423) {
                    token = null; unlocked.hidden = true; codeForm.hidden = error.status === 423;
                    status.textContent = error.status === 423 ? 'Dorëzimi është mbyllur.' : 'Vendosni përsëri kodin për të vazhduar.';
                }
            } finally { button.disabled = false; button.textContent = 'Dërgo dokumentet'; }
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initDelivery); else initDelivery();
})();
