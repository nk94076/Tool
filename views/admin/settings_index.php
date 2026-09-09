<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">System Settings</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>System Settings</h1>
      <p>Configure how the portal behaves for everyone.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-gear-fill"></i></span>
    <div class="dir-hero-script">Fine<br>Tune<br>Your Portal</div>
  </div>
</div>

<form method="post" action="/admin/settings">
  <?= $csrfField ?>

  <div class="card mb-3">
    <div class="card-head-x"><i class="bi bi-building text-primary"></i> General</div>
    <div class="card-body-x p-3 row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Company Name</label>
        <input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Timezone</label>
        <input type="text" name="timezone" class="form-control" value="<?= e($settings['timezone'] ?? 'Asia/Kolkata') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Allowed Official Email Domains</label>
        <input type="text" name="allowed_email_domains" class="form-control" value="<?= e($settings['allowed_email_domains'] ?? '') ?>">
        <div class="form-text">Comma-separated, e.g. @adhookmedia.com</div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Daily Notification Time</label>
        <input type="time" name="notification_time" class="form-control" value="<?= e($settings['notification_time'] ?? '09:00') ?>">
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-head-x"><i class="bi bi-shield-lock text-primary"></i> OTP</div>
    <div class="card-body-x p-3 row g-3">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">OTP Expiry (minutes)</label>
        <input type="number" min="1" name="otp_expiry_minutes" class="form-control" value="<?= e((string) ($settings['otp_expiry_minutes'] ?? 10)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Resend Cooldown (seconds)</label>
        <input type="number" min="10" name="otp_resend_cooldown_seconds" class="form-control" value="<?= e((string) ($settings['otp_resend_cooldown_seconds'] ?? 60)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Max Verification Attempts</label>
        <input type="number" min="1" name="otp_max_attempts" class="form-control" value="<?= e((string) ($settings['otp_max_attempts'] ?? 5)) ?>">
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-head-x"><i class="bi bi-bell text-primary"></i> Notifications</div>
    <div class="card-body-x p-3 row g-3">
      <div class="col-md-3 form-check ms-3">
        <input class="form-check-input" type="checkbox" name="birthday_reminder_enabled" value="1" id="s1" <?= !empty($settings['birthday_reminder_enabled']) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="s1">Birthday reminders</label>
      </div>
      <div class="col-md-3 form-check ms-3">
        <input class="form-check-input" type="checkbox" name="anniversary_reminder_enabled" value="1" id="s2" <?= !empty($settings['anniversary_reminder_enabled']) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="s2">Anniversary reminders</label>
      </div>
      <div class="col-md-3 form-check ms-3">
        <input class="form-check-input" type="checkbox" name="browser_notifications_enabled" value="1" id="s3" <?= !empty($settings['browser_notifications_enabled']) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="s3">Browser push notifications</label>
      </div>
      <div class="col-md-3 form-check ms-3">
        <input class="form-check-input" type="checkbox" name="email_notifications_enabled" value="1" id="s4" <?= !empty($settings['email_notifications_enabled']) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="s4">Email notifications</label>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-head-x"><i class="bi bi-gift text-primary"></i> Secret Santa Defaults</div>
    <div class="card-body-x p-3 row g-3">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Default Minimum Budget</label>
        <input type="number" name="default_secret_santa_min_budget" class="form-control" value="<?= e((string) ($settings['default_secret_santa_min_budget'] ?? 500)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Default Maximum Budget</label>
        <input type="number" name="default_secret_santa_max_budget" class="form-control" value="<?= e((string) ($settings['default_secret_santa_max_budget'] ?? 1500)) ?>">
      </div>
    </div>
  </div>

  <div class="text-end">
    <button class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i>Save Settings</button>
  </div>
</form>
