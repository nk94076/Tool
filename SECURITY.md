# Security Checklist

Verification notes for each control, and how to re-check it yourself.

## Authentication & Session

- [x] **Password hashing** — `password_hash()` / `password_verify()`, bcrypt cost 12 (`AuthController`, `create_super_admin.php`). No password is ever stored or logged in plaintext.
- [x] **Session hardening** — `App\Core\Session::start()` sets `HttpOnly`, `SameSite=Lax`, `Secure` (when `SESSION_SECURE_COOKIE=true`), a custom session name, and enforces a server-side idle timeout independent of the cookie's own expiry.
- [x] **Session fixation** — `Auth::login()` calls `session_regenerate_id(true)` on every successful login.
- [x] **Logout** — destroys the session server-side (`Session::destroy()`), not just the cookie.
- [x] **Account lockout** — 5 failed logins locks the account for 15 minutes (`users.locked_until`), independent of the IP-based rate limiter.
- [x] **OTP secrecy** — OTP codes are never stored in plaintext; `otp_verifications.otp_hash` is an HMAC-SHA256 of the code keyed by `APP_KEY`. Comparison uses `hash_equals()` (timing-safe).
- [x] **OTP lifecycle** — one-time use (`is_used` flag checked before and set after success), expiring (`expires_at`), rate-limited resend (cooldown) and verify (max attempts + separate `RateLimit` throttle on the endpoint).
- [x] **Password reset tokens** — random 32-byte token, only its SHA-256 hash is stored, 30-minute expiry, single use (cleared on success). The "email not found" and "email found" responses are identical to avoid account enumeration.

## Input / Output

- [x] **SQL injection** — every query in `app/Models/*` and `app/Services/*` uses PDO prepared statements with bound parameters; `PDO::ATTR_EMULATE_PREPARES` is `false`, so these are real server-side prepares, not string-substituted. No user input is ever concatenated into SQL.
- [x] **XSS** — all dynamic output in views goes through `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`). Email templates interpolate `{{variables}}` through the same escaping (`EmailTemplate::render()`), so a malicious "full name" can't inject markup into an email sent to others.
- [x] **CSRF** — every state-changing form includes a per-session token (`Csrf::field()`); `Controller::verifyCsrf()` checks it (`hash_equals`) before any mutation runs, both for form posts (`_csrf` field) and JSON/fetch calls (`X-CSRF-Token` header). Mismatch returns `419`, not a silent failure.
- [x] **IDOR** — Secret Santa endpoints re-derive "am I this assignment's santa/recipient?" from the logged-in session's user id on every request (`SecretSantaAssignment::recipientFor()`, `assignmentIdAsRecipient()`) rather than trusting an id parameter from the client. Employee profile edit endpoints check `Auth::id()` against the profile owner (or Super Admin) before allowing a write.

## Authorization

- [x] **Server-side permission checks on every protected route** — `routes/web.php` attaches `PermissionMiddleware::require('slug')` (or `requireSuperAdmin()`) to every `/admin/*` route; the sidebar's `can()` checks only control *visibility*, never *access*. Verified: an Employee-role account hitting `/admin/employees` or `/admin/settings` directly gets `403`.
- [x] **Super Admin cannot be modified via the UI** — `EmployeeController::guardSuperAdminAccount()` blocks activate/deactivate/lock/delete; `RoleController::assignUpdate()` blocks role reassignment; there is no UI path to demote or delete the account holding `is_super_admin = 1`.
- [x] **Locked profile enforcement is independent of role permissions** — `EmployeeController::guardLockedEditing()` requires `Auth::isSuperAdmin()` (not merely `employees.edit`) unless the specific profile has `profile_unlocked = 1`, so granting `employees.edit` to Admin does not, by itself, let Admin bypass the lock.
- [x] **Least-privilege defaults** — the seeded `employee` and `manager` roles do **not** include `employees.view` (which gates the admin panel exposing DOB/address/emergency contact); the public directory (safe fields only) needs no permission and remains available to everyone. A Super Admin must explicitly grant broader access.

## File Uploads

- [x] **MIME sniffing, not trusted headers** — `FileUploadService` uses `finfo` on the actual file content, cross-checked against an extension whitelist.
- [x] **Re-encoding** — the upload is decoded and re-encoded through GD (`imagecreatefromjpeg/png/webp` → `imagejpeg/png/webp`), which both strips any embedded non-image payload/EXIF and guarantees the file is a genuinely decodable image, not just a spoofed `Content-Type`.
- [x] **Size limit** — enforced against `config('uploads.max_mb')` before any processing.
- [x] **Randomized filenames** — `u{userId}_{random32hex}.{ext}`; the client-supplied filename is never used for the stored path.
- [x] **No execution in the upload directory** — `public/.htaccess` disables the PHP engine under `uploads/`.

## Data Exposure

- [x] **Directory shows only safe fields** — `User::search()` (used by both the public directory and the admin list) only returns photo/name/designation/department/status for the directory view; DOB, address, emergency contact, personal email are only ever returned by `EmployeeProfile::withUser()`, which is used exclusively by the profile's owner and by permission-gated admin views.
- [x] **Secret Santa mapping stays private by construction** — no query in the codebase joins `santa_user_id` and `recipient_user_id` back to a requesting user's own identity except `fullMappingForEvent()`, which is only reachable through `adminEmergencyReveal()` (password re-auth + audit log + explicit UI warning). Regular participant-facing queries select only the counterpart's public info, never the pairing's other half.
- [x] **Anonymous messages never expose the sender** — `SecretSantaMessage::forRecipient()` explicitly omits `sender_user_id` from its `SELECT`; there is no other query path that lets a recipient resolve their santa's identity.
- [x] **Errors never leak internals** — `display_errors` is off; the global exception handler logs to `storage/logs/app.log` and renders a generic branded error page (403/404/419/429/500) with no stack trace, SQL text, or file paths. Verified: the handler discards any partially-rendered output buffer before printing the error page, so a mid-render exception can't leak a fragment of the page that failed.

## Infrastructure

- [x] **App code lives outside the web root** — only `public/` is served; `app/`, `config/`, `database/`, `storage/`, `routes/`, `vendor/` are unreachable directly (defense in depth via a root `.htaccess` denying everything, on top of correctly pointing DocumentRoot at `public/`).
- [x] **`.env` and dotfiles blocked** — `public/.htaccess` denies any request matching `^\.`.
- [x] **Rate limiting** — DB-backed sliding window (`RateLimit` model) applied to signup (IP), login (IP + email, separately), OTP verify/resend, and password-reset requests. Exceeding the limit returns `429`, not a silent retry.
- [x] **Outbound mail cannot hang a request indefinitely** — `MailService` sets a 10-second SMTP timeout; a misconfigured or unreachable mail host degrades to a logged failure rather than blocking signup/login/OTP.
- [x] **HTTPS-ready** — `SESSION_SECURE_COOKIE` toggles the `Secure` cookie flag; Web Push requires a secure context in production browsers by design.

## What Was Actually Verified (not just written)

The items above were exercised against a live MariaDB instance during development, not just inspected by reading: signup domain validation, OTP hash/expiry/attempts/reuse rejection, login lockout path, CSRF `419` rejection, rate-limit `429` after the configured threshold, RBAC `403` for an under-privileged role, profile lock/unlock cycle with audit trail, and the full Secret Santa cycle including the privacy checks above. Two real bugs were found and fixed this way (duplicate SQL placeholders under real prepared statements, and an over-broad default permission grant) — see the git history for details.
