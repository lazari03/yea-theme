(() => {
  'use strict';
  const config = window.yeaLanguageAdmin;
  const status = document.getElementById('yea-language-status');
  async function request(action, body) {
    const response = await fetch(config.url + action, {
      method: body ? 'POST' : 'GET',
      headers: {'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce},
      body: body ? JSON.stringify(body) : undefined
    });
    const result = await response.json();
    if (!response.ok) throw new Error(result.message || 'Request failed');
    return result;
  }
  const refresh = async () => { status.textContent = JSON.stringify(await request('status'), null, 2); };
  document.querySelectorAll('[data-yea-action]').forEach(button => button.addEventListener('click', async () => {
    button.disabled = true;
    try {
      const action = button.dataset.yeaAction;
      const language = document.getElementById('yea-language').value;
      if (action === 'export') {
        const catalog = await request('catalog', {language});
        const url = URL.createObjectURL(new Blob([JSON.stringify(catalog, null, 2)], {type: 'application/json'}));
        const link = document.createElement('a'); link.href = url; link.download = language + '.json'; link.click(); URL.revokeObjectURL(url);
      } else if (action === 'import') {
        const file = document.getElementById('yea-import').files[0];
        if (!file || file.size > 5000000) throw new Error('Choose a JSON file smaller than 5 MB.');
        await request('catalog', {language, entries: JSON.parse(await file.text())});
      } else {
        await request(action === 'disable' ? 'enable' : action, {enabled: action === 'enable'});
      }
      await refresh();
    } catch (error) { status.textContent = error.message; }
    finally { button.disabled = false; }
  }));
  refresh().catch(error => { status.textContent = error.message; });
})();
(() => {
  'use strict';
  const config = window.yeaLanguageAdmin;
  const container = document.getElementById('yea-language-editor');
  const info = document.getElementById('yea-page-info');
  const state = {page: 1, pages: 1, dirty: false};
  async function api(path, data) {
    const response = await fetch(config.url + path, {method: data ? 'POST' : 'GET', headers: {'X-WP-Nonce': config.nonce, 'Content-Type': 'application/json'}, body: data ? JSON.stringify(data) : undefined});
    const value = await response.json(); if (!response.ok) throw new Error(value.message || 'Request failed'); return value;
  }
  async function load(page) {
    if (state.dirty && !window.confirm('Discard unsaved translations on this page?')) return;
    const search = document.getElementById('yea-search').value;
    const result = await api('sources?page=' + page + '&per_page=15&search=' + encodeURIComponent(search));
    state.page = result.page; state.pages = result.pages; state.dirty = false;
    container.replaceChildren();
    result.rows.forEach(row => {
      const block = document.createElement('fieldset'); block.style.cssText = 'padding:16px;background:white;border:1px solid #ddd;margin-bottom:16px';
      const source = document.createElement('p'); source.textContent = row.source; source.style.whiteSpace = 'pre-wrap'; block.append(source);
      ['sq', 'en', 'de'].forEach(language => {
        const label = document.createElement('label'); label.style.cssText = 'display:block;margin:8px 0'; label.textContent = {sq:'Shqip', en:'English', de:'Deutsch'}[language];
        const input = document.createElement('textarea'); input.value = row[language]; input.dataset.key = row.key; input.dataset.language = language;
        input.style.cssText = 'display:block;width:100%;min-height:65px'; input.addEventListener('input', () => { state.dirty = true; });
        label.append(input); block.append(label);
      });
      container.append(block);
    });
    info.textContent = 'Page ' + state.page + ' of ' + state.pages + ' · ' + result.total + ' source texts';
    document.getElementById('yea-prev').disabled = state.page <= 1;
    document.getElementById('yea-next').disabled = state.page >= state.pages;
  }
  const handle = task => task.catch(error => { info.textContent = error.message; });
  document.getElementById('yea-find').onclick = () => handle(load(1));
  document.getElementById('yea-prev').onclick = () => handle(load(Math.max(1, state.page - 1)));
  document.getElementById('yea-next').onclick = () => handle(load(state.page + 1));
  document.getElementById('yea-save').onclick = async () => {
    const button = document.getElementById('yea-save'); button.disabled = true;
    try {
      for (const language of ['sq', 'en', 'de']) {
        const entries = {}; container.querySelectorAll('[data-language="' + language + '"]').forEach(input => { entries[input.dataset.key] = input.value; });
        if (Object.keys(entries).length) await api('catalog', {language, entries});
      }
      state.dirty = false; info.textContent = 'Translations saved.';
    } catch (error) { info.textContent = error.message; } finally { button.disabled = false; }
  };
  window.addEventListener('beforeunload', event => { if (state.dirty) { event.preventDefault(); event.returnValue = ''; } });
  handle(load(1));
})();
