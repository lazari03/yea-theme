# YEA WordPress theme

Version 1.3.51 fixes responsive navigation and refreshes the existing design without modifying published page content, forms, registrations, menus, logos or saved Site Editor overrides.

## Validation

Use Node 22 and a PHP version compatible with the production host:

```sh
npm ci --ignore-scripts
npm test
find . -name '*.php' -not -path './node_modules/*' -print0 | xargs -0 -n1 php -l
```

The DOM tests cover both block navigation and the legacy PHP header, parent-page links, submenu toggles, keyboard focus, Escape, resize state, empty menus, reduced motion and CSS parsing. DOM tests do not measure layout.

For an additional real WordPress render, set `YEA_RENDERED_HTML` to the absolute path of a rendered homepage before `npm test`. To inspect geometry in Chromium, install Playwright in a development environment and run:

```sh
YEA_RENDERED_HTML=/absolute/path/homepage.html \
YEA_PREVIEW_ROOT=/absolute/path/isolated-wordpress \
YEA_CHROMIUM=/absolute/path/chrome-headless-shell \
npm run test:browser
```

`YEA_CHROMIUM` is optional if Playwright has its default browser installed. `YEA_ASSET_CACHE` can point to a JSON map of remote URLs to `{file, mime}` local assets. `YEA_FONT_CSS` can point to cached Google Fonts CSS. The browser test fulfills local WordPress assets directly from the isolated copy and blocks other requests. It never submits forms or navigates to production. It checks 12 viewport widths and saves screenshots and JSON to ignored `test-results/`.

## Release behavior

Pull requests and non-main branches run validation without deployment. Main pushes still deploy via strict FTPS, after PHP syntax and the JavaScript/CSS/navigation checks pass. Developer dependencies and test files are excluded from uploads.

Before merging, review rendered screenshots with the real saved templates, content and active plugins. The theme retains Customizer colors and Join destinations. Existing saved Inter font references remain supported; default headings use Bebas Neue and default body text uses Montserrat. The original logo is not changed.

Default header fragments resolve against the homepage; explicitly configured destinations are retained. The homepage starter pattern now uses the site's published volunteer and contact paths for its final actions. Pattern edits do not overwrite existing pages.

Keep the previous theme package for rollback. Do not reset saved Site Editor overrides to apply this update.
