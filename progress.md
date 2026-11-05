# Theme improvement progress

Updated: 2026-11-05. Release candidate: 1.3.53. Work branch: `improve/programs-forms-and-languages` (the branch name predates clarification; no language changes are included).

## Scope

Small reversible fixes. Keep colors, original photos, written content, existing registrations and leads. Defer translations and language switching. No site-specific partner arrays, taxonomy IDs or navigation URL lists in theme components.

## Verified pieces

- **Partner logos:** extracted reusable component; replaced 38/56px caps and six-column desktop layout with responsive cards. Optional cached optical bounds compensate for empty upload margins without modifying images. Fixed inherited article-image margins that clipped marks. Reviewed the eight original partner logos in desktop screenshots.
- **Navigation:** removed header signup CTA markup, stopped copying CTAs into mobile menu, replaced mobile accordion interception with visible linked groups, retained focus/Escape/no-JS handling. Prepared restoration of original real page destinations; corrected broken Social Projects/Code Week references and removed duplicate Code Week link. Every internal menu destination exists in published pages.
- **Spacing and images:** scoped removal of the header/main gap. Removed duplicate margins inside Image blocks, retained authored image dimensions and natural proportions. Partner rules do not touch article media.
- **Forms/tables:** extracted CF7 styling that preserves authored layout wrappers and optional responsive `.form-grid` columns. Checkboxes retain natural control sizing. Native Table blocks contain horizontal scrolling. No form field, validation rule, mail or integration mapping was edited.
- **Blog:** confirmed Blog and Latest News are categories, not tags. Generic Blog Home uses an editor-customizable Query Loop. The site's chosen terms will be stored in its template, never hardcoded in PHP. Pagination remains unlimited.
- **Page inventory:** read all 59 published pages and 97 posts plus header/footer/navigation references. Historical project and registration pages are retained. Empty/abandoned placeholders are being checked before reversible unpublishing.
- **Documentation:** README now explains installation, saved overrides, reusable components, menus, taxonomy filters, forms, tables, validation and rollback. Deployment excludes progress/documentation.

## Validation

- All theme PHP files: syntax passes under PHP 8.3.
- JavaScript syntax and seven DOM/CSS/real-WordPress render tests: pass.
- Isolated WordPress integration: category union, 21 posts over three pages, changing selected categories, and article-image isolation: pass.
- Chromium: 12 widths from 320 to 1440px pass menu visibility/focus/Escape, no-JS fallback, header gap, button/page overflow, portrait/landscape sizing, form columns/checkbox sizing and table containment. Original partner assets load correctly. Re-running after a mobile spacing refinement and refreshing the local copy of the latest live homepage.
- Tests never submit forms. Live mail delivery and external registration integrations are unchanged and were not exercised with fake submissions.

## Production record

- PR #2 merged as `2a77b6a`; GitHub validation and FTPS deployment succeeded for 1.3.52.
- Saved header/nav restored with real links, Home and Lajmet-Blog labels, no menu signup CTA. Corrected Social Projects and duplicate/broken Code Week destinations.
- Blog Home now uses editor-selected Blog/Latest News categories; REST reports 73 matching published posts, and the live first page has ten posts with pagination.
- Reversibly drafted only three empty/abandoned unreferenced placeholders: Become A Teacher, Evaluation Criteria, and the old under-construction Debate page. No registrations/leads or historical project content deleted.
- Corrected nine stale hrefs in Team, the annual report and the archived award page. Historical article URLs now use canonical destinations; the missing chocolate-distribution article links to the relevant social-project overview.
- Production HTML confirmed all eight partner canvases rendered and the four component styles loaded at the new version.
- Production snapshot checking uncovered a further lazy-image placeholder grid overflow that the isolated render did not reproduce. Added minimum-width containment to grid children/mosaic and block formatting for card-image links. The follow-up also removes `sizes=auto` only inside optical logo wrappers: native size containment was forcing a 1500px placeholder height. Explicit responsive sizes and article images are preserved. An isolated GD/DOM rendering test covers this regression. Desktop/phone screenshots now show every mark intact; the 12-width final sweep is in progress.
- Offline screenshot fixtures replace remote asset URLs with local copies and disable external plugin scripts; they verify layout/theme behavior, not third-party plugin execution.

## Next checkpoint

Complete screenshot review, publish the tested revision, apply narrowly scoped saved navigation/header/blog-template corrections, verify production output and record CI/deployment status here. Retain WordPress revisions and the prior theme revision for rollback.

## Latest checkpoint

- Live blog first page: 10 posts; eighth/final page: 3 posts, matching 73 total.
- Additional public HTTP fetches of several inner pages hit proxy timeouts/errors; authenticated page/content inventory remains available. Do not treat these transport failures as proof that a page should be removed.
