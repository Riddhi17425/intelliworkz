# Intelliworkz — website + admin panel (Laravel 12)

The public website is the original Intelliworkz design, unchanged, now rendered from a MySQL database.
Everything on it (texts, pictures, lists, menus, SEO, logo, contact details) is edited in a separate admin panel at **`/admin`**.

| | |
|---|---|
| Website | `http://127.0.0.1:8000/` (12 pages, same addresses as before: `/about-us.php`, `/seo-services.php`, …) |
| Admin panel | `http://127.0.0.1:8000/admin` |
| Stack | PHP 8.2 · Laravel 12 · MySQL/MariaDB · Bootstrap 5 (admin only) · Quill (rich text) |
| Original PHP site | `C:\xampp\htdocs\intelliworkz` — untouched, kept as the visual reference |

## 1. Set up on XAMPP (Windows)

Needs XAMPP with PHP 8.2+ and MySQL running, and Composer.

```bash
cd C:\xampp\htdocs\intelliworkz-cms

composer install
copy .env.example .env            # then edit DB_* if your MySQL user/password differ
php artisan key:generate

# create an empty database (phpMyAdmin, or:)
mysql -u root -e "CREATE DATABASE intelliworkz_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

php artisan migrate               # creates the tables (never drops anything)
php artisan content:import        # loads the site's texts/lists/menus/settings into the database
php artisan admin:create          # asks for e-mail, name and a password (or use --generate to get a random one) — nothing is hard-coded
php artisan serve                 # http://127.0.0.1:8000
```

`content:import` only fills what is missing. Re-running it **never overwrites edits** unless you add `--force`
(which asks for confirmation, or pass `--yes`). Use `--force` only to reset the content to the original site.

Uploaded pictures are stored in `public/uploads` (no `storage:link` needed on Windows).
Make sure that folder is writable. It is git-ignored.

### Running under Apache instead of `php artisan serve`
Point a virtual host's `DocumentRoot` at the **`public`** folder (never at the project root), with `AllowOverride All`
so `public/.htaccess` can rewrite URLs. Set `APP_URL` in `.env` to that address.
`.env`, `vendor/`, `app/` etc. must stay outside the web root — they are, as long as only `public/` is exposed.

### Going live
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain
```
then `php artisan config:cache && php artisan route:cache && php artisan view:cache`, serve over HTTPS and set
`SESSION_SECURE_COOKIE=true`. Give the database user only the rights it needs. Configure `MAIL_*` if you want
enquiries e-mailed as well (see below).

## 2. What the admin panel does

| Section | What you can do |
|---|---|
| **Dashboard** | Real numbers: pages, menu entries, inquiries (unread / this week, 14-day chart), content blocks, latest inquiries, recent activity |
| **Pages** | The 12 original pages: edit SEO (title, description, keywords, share image), publish / unpublish, open the content editor. New pages: title, address, rich text, featured image, SEO, draft/published, delete |
| **Page content editor** | Every text, image, link and list of a page, grouped by section. Replace/remove pictures (with preview), add / remove / reorder list rows (FAQs, tools, steps…). Colours, spacing and layout are fixed so the design cannot be broken |
| **Services & menus** | Header "Services" mega-menu (add/rename/reorder/hide groups and links, link to a page or any URL) and the footer link columns |
| **Header, footer & shared** | Texts used on every page: header button, call-to-action, offices, dark showcase slider, engagement models |
| **Contact inquiries** | Everything sent from the website forms: search, filter (read/unread, page, dates), sort, read/unread, bulk actions, CSV export, delete (admin) |
| **Company settings** *(admin)* | Company name, copyright, footer text, header/footer logo, favicon, certification badge, notification e-mail, phone, social links, default SEO description + share image |
| **Users** *(admin)* | Create / edit / disable users, roles, password reset. The last active administrator cannot be removed or demoted |
| **Activity log / My profile** | Who changed what and when; change own name, e-mail, password |

**Roles** — *Editor*: edits content, pages, menus, reads inquiries. *Administrator*: all of that plus deleting, company settings and users.

Everything you save is live immediately (caches are cleared on save).

## 3. Website forms

All 9 forms of the site (the lead form on 8 service pages + the contact page) post to `POST /inquiries`: validated, stored in `contact_inquiries`, shown in the admin
panel. They work with JavaScript (inline success/error message) and without it (redirect back with a message).
Protection: CSRF token, hidden honeypot field, rate limit (5 per minute and 40 per day per IP), plain-text storage.
To also receive each inquiry by e-mail, set **Company settings → Contact details → Inquiry notification e-mail**
and configure `MAIL_*` in `.env` (`MAIL_MAILER=log` just writes mails to `storage/logs`).

## 4. Security notes

* Passwords hashed (bcrypt), login rate-limited (5 failures / minute per e-mail+IP), session regenerated on login, disabled accounts are signed out immediately.
* CSRF on every form; Eloquent/query builder only (no raw SQL with user input); mass assignment guarded (role, slug, scope… are set explicitly, never from a request).
* All editable text passes `App\Support\Html`: scripts, event handlers, `style` attributes and `javascript:`/`data:` links are removed; links are checked.
* Uploads: type + size validated (jpg/png/webp/gif ≤ 5 MB, SVG only for logos and sanitised), random file names, stored under `public/uploads`.
* Admin responses are sent `no-store` + `noindex`; `.env` and application code are not inside `public/`.
* No credentials in source; the first administrator is created with `php artisan admin:create`.

## 5. Tests

```bash
php artisan test            # 95 tests: auth, roles, inquiries, pages, content editor, menus, settings, users, sanitising
```
Tests run on an in-memory SQLite database and the real imported content — they never touch your MySQL data.

## 6. Project map

```
app/Http/Controllers/Admin/*      admin panel          app/Http/Controllers/{Page,Inquiry}Controller   public site
app/Support/{Html,Media,Content}  sanitising, uploads, content lookup (cached)
app/Content/ListSchema.php        how each repeatable list is edited and saved   config/content_lists.php   list definitions
resources/views/admin/**          admin panel (Bootstrap 5)                      resources/views/site/**   public pages (original markup)
database/migrations/**            schema                                         database/seeders/content/*.json   original content
tools/                            one-off converter used to turn the PHP site into these templates
docs/IMPLEMENTATION_PLAN.md       inspection findings, decisions, schema
```

## 7. Troubleshooting

* **Blank page / 500** — look at `storage/logs/laravel.log`; check `.env` DB settings; `php artisan config:clear`.
* **Pictures don't upload** — `public/uploads` must be writable; PHP `upload_max_filesize` ≥ 5M (`php.ini`).
* **Changed `.env` but nothing happens** — `php artisan config:clear`.
* **Forgot the admin password** — `php artisan admin:create you@example.com` sets a new password for that existing user (it also creates the user if missing), or reset it from **Users** with another administrator.
