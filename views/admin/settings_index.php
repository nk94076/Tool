<h2 class="h5 fw-bold mb-3">System Settings</h2>

<form method="post" action="/admin/settings">
  <?= $csrfField ?>

  <div class="card mb-3">
    <div class="card-body row g-3">
      <h3 class="h6 fw-bold">General</h3>
      <div class="col-md-6">
        <label class="form-label small">Company Name</label>
        <input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Timezone</label>
        <input type="text" name="timezone" class="form-control" value="<?= e($settings['timezone'] ?? 'Asia/Kolkata') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Allowed Official Email Domains</label>
        <input type="text" name="allowed_email_domains" class="form-control" value="<?= e($settings['allowed_email_domains'] ?? '') ?>">
        <div class="form-text">Comma-separated, e.g. @adhookmedia.com</div>
      </div>
      <div class="col-md-6">
        <label class="form-label small">Daily Notification Time</label>
        <input type="time" name="notification_time" class="form-control" value="<?= e($settings['notification_time'] ?? '09:00') ?>">
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body row g-3">
      <h3 class="h6 fw-bold">OTP</h3>
      <div class="col-md-4">
        <label class="form-label small">OTP Expiry (minutes)</label>
        <input type="number" min="1" name="otp_expiry_minutes" class="form-control" value="<?= e((string) ($settings['otp_expiry_minutes'] ?? 10)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Resend Cooldown (seconds)</label>
        <input type="number" min="10" name="otp_resend_cooldown_seconds" class="form-control" value="<?= e((string) ($settings['otp_resend_cooldown_seconds'] ?? 60)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Max Verification Attempts</label>
        <input type="number" min="1" name="otp_max_attempts" class="form-control" value="<?= e((string) ($settings['otp_max_attempts'] ?? 5)) ?>">
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body row g-3">
      <h3 class="h6 fw-bold">Notifications</h3>
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
    <div class="card-body row g-3">
      <h3 class="h6 fw-bold">Secret Santa Defaults</h3>
      <div class="col-md-4">
        <label class="form-label small">Default Minimum Budget</label>
        <input type="number" name="default_secret_santa_min_budget" class="form-control" value="<?= e((string) ($settings['default_secret_santa_min_budget'] ?? 500)) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Default Maximum Budget</label>
        <input type="number" name="default_secret_santa_max_budget" class="form-control" value="<?= e((string) ($settings['default_secret_santa_max_budget'] ?? 1500)) ?>">
      </div>
    </div>
  </div>

  <div class="text-end">
    <button class="btn btn-primary px-4">Save Settings</button>
  </div>
</form>
