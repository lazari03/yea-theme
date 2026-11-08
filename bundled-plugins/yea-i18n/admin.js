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
