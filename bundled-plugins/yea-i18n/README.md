# YEA JSON Languages

Custom WordPress plugin for Albanian (`sq`), English (`en`) and German (`de`). Uses the site's existing WordPress AI Client Google provider. No new API key is stored by this plugin.

Translations are persisted in `wp-content/uploads/yea-i18n/{sq,en,de}.json`; bundled UI catalogs provide initial labels. JSON keys are SHA256 hashes of normalized source strings. The source index is stored in the non-autoloaded `yea_i18n_sources` WordPress option. Existing translated keys are retained, so imported corrections survive background runs. Source changes create new keys and are queued again.

Install the bundled directory as `wp-content/plugins/yea-i18n`, then activate **YEA JSON Languages**. The theme also provides an administrator-only `POST /yea/v1/i18n/install` endpoint that copies and activates this fixed bundle. Translation files survive plugin/theme updates.

Under **Settings → YEA Languages**, scan public posts/pages, template parts, navigation and CF7 confirmation templates. Translate batches with Google, inspect progress, export/import corrected JSON and enable languages after coverage reaches 100%. Languages stay hidden until explicitly enabled. Keep a backup of the uploads directory and WordPress database.

Language URLs use `/sq/`, `/en/`, `/de/`; existing root URLs remain available with Albanian when enabled. Page rendering uses saved translations only. Internal links, canonical URLs, HTML language and alternate links follow the active language. Content markup is parsed with PHP DOM; form names, hidden values, explicit and implicit option values, scripts, filenames and participant data stay unchanged. Use `translate="no"` on other content that must remain literal.

New published content sets a rescan flag. WP-Cron processes a small Google batch, then schedules the next batch; provider errors stop rapid retries and are visible on the settings screen. Reliable automatic processing requires a working WP-Cron or server cron. Translation API charges are billed by the site's Google provider. The complete initial translation can require many batches.

PHP: `yea_i18n_t('Welcome {{name}}', ['name' => $name])`.
Browser: `window.yeaI18n.t('Welcome {{name}}', {name})`.
Register dynamic fixed strings in `locales/source.json`. Never send submitted values to translation. Client strings are delivered before theme scripts; they work without a React dependency.

Admin API namespace `/yea-i18n/v1`: GET `status`; POST `scan`, `tick`, `enable` (`enabled` boolean), `catalog` (`language`, optional plain-text `entries`). Routes require `manage_options`; the settings UI uses REST nonces. Catalog import merges supplied keys instead of deleting other translations.

Limitations: external embeds, images containing text, attached documents, third-party scripts and historical emails are not translated. Editorial review is recommended before publishing machine translations. Source-content edits can display their source text until their new translation batch completes.
