# YEA JSON Languages

Offline translations for Albanian (`sq`), English (`en`) and German (`de`). No translation service, AI provider, API key or background translation request is used.

Translations are persisted in `wp-content/uploads/yea-i18n/{sq,en,de}.json`; versioned catalogs in `locales/` provide initial translations. Keys are SHA256 hashes of normalized source strings. The source index is stored in the non-autoloaded `yea_i18n_sources` WordPress option. Existing translated keys are retained after scanning new content. Source changes create new keys needing new translations.

Install as `wp-content/plugins/yea-i18n` and activate **YEA JSON Languages**. The theme includes an administrator-only `POST /yea/v1/i18n/install` endpoint that copies and activates this fixed bundle. Saved translation files survive theme and plugin updates.

Use **Settings → YEA Languages** to scan public content, search source texts, enter Albanian/English/German translations, save each page of edits or import/export JSON. Once every indexed text has translations, enable the header switcher. New content is flagged for a rescan. An empty translation falls back to its source. JSON import merges supplied entries; empty values remove saved entries.

Routes use `/sq/`, `/en/` and `/de/`. Existing root URLs remain available in Albanian when enabled. Internal links, canonical URLs, language metadata and the header switcher follow the selected language. PHP DOM translates visible text while preserving markup, hidden identifiers, form names, option values, scripts, uploaded filenames and participant data. Add `translate="no"` to content that should remain literal.

PHP: `yea_i18n_t('Welcome {{name}}', ['name' => $name])`.
Browser: `window.yeaI18n.t('Welcome {{name}}', {name})`.
Register dynamic fixed strings in `locales/source.json`. Preserve interpolation placeholders and CF7 mail-tags in translations.

Admin API `/yea-i18n/v1`: GET `status`, GET `sources` (`page`, `per_page`, optional `search`), POST `scan`, `enable` (`enabled`), `catalog` (`language`, optional `entries`). Routes require `manage_options`; the editor sends REST nonces. Sources are exportable through the paginated API for preparing translated JSON externally.

External embeds, images containing text, attached documents, third-party scripts and historical emails are not translated. New or changed strings fall back to source until translated. Back up both uploads and the WordPress database.
