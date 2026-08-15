# Adhook Employee Portal

A production-ready internal employee management portal for Adhook Media: authentication with official-domain-only signup and email OTP verification, role-based access control with a configurable permission system, employee profile onboarding with a submit-and-lock workflow, birthday/work-anniversary automation, email + browser push notifications, a full Secret Santa module with strict privacy guarantees, and an audit trail across every sensitive action.

Built with plain PHP 8.2+ (no framework), PDO, MySQL 8+, Bootstrap 5, and vanilla JavaScript — see [Architecture](#architecture) below.

---

## Table of Contents

1. [Requirements](#1-requirements)
2. [Project Structure](#2-project-structure)
3. [Installation](#3-installation)
4. [Environment Configuration](#4-environment-configuration)
5. [Database Setup](#5-database-setup)
6. [Creating the Super Admin](#6-creating-the-super-admin)
7. [SMTP Setup](#7-smtp-setup)
8. [Web Push / VAPID Setup](#8-web-push--vapid-setup)
9. [Apache Configuration](#9-apache-configuration)
10. [Cron Configuration](#10-cron-configuration)
11. [SSL / HTTPS](#11-ssl--https)
12. [Production Deployment Checklist](#12-production-deployment-checklist)
13. [Role-Based Access Control](#13-role-based-access-control)
14. [Business Rules Enforced](#14-business-rules-enforced)
15. [Security Checklist](#15-security-checklist)
16. [Testing Checklist](#16-testing-checklist)
17. [Troubleshooting](#17-troubleshooting)

---

## 1. Requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `gd`, `openssl`, `fileinfo`, `json`
- MySQL **8.0+** or MariaDB 10.6+
- Composer 2.x
- Apache 2.4+ with `mod_rewrite` (or equivalent Nginx config, see below)
- An SMTP account (Office 365, Gmail Workspace, SES, SendGrid, etc.)
- Cron access (Linux `cron`, or any scheduler that can run `php` on a schedule)
- A domain with HTTPS in production (Web Push requires a secure context)

## 2. Project Structure

```
/app
  /Controllers    HTTP request handlers
  /Models         PDO data-access classes (one per table/domain)
  /Services       Business logic (mail, OTP, push, birthday/anniversary, Secret Santa matching...)
  /Middleware     Auth, permission, and rate-limit gates run before controllers
  /Core           Router, Database (PDO singleton), Session, Csrf, Auth, base Controller/Model/View
  /Helpers        Global helper functions (e(), csrf_field(), setting(), can(), ...)
/config           bootstrap.php (autoload/env/error handling) + config.php (typed config)
/database
  /migrations     Numbered .sql files, applied by database/migrate.php
  /seeders        seed.php (roles/permissions/settings/templates) + create_super_admin.php
/public           Web root — point your VirtualHost DocumentRoot here
  index.php       Front controller
  .htaccess       Rewrite rules (clean URLs, blocks .php access, blocks dotfiles)
  service-worker.js, manifest.json
  /assets         css/js (vanilla JS, Bootstrap 5 via CDN)
  /uploads        Profile photos (PHP execution disabled here)
/routes/web.php   All route definitions with their middleware
/cron             Standalone CLI scripts, one per scheduled job
/storage/logs     App error log (never exposed publicly)
/views            Plain PHP templates: layouts/, auth/, admin/, employee/, errors/, partials/
```

This is a lightweight MVC: `routes/web.php` maps a clean URL + HTTP method to `Controller@action`, controllers call Models/Services and render a View. There is no ORM — Models are thin PDO wrappers using prepared statements throughout.

## 3. Installation

```bash
git clone <this-repo> adhook-portal
cd adhook-portal
composer install --no-dev --optimize-autoloader
cp .env.example .env
```

Point your web server's document root at the `public/` directory — **never** the project root. A stray misconfiguration is defended in depth by the root `.htaccess` (`Require all denied`), but the correct fix is always the DocumentRoot.

## 4. Environment Configuration

Edit `.env` (never commit this file):

```
APP_URL=https://portal.adhookmedia.com
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Kolkata
APP_KEY=base64:...                 # generate: php -r "echo base64_encode(random_bytes(32));"

DB_HOST=127.0.0.1
DB_NAME=adhook_portal
DB_USER=adhook_portal_user
DB_PASS=...

MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=noreply@adhookmedia.com
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_EMAIL=noreply@adhookmedia.com
MAIL_FROM_NAME="Adhook Employee Portal"

VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
VAPID_SUBJECT=mailto:admin@adhookmedia.com

SESSION_SECURE_COOKIE=true          # keep true in production (HTTPS only)
CRON_SECRET=...                     # random string, currently reserved for future HTTP-triggered cron
```

`APP_KEY` is used to HMAC-hash OTP codes — treat it like any other secret; rotating it invalidates all outstanding (unused) OTPs.

## 5. Database Setup

```bash
mysql -u root -p -e "CREATE DATABASE adhook_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p -e "CREATE USER 'adhook_portal_user'@'localhost' IDENTIFIED BY 'CHANGE_ME';"
mysql -u root -p -e "GRANT ALL ON adhook_portal.* TO 'adhook_portal_user'@'localhost'; FLUSH PRIVILEGES;"

php database/migrate.php          # creates all tables (idempotent, tracked in a `migrations` table)
php database/seeders/seed.php     # permissions, default roles, system settings, email templates, sample departments/designations
```

`seed.php` is safe to re-run — it uses `INSERT IGNORE` / `ON DUPLICATE KEY UPDATE` and will not overwrite settings or role/permission assignments a Super Admin has since changed through the UI.

## 6. Creating the Super Admin

There is exactly **one** Super Admin, and the only way to create it is this CLI script — there is intentionally no web-based "create super admin" endpoint:

```bash
php database/seeders/create_super_admin.php
# or non-interactively:
php database/seeders/create_super_admin.php "Full Name" "you@adhookmedia.com" "a-strong-password-12chars+"
```

The script refuses to run if a Super Admin already exists, and validates the email against `system_settings.allowed_email_domains`.

## 7. SMTP Setup

Fill in `MAIL_*` in `.env`. Any standard SMTP provider works (PHPMailer under the hood). Test it once configured:

```bash
php -r '
require "config/bootstrap.php";
$ok = App\Services\MailService::sendNow("you@adhookmedia.com", "Test", "<p>It works.</p>");
echo $ok ? "Sent\n" : "Failed — check storage/logs/app.log and the email_logs table\n";
'
```

All send attempts (success and failure) are recorded in `email_logs`, viewable at **Admin → Email Logs**. OTP/login/signup mail is sent synchronously with a bounded 10-second timeout so a slow or unreachable SMTP host cannot hang a request; everything else (birthday/anniversary wishes, announcements) goes through `email_queue`, drained by `cron/email_queue_worker.php`.

## 8. Web Push / VAPID Setup

Generate a VAPID key pair once:

```bash
php cron/generate_vapid_keys.php
```

Paste the output into `.env` as `VAPID_PUBLIC_KEY` / `VAPID_PRIVATE_KEY`. Web Push requires HTTPS (except on `localhost` during development) — browsers refuse to grant the Notification permission over plain HTTP on a real domain.

Employees opt in from the dashboard ("Enable Browser Notifications" button); if they decline or their browser doesn't support Push, the app continues to work normally — push is always best-effort. Add real PNG icons at `public/assets/img/icon-192.png` and `icon-512.png` for the notification/PWA icons (placeholders are not included in the repo).

## 9. Apache Configuration

Example VirtualHost:

```apache
<VirtualHost *:443>
    ServerName portal.adhookmedia.com
    DocumentRoot /var/www/adhook-portal/public

    <Directory /var/www/adhook-portal/public>
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile      /etc/letsencrypt/live/portal.adhookmedia.com/fullchain.pem
    SSLCertificateKeyFile   /etc/letsencrypt/live/portal.adhookmedia.com/privkey.pem
</VirtualHost>

<VirtualHost *:80>
    ServerName portal.adhookmedia.com
    Redirect permanent / https://portal.adhookmedia.com/
</VirtualHost>
```

`public/.htaccess` handles the rest: every request is routed through `index.php` (no `.php` ever appears in a public URL), `.php` files cannot be requested directly, dotfiles (`.env`, `.git`) are denied, and PHP execution is disabled inside `public/uploads`.

**Nginx equivalent**, if you use it instead:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root/index.php;
}
location ~ /\. { deny all; }
location ^~ /uploads/ { location ~ \.php$ { deny all; } }
```

## 10. Cron Configuration

All jobs are idempotent — safe to re-run without duplicate emails/notifications (dedup is enforced by the `celebration_logs` unique constraint for birthdays/anniversaries, and by status transitions for the email queue).

```cron
# Adhook Employee Portal — run as the web server user
PHP=/usr/bin/php
APP=/var/www/adhook-portal

# Birthday & anniversary reminders/wishes — once daily, early morning
0 6 * * * $PHP $APP/cron/birthday_reminders.php      >> $APP/storage/logs/cron.log 2>&1
5 6 * * * $PHP $APP/cron/birthday_notifications.php   >> $APP/storage/logs/cron.log 2>&1
10 6 * * * $PHP $APP/cron/anniversary_reminders.php    >> $APP/storage/logs/cron.log 2>&1
15 6 * * * $PHP $APP/cron/anniversary_notifications.php >> $APP/storage/logs/cron.log 2>&1

# Email queue worker — every minute
* * * * * $PHP $APP/cron/email_queue_worker.php >> $APP/storage/logs/cron.log 2>&1

# Housekeeping — once daily
30 2 * * * $PHP $APP/cron/cleanup_expired_otp.php          >> $APP/storage/logs/cron.log 2>&1
35 2 * * * $PHP $APP/cron/expire_secret_santa_access.php    >> $APP/storage/logs/cron.log 2>&1
40 2 * * * $PHP $APP/cron/cleanup_stale_data.php             >> $APP/storage/logs/cron.log 2>&1
```

Adjust the hour to your `APP_TIMEZONE`. `notification_time` in System Settings documents the intended send time for admins; the crontab above is the actual enforcement point — keep them in sync if you change one.

## 11. SSL / HTTPS

- Use Let's Encrypt (`certbot --apache` or `--nginx`) or your CA of choice.
- `SESSION_SECURE_COOKIE=true` in `.env` makes the session cookie `Secure` — **do not** enable this until HTTPS is actually serving the site, or logins will silently fail to persist.
- Web Push subscriptions require HTTPS in production browsers.

## 12. Production Deployment Checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `.env` is not committed and is not web-readable (confirm `curl https://.../.env` → 403/404)
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] DocumentRoot points at `public/`, not the project root
- [ ] Migrations applied, seeded, Super Admin created
- [ ] Real SMTP credentials configured and test email sent successfully
- [ ] VAPID keys generated and set
- [ ] Cron jobs installed (see §10)
- [ ] HTTPS enforced, `SESSION_SECURE_COOKIE=true`
- [ ] `storage/logs/` is writable by the web server user but not web-accessible
- [ ] `public/uploads/` is writable by the web server user
- [ ] File upload limits (`MAX_UPLOAD_MB`) match `php.ini`'s `upload_max_filesize`/`post_max_size`
- [ ] Default/sample departments & designations reviewed and adjusted for the real org chart

## 13. Role-Based Access Control

Four seeded roles: **Super Admin** (system role, bypasses every permission check, cannot be deleted/edited/removed through the UI), **Admin**, **Manager**, **Employee**. Permissions are not hard-coded to role names — the Super Admin can create arbitrary custom roles at **Admin → Roles & Permissions** and assign any combination of the permission slugs listed in the schema (`employees.*`, `roles.*`, `departments.manage`, `secret_santa.manage`, `system_settings.manage`, etc.).

Every protected route is gated server-side in `routes/web.php` via `PermissionMiddleware::require('slug')`, re-checked on every request from the database — the sidebar only *hides* links a user can't use (`can()` in `views/partials/nav_links.php`); it never *grants* access. A determined user hitting a URL directly without the permission gets a real `403`, not just a hidden button.

## 14. Business Rules Enforced

A non-exhaustive map from the spec's "Important Business Rules" to where they're enforced in code:

| Rule | Enforced in |
|---|---|
| One Super Admin, not removable via UI | `create_super_admin.php` refuses if one exists; `EmployeeController::guardSuperAdminAccount()` blocks deactivate/delete/lock on `is_super_admin=1` |
| Official-domain-only signup | `AuthController::isOfficialDomain()` against `system_settings.allowed_email_domains`, checked before any account/OTP is created |
| OTP hashed, one-time, rate-limited, expiring | `OtpService` (HMAC hash, `is_used` flag, `expires_at`, `attempts`/`max_attempts`), `RateLimit` model for resend/verify throttling |
| Preview before submit; lock after submit | `ProfileController::preview()` / `submit()`; `employee_profiles.is_locked` |
| Only Super Admin edits/unlocks locked profiles | `EmployeeController::guardLockedEditing()` — checked server-side regardless of what permissions a role holds |
| No duplicate birthday/anniversary sends | `celebration_logs` unique key on `(user_id, event_type, event_year, channel)`; `CelebrationLog::claim()` |
| Secret Santa: no self-match, unique assignment, locked after generation | `SecretSantaService::tryAssign()` + `secret_santa_assignments` unique keys + `chk_ssa_not_self` CHECK constraint + `existsForEvent()` guard |
| Secret Santa mapping stays private | `SecretSantaAssignment::recipientFor()`/`assignmentIdAsRecipient()` never select the counterpart identity; full mapping only via `adminEmergencyReveal()` (password re-auth + audit log) |
| All admin actions audited | `AuditService::log()` called from every mutating controller action touching employee/role/system data |

## 15. Security Checklist

- [x] Passwords hashed with `password_hash()` (bcrypt), verified with `password_verify()`
- [x] 100% PDO prepared statements, `EMULATE_PREPARES` disabled (real server-side prepares)
- [x] CSRF token per session, verified on every state-changing request (`Controller::verifyCsrf()`), `419` on mismatch
- [x] Output escaped via `e()` (`htmlspecialchars`) everywhere user data is rendered
- [x] Session: HttpOnly, SameSite=Lax, Secure (when `SESSION_SECURE_COOKIE=true`), regenerated on login, idle-timeout enforced server-side
- [x] Login/signup/OTP rate limiting (DB-backed sliding window, IP- and email-keyed)
- [x] Every admin route re-checks permissions server-side (`PermissionMiddleware`) — UI hiding is not treated as access control
- [x] File uploads: MIME sniffed via `finfo` (not trusted client header), re-encoded through GD (strips payloads/EXIF, rejects non-images), extension whitelist, size limit, randomized filenames, PHP execution disabled in the upload directory
- [x] No stack traces/SQL errors/credentials ever reach the browser — friendly 403/404/419/429/500 pages, real errors go to `storage/logs/app.log` only
- [x] `.env`, `.git`, and all dotfiles blocked at the web server; app source lives outside `public/`
- [x] IDs/permissions are never trusted from the client — every "who am I" and "can I do this" check re-queries the DB from the session's user id
- [x] Audit log captures actor, subject, action, field, old/new value, IP, user agent, timestamp for sensitive actions

See `SECURITY.md` for the full checklist and manual verification notes.

## 16. Testing Checklist

See `TESTING.md` for the complete manual test matrix (authentication, profile lifecycle, permissions, birthday/anniversary automation, push, Secret Santa, and security). The core flows below were verified end-to-end against a real MySQL/MariaDB instance during development:

- Signup with invalid domain rejected; valid domain creates a `pending_verification` user and queues an OTP
- OTP: wrong code rejected, correct code accepted once, reused code rejected, expired code rejected, max-attempts lockout enforced
- Login → session regenerated → dashboard renders role-appropriate content
- Profile: edit → preview → submit → locked; locked profile blocks further self-edits; Super Admin unlock re-enables editing; every step audit-logged
- RBAC: a plain Employee gets `403` on `/admin/employees` and `/admin/settings`; Super Admin bypasses all checks
- CSRF: request without a valid token gets `419`
- Rate limiting: exceeding the configured attempt count gets `429`
- Secret Santa: matching generated with no self-assignments and a full derangement; a participant sees only their recipient's name, never their own santa's identity; anonymous messages reach the recipient with the sender identity fully absent from the response; emergency reveal requires the Super Admin's password and is audit-logged

## 17. Troubleshooting

- **500 on every page**: check `storage/logs/app.log` — almost always a DB connection issue (`.env` credentials) or a missing `vendor/` (`composer install`).
- **Public URLs show `.php`**: `mod_rewrite` isn't enabled or `AllowOverride All` is missing — see §9.
- **OTP emails never arrive**: check `email_logs` for the failure reason; verify SMTP credentials and that outbound port 587/465 isn't blocked by your host's firewall.
- **Push notifications don't appear**: confirm `VAPID_PUBLIC_KEY`/`VAPID_PRIVATE_KEY` are set, the site is served over HTTPS, and the employee actually granted the browser permission (check `push_subscriptions` for their user).
- **Duplicate birthday emails**: check for more than one crontab entry running the same script, or a manually-run script alongside a scheduled one — `celebration_logs` will only allow the collision on the *first* concurrent writer to reach the unique key; investigate rather than disabling the constraint.
