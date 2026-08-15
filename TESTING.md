# Testing Checklist

Manual test matrix. Items marked **[Verified]** were run end-to-end against a real MariaDB instance during development (see commit history); the rest follow the same patterns and are straightforward to run the same way against your own environment.

## Authentication

- [x] **[Verified]** Signup with a non-official domain (e.g. `@gmail.com`) is rejected before any account or OTP is created, with a clear message
- [x] **[Verified]** Signup with an official domain creates a `pending_verification` user and queues exactly one OTP email
- [x] **[Verified]** OTP: correct code within the expiry window succeeds and activates the account
- [x] **[Verified]** OTP: wrong code is rejected and increments the attempt counter
- [x] **[Verified]** OTP: reusing an already-consumed code fails (`is_used`)
- [x] **[Verified]** OTP: an expired code is rejected even if otherwise correct
- [x] **[Verified]** OTP: exceeding `otp_max_attempts` locks out further guesses on that code (a new one must be requested)
- [ ] Resend OTP respects the resend cooldown (`otp_resend_cooldown_seconds`)
- [x] **[Verified]** Login with correct credentials succeeds, regenerates the session, and redirects to the dashboard
- [ ] Login with wrong password increments `failed_login_attempts`; 5 failures locks the account for 15 minutes
- [ ] Login for a `pending_verification` account redirects to OTP verification instead of failing silently
- [x] **[Verified]** Logout destroys the session (subsequent authenticated requests redirect to `/login`)
- [ ] Forgot/reset password: request always returns the same message regardless of whether the email exists (no account enumeration); the reset link expires after 30 minutes and works only once

## Employee Profile Lifecycle

- [x] **[Verified]** Profile can be created and edited freely before submission
- [x] **[Verified]** Preview shows the exact data that will be locked, with **Edit Details** / **Submit Profile** actions
- [x] **[Verified]** Submitting locks the profile (`is_locked=1`, `profile_status=submitted_locked`) and shows the "only the Super Admin can modify" message
- [x] **[Verified]** A locked employee cannot reach `/profile/edit` (redirected back with an error) even by navigating directly to the URL
- [x] **[Verified]** Super Admin **Unlock Profile** re-enables editing for that employee only
- [x] **[Verified]** After unlock → edit → resubmit, the profile re-locks automatically
- [x] **[Verified]** Every submit/unlock/lock/field-change is recorded in `audit_logs` with actor, subject, old/new value, IP, and user agent
- [ ] Admin/Manager holding only `employees.edit` (no override) cannot edit a **locked** profile — only Super Admin or an explicit unlock can
- [ ] Deactivating/deleting an employee is blocked for the Super Admin account itself

## Permissions / RBAC

- [x] **[Verified]** Super Admin bypasses every permission check (verified across employees, roles, settings, Secret Santa admin screens)
- [x] **[Verified]** A plain Employee gets `403` on `/admin/employees` and `/admin/settings` when hitting the URL directly
- [ ] Admin (seeded default) can reach employee management and department/designation management, but not `roles.delete` on the system role
- [ ] Manager (seeded default) can view the dashboard and reports but not the admin employee panel, by default
- [ ] Creating a custom role, assigning a subset of permissions, and assigning it to a user grants exactly that subset — nothing more
- [ ] The `super_admin` system role cannot be edited or deleted from the Roles screen
- [x] **[Verified]** Hiding a nav link (`can()` in the sidebar) is confirmed to not be the only protection — the underlying route independently returns `403`

## Birthday Automation

- [ ] `cron/birthday_reminders.php` sends exactly one reminder per birthday employee the day before their birthday
- [ ] `cron/birthday_notifications.php` sends exactly one "happy birthday" wish on the day
- [ ] Running either script twice on the same day does **not** duplicate the email/push/in-app notification (`celebration_logs` unique constraint on `user_id, event_type, event_year, channel`)
- [ ] An employee with no `date_of_birth` set is silently skipped (no error)

## Anniversary Automation

