# Putting the website live on cPanel

The release is built on your own computer, uploaded in three pieces (application, pictures, database) and switched on with one small settings file.
Takes about 45 minutes the first time. You do **not** need SSH or the command line on the host.

## 0. What you need
* A cPanel hosting account with **PHP 8.2 or newer** and **MySQL** (any normal shared hosting has both)
* The domain pointed at that hosting, and an SSL certificate (cPanel → *SSL/TLS Status* → *Run AutoSSL*)
* FTP details (or just the cPanel *File Manager*)

## 1. Build the release (on your computer)
1. Optional: open the admin panel and delete test pages / test content you do not want on the live site.
2. Start XAMPP (MySQL must be running), then in the project folder:
   ```
   php deploy/build.php
   ```
   It takes 2–3 minutes and creates `deploy/dist/`:

   | File | What it is |
   |---|---|
   | `intelliworkz-app.zip` (8 MB) | the application (Laravel, PHP packages without developer tools) |
   | `intelliworkz-assets.zip` (150 MB) | all pictures, icons, fonts and 3D models of the website |
   | `database.sql` | your content as it is now: pages, texts, menus, settings, the admin user. Test inquiries, the activity log, sessions and cache are left out (add `--with-inquiries` to keep them) |
   | `env-production.txt` | the live settings file, with a fresh secret key already filled in |
   | `public_html/` | `index.php` + `.htaccess` for the "standard" folder layout (Layout B below) |

   Re-run the build whenever you changed code. **Never import `database.sql` over a live site that already has new content** – it would replace it. After go-live, the live database is the master copy.

## 2. Prepare cPanel
1. **PHP version** – *MultiPHP Manager* → select the domain → PHP **8.2** (or 8.3) → Apply.
2. **PHP extensions** – *Select PHP Version* → *Extensions*: make sure `mbstring`, `xml` (dom), `fileinfo`, `pdo_mysql`, `openssl`, `tokenizer`, `ctype`, `json` are ticked (most are by default).
3. **Database** – *MySQL® Databases*: create a database (e.g. `intelliworkz`), create a user with a strong password, and add the user to the database with **ALL PRIVILEGES**. Note the three names – cPanel prefixes them with your account name (`cpaneluser_intelliworkz`).
4. **Import** – *phpMyAdmin* → click the new database → *Import* → choose `database.sql` → *Go*. You should see about 13 tables (pages, content_fields, users …).

## 3. Upload the files
Large files: if the File Manager refuses the 150 MB zip, upload it with FTP (FileZilla) into the same folder, then extract it in the File Manager.

### Layout A – recommended (the site's web folder points at the app's `public` folder)
Possible for domains/subdomains in *cPanel → Domains → Manage → Document Root*.
1. In your home folder (`/home/cpaneluser/`) create the folder `intelliworkz-cms`. Upload `intelliworkz-app.zip` there and **Extract**.
2. Upload `intelliworkz-assets.zip` into `intelliworkz-cms/public/` and **Extract** (it creates `public/assets/`).
3. Set the domain's *Document Root* to `intelliworkz-cms/public`.

### Layout B – standard `public_html`
Use this when the document root cannot be changed. The application stays **outside** `public_html`, so nobody can reach its code or `.env`.
1. Create `/home/cpaneluser/intelliworkz-cms/`, upload `intelliworkz-app.zip` there, **Extract**.
2. Open `intelliworkz-cms/public/` (File Manager → Settings → tick *Show Hidden Files*), select everything inside it (including `.htaccess`) and **Move** it to `public_html/`.
3. Replace `public_html/index.php` with the file from `deploy/dist/public_html/index.php` (it tells PHP where the application is; if you used another folder name than `intelliworkz-cms`, change `$appFolder` in it).
4. Upload `intelliworkz-assets.zip` into `public_html/` and **Extract** (creates `public_html/assets/`).

## 4. The settings file
1. Open `deploy/dist/env-production.txt` in Notepad and change the lines marked `>>> CHANGE <<<`:
   * `APP_URL` – `https://www.your-domain.com` (no trailing slash)
   * `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` – from step 2.3
   * `MAIL_*` – create an e-mail account in cPanel (*Email Accounts*, e.g. `no-reply@your-domain.com`) and enter its host (`mail.your-domain.com`), user and password. This is how the contact-form inquiries reach your inbox.
