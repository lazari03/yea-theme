# YEA WordPress theme

A block theme with reusable page patterns, responsive navigation, article layouts, partner logos and form styling. Version 1.3.53. The release fixes layout glitches; it does not migrate content or add translations.

## Install and update

1. Back up the current theme folder and WordPress database. Keep uploads and plugin settings.
2. Upload the theme folder to `wp-content/themes/` and activate it in Appearance → Themes. For an existing installation, replace the files in its existing theme folder.
3. Set the homepage and posts page under Settings → Reading. Set your logo, colors and site details through the WordPress editor/Customizer options available on your installation.
4. Review desktop and phone layouts, then clear any page/CDN caches.

Production needs WordPress with the block editor and a supported PHP version. Node is needed only for development checks. Contact Form 7 and WPForms are optional plugins, not bundled dependencies. Starter patterns contain sample copy: replace it for your organization. Existing saved templates and published content are never reset by a theme update.

## Pages and components

Use Appearance → Editor for templates/header/footer, and the page editor for page content. Insert the theme patterns from the block inserter; they are editable native blocks. The default Page template has a title hero. Use the builder template for pages that supply their own hero. Single Post keeps featured images separate from images embedded in the article.

Component styles are split into `assets/css/components/`: navigation, partner logos, content/tables and forms. Brand tokens remain in the base stylesheet and theme settings. Scope new component rules to their wrapper instead of changing every image, label or paragraph across the site.

### Navigation

Edit the Navigation block in the header. Add real page links and put related pages underneath their parent. The parent remains a working link. Mobile navigation displays groups and their destinations together, scrolls within the viewport, traps keyboard focus while open and closes with Escape. It does not copy signup buttons into the menu. Home is an editor-controlled label.

Saved header overrides take precedence over pattern files. To remove an old CTA on an existing site, remove its Buttons group in the saved header; updating the theme alone cannot overwrite it.

### Partner logos

Insert **YEA Logo Wall**, replace its Image blocks with Media Library uploads, and add/remove cards in the editor. The outer group uses `logo-wall-grid`; each card uses `logo-wall-card`. Partner names, links and image IDs belong to content, not PHP arrays.

Original uploads are unchanged. On hosts with GD and DOM, visible image bounds are measured once and cached to compensate for oversized white/transparent margins. Other hosts use proportional contain sizing. This processing applies only to the logo wall, never article photos. Use contrasting logo variants when the mark itself is white.

Optional wrapper CSS variables: `--partner-column-min`, `--partner-logo-size`, `--partner-card-height`, `--partner-gap`. Use normal Image blocks for photos outside this component.

### Blog and news

Open the **Blog Home** template, select its Query Loop in List View, and use a custom query (`Inherit query` off). Select the desired categories or tags under Filters, set order and posts per page, and keep Pagination blocks underneath Post Template. Leave the maximum page count unlimited (`pages: 0`) to expose every matching post.

Term selections are saved by WordPress in the template. The theme does not hardcode taxonomy names, IDs or URLs. Newly published matching posts appear automatically; changing selected terms changes the feed. Keep archive/search queries inherited so those templates respect the current archive or search.

### Forms

Insert a Contact Form 7 or WPForms block/shortcode provided by the plugin. Configure fields, required rules, email, spam protection and integrations in that plugin. Theme styling does not change submissions or existing integration mappings.

CF7 supports ordinary paragraphs and custom layouts. For optional responsive columns, use:

```html
<div class="form-grid">
  <p><label>Name [text* your-name]</label></p>
  <p><label>Email [email* your-email]</label></p>
  <p class="form-full-width"><label>Message [textarea your-message]</label></p>
</div>
<div class="form-actions">[submit "Send"]</div>
```

Keep each checkbox/radio label associated with its input. Use the plugin's `use_label_element` option for CF7 choices. Never rename an existing field without checking its mail template and external integration mappings.

### Tables and images

Insert WordPress's Table block with a header row and optional caption. Wide tables scroll inside their own wrapper on phones. Article Image blocks retain their selected size/proportions; set crops intentionally in the editor. The logo wall's sizing rules do not apply to article or featured images.

## Development and validation

```sh
npm ci --ignore-scripts
npm test
find . -name '*.php' -not -path './node_modules/*' -print0 | xargs -0 -n1 php -l
```

For real rendered WordPress markup, set `YEA_RENDERED_HTML=/absolute/path/homepage.html` before `npm test`. For browser geometry and screenshots, install Playwright/Chromium in development and run:

```sh
YEA_RENDERED_HTML=/absolute/path/homepage.html \
YEA_PREVIEW_ROOT=/absolute/path/isolated-wordpress \
YEA_CHROMIUM=/absolute/path/chrome-headless-shell \
npm run test:browser
```

`YEA_CHROMIUM` is optional when Playwright's browser is installed. Optional `YEA_ASSET_CACHE` is a JSON URL map to `{file,mime}` cached assets; `YEA_FONT_CSS` is cached font CSS; `YEA_SCREENSHOTS` sets the output folder. The browser checks 12 widths (320–1440px), navigation/focus/no-JS behavior, header spacing, overflow, image proportions, forms and tables. It never submits a form. Set `YEA_ASSERT_PARTNER_ASSETS=1` when testing with real cached partner images.

An additional test creates temporary posts/categories and removes them afterward. Run **only against an isolated database**:

```sh
WP_TEST_ROOT=/absolute/path/isolated-wordpress WP_TEST_ISOLATED=1 php tests/wordpress-integration.php
```

It verifies category unions, pagination beyond the first page, changing editor filters, and isolation of article images from logo processing. Tests are not a substitute for reviewing screenshots and actual saved templates/plugin markup.

## Release and rollback

Non-main branches and PRs validate without deploying. Main pushes validate PHP/JS/CSS/navigation before strict FTPS deployment. Configure `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD` as GitHub repository secrets; the FTP account must be scoped to the intended theme folder. Tests, development dependencies and documentation are excluded from uploads.

Review `progress.md` for the release checklist. Restore the previous theme folder to roll back code, and use WordPress revisions/backups to restore any separately edited header, menu or template. Do not reset all Site Editor overrides during an update.