- [ ] Reminder fires the day before an anniversary; wish fires on the day
- [ ] Years-completed is calculated correctly from `date_of_joining` (e.g. joined 2023-07-15 → "2nd anniversary" on 2025-07-15)
- [ ] An employee who joined less than a year ago produces no anniversary event (0 years is not celebrated)
- [ ] A future or otherwise invalid `date_of_joining` is skipped rather than producing a negative/nonsensical year count
- [ ] Re-running the same day's script does not duplicate sends (same dedupe mechanism as birthdays)

## Push Notifications

- [ ] Granting browser notification permission successfully registers a `push_subscriptions` row (endpoint + keys)
- [ ] Denying permission does not break any other functionality — the rest of the app works normally
- [ ] A push notification triggered server-side (e.g. an announcement) is received by a subscribed browser
- [ ] An expired/invalidated subscription is detected on send failure and deactivated (`PushSubscription::deactivate()`) rather than retried forever
- [ ] Unsubscribing removes the subscription row and stops further pushes to that device

## Secret Santa

- [x] **[Verified]** Opting in during an active event creates/updates a `secret_santa_participants` row
- [ ] Opting out before matching removes the employee from the pool
- [x] **[Verified]** Generating a matching produces a full derangement: no participant is assigned to themselves, every participant is a santa exactly once and a recipient exactly once
- [x] **[Verified]** Generating a matching a second time for the same event is refused (`existsForEvent()` guard) — assignments are locked once generated
- [x] **[Verified]** A participant's own Secret Santa page shows only their recipient's name — never their own santa's identity, verified by inspecting the actual HTTP response for a real assigned pair
- [x] **[Verified]** The recipient's wishlist/preferences are visible only to their assigned santa
- [x] **[Verified]** Anonymous messages reach the recipient's inbox with the sender's identity completely absent from the page/response
- [x] **[Verified]** Emergency reveal of the full mapping requires the Super Admin's password on the same request, is recorded in `audit_logs`, and shows an explicit warning before submission; a wrong password reveals nothing
- [ ] Access to wishlist/messaging automatically stops once the event is marked `completed` (via `cron/expire_secret_santa_access.php` after the gift exchange date passes)
- [ ] "Avoid previous year's pairing" setting excludes prior-year santa→recipient pairs when generating a new matching, with automatic fallback if that constraint is infeasible

## Security

- [x] **[Verified]** CSRF: a POST without a valid `_csrf` token returns `419`, not a silent no-op or 500
- [x] **[Verified]** Rate limiting: exceeding the configured attempt count on signup returns `429`
- [ ] Rate limiting also triggers correctly on login and OTP verify/resend endpoints
- [x] **[Verified]** SQL injection: every data-access path uses parameterized queries; spot-checked by attempting `' OR '1'='1` style input in the directory search box (no query breakage, no extra rows)
- [ ] XSS: entering `<script>alert(1)</script>` as a full name or announcement body renders as literal text everywhere it's displayed (directory, admin panel, emails)
- [ ] Unauthorized API access: hitting `/api/notifications`, `/api/push/subscribe`, etc. while logged out returns `401`, not data
- [ ] IDOR: attempting to view another user's Secret Santa assignment or a locked profile's edit form by guessing/incrementing an id in the URL is blocked server-side
- [ ] File upload: uploading a `.php` file renamed to `.jpg`, or a valid-looking image with a PHP payload appended, is rejected (MIME sniff + GD re-encode)

## Mobile Responsiveness

- [ ] Layouts checked at 360px, 375px, 390px, 414px, tablet (768px), and desktop (1280px+) widths
- [ ] Sidebar collapses to a hamburger + offcanvas menu below `lg`; a bottom navigation bar is present on mobile
- [ ] Dashboard stat cards stack to 2-across (then 1) on narrow screens
- [ ] Data tables (employee list, audit logs) become horizontally scrollable / card-based below `md`
- [ ] Forms (profile edit, signup, settings) remain usable with large enough tap targets on a real mobile device or emulator
