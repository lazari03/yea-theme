(() => {
  'use strict';
  const messages = window.yeaTranslations || {};
  window.yeaI18n = Object.freeze({
    t(key, variables = {}) {
      let text = messages[key] || key;
      Object.entries(variables).forEach(([name, value]) => { text = text.split('{{' + name + '}}').join(String(value)); });
      return text;
    }
  });
})();
