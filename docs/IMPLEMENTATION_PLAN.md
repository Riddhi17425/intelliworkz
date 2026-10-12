# Intelliworkz — Laravel backend + Admin Panel: implementation plan

## 1. What was inspected (read-only)

| Item | Finding |
|---|---|
| Existing project | `C:\xampp\htdocs\intelliworkz` — plain PHP (includes + data arrays), **not** Laravel, **no database**, no server-side form handling. Under git (full history = backup). |
| Pages | 12: `index`, `about-us`, `contact-us`, `video-editing`, `magazine-design`, `3d-modeling-rendering`, `seo-services`, `marketing-sales-collaterals`, `packaging-design`, `ppt-design`, `pre-press-service`, `manufacturing` |
| Shared parts | `header.php` (head), `site-header.php` (top bar, mega-menu, drawer), `footer.php` (pre-footer, columns, offices), `includes/sections/{showcase,engagement}.php`, `helpers.php` |
| Data | `includes/data*.php` (≈120 arrays: lists, positional tuples, nav, footer, offices) + a lot of copy written directly in the page templates |
| Forms | 9 lead forms (`action="#"`) + the contact form — none stores anything today |
| Assets | `assets/{css,js,images(155 MB),icons,fonts,models}` — copied unchanged to `public/assets` |
| Environment | PHP 8.2.12, Composer 2.9.4, MariaDB 10.4.32 (XAMPP), mod_rewrite on. Laravel **12** fits. |
| Existing MySQL | Other projects' databases exist (incl. `iw`, which belongs to a *different* portfolio site). They are **not touched**. A new database `intelliworkz_cms` is used. |

## 2. Where the supplied prompt was kept, and where it was changed

Kept: separate admin panel (Bootstrap 5, never applied to the public site), Eloquent + migrations, server-side validation, CSRF, rate-limited login, secure uploads, no hard-coded credentials, tests, frontend appearance must not change.

Changed on purpose:

1. **New sibling Laravel app (`intelliworkz-cms`) instead of migrating in place.** The old site keeps working untouched and is used as the visual reference for before/after comparison.
2. **No generic "WYSIWYG page" model for the 12 existing pages.** They are fixed designs made of ~30 section types. Content is stored as *typed fields* (`content_fields`) and *repeatable lists* (`content_lists`) per page, so the template markup stays byte-identical. Generic pages (title/slug/rich text/SEO) are supported *in addition*, for new pages.
3. **One table per concern, not one per section.** Dedicated tables only where they pay off: users, pages, menu (services/footer), settings, inquiries, activity log. FAQs, metrics, workflow steps, offers… are lists inside the page editor.
4. **Existing `.php` URLs keep working** (`/seo-services.php` and `/seo-services`), so no link in the markup changes.
5. **Initial admin is created by an Artisan command** (`php artisan admin:create`), password prompted or generated — never stored in code.

## 3. Schema

`users` (+role, is_active, last_login_at) · `pages` (slug, template, is_system, title, SEO, status, content for generic pages) ·
`content_fields` (scope, section, key, label, type, value) · `content_lists` (scope, key, label, items JSON) ·
`menu_groups` / `menu_items` (header "Services" mega-menu + footer columns) · `site_settings` (company, logo, favicon, social, SEO defaults) ·
`contact_inquiries` · `activity_logs`.

## 4. Phases

1. Scaffold, migrations, models, auth, admin shell — *verify login/logout/guards*
2. Admin: settings, menu/services, pages (+field & list editor), inquiries, dashboard
3. Public layout + one page converted (home) — *verify HTML/pixel parity with the old site*
4. Convert the other 11 pages; seed the DB from the legacy data
5. Forms → inquiries, SEO head, publish/unpublish
6. Automated tests, parity re-run on every page, docs (`README_SETUP.md`)