2. Upload it into `intelliworkz-cms/` (next to the file `artisan`) and **rename it to `.env`** (a name that starts with a dot).
3. Do not touch `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`.

## 5. Folder permissions
These folders must be writable by the website: `intelliworkz-cms/storage` (with everything inside), `intelliworkz-cms/bootstrap/cache`, and the `uploads` folder (`public/uploads`, or `public_html/uploads` in Layout B). On cPanel the default **755** is normally enough; if the site later shows "permission denied" errors in `storage/logs/laravel.log`, set these folders to **775**. Never use 777.

## 6. Check it
Open these (replace the domain):

| Address | Expected |
|---|---|
| `https://your-domain.com/` | home page with pictures |
| `/about-us.php`, `/seo-services.php`, `/contact-us.php` | the pages |
| `/does-not-exist` | the friendly "We can't find that page" |
| `/.env` and `/vendor/autoload.php` | the same 404 page – **never** file contents |
| `/admin` | login page |

Then:
1. Log in at `/admin/login`. The admin account is the one from your computer (`admin@intelliworkz.test` with the password you set there).
2. **Immediately** go to *My profile* → change the e-mail address to your real one and set a new strong password.
3. *Users* → create an **Editor** account for the data-entry person (editors cannot delete, change settings or manage users).
4. *Company settings* → fill in *Inquiry notification e-mail* (and phone, social links).
5. Send a test message with the contact form: you should land on the Thank-you page, see the inquiry under *Contact inquiries* and receive the e-mail. Delete the test inquiry afterwards.
6. Upload a picture in some page's content editor to confirm uploads work.

## 7. HTTPS only
After the certificate is active, force https: add these lines at the very top of the `.htaccess` that is next to `index.php` (`public/.htaccess` or `public_html/.htaccess`), then reload the site:
```
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```
(If login keeps coming back to the login page before SSL works, set `SESSION_SECURE_COOKIE=false` in `.env` – and back to `true` once https works.)

## 8. After go-live
* **Data entry** happens in the live admin panel. The live database is now the only copy that matters.
* **Backups** – cPanel → *Backup* (full account) or *phpMyAdmin → Export* for the database, plus the `uploads` folder. Do it before each big editing session.
* **Updating the code later** – run `php deploy/build.php` again, upload `intelliworkz-app.zip` and extract it **over** the old files (your `.env` and `uploads` are not in the zip, so they stay). Do not re-import `database.sql`. If a new version adds database tables (the release notes will say so), they are applied with `php artisan migrate --force` in cPanel's *Terminal* (if your host has it) – or ask for a small SQL file.
* **Speed** (only if cPanel has *Terminal*): in `intelliworkz-cms` run `php artisan config:cache && php artisan route:cache && php artisan view:cache`. Run `php artisan optimize:clear` after changing `.env`.

## Troubleshooting
| Symptom | Cause / fix |
|---|---|
| White page or "500 Server Error" | open `intelliworkz-cms/storage/logs/laravel.log` – the last lines say why. Usual causes: wrong PHP version, missing extension, wrong `.env` values, `storage` not writable |
| "No application encryption key has been generated" | `.env` missing, misnamed (`env-production.txt`), or in the wrong folder (must be next to `artisan`) |
| Home page works, other pages give the host's 404 | `.htaccess` is missing (hidden file – tick *Show Hidden Files* when moving files) or `mod_rewrite` is off (ask the host) |
| Page text but no pictures/styling | `intelliworkz-assets.zip` extracted into the wrong folder: `assets/` must sit next to `index.php` |
| "SQLSTATE … Access denied" | `DB_*` values wrong, or the user was not added to the database |
| Inquiries saved but no e-mail | `MAIL_*` values wrong, or *Company settings → Inquiry notification e-mail* empty |
| Login loops back to the login page | `SESSION_SECURE_COOKIE=true` while the site is opened over http – use https or set it to `false` |
