<h2 class="h5 fw-bold mb-1">Reset password</h2>
<p class="text-muted small mb-4">Choose a new password for your account.</p>

<form method="post" action="/reset-password" novalidate>
  <?= $csrfField ?>
  <input type="hidden" name="token" value="<?= e($token) ?>">
  <input type="hidden" name="email" value="<?= e($email) ?>">
  <div class="mb-3">
    <label class="form-label small fw-medium">New Password</label>
    <input type="password" name="password" class="form-control" required minlength="8" autofocus>
  </div>
  <div class="mb-4">
    <label class="form-label small fw-medium">Confirm New Password</label>
    <input type="password" name="confirm_password" class="form-control" required minlength="8">
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Reset Password</button>
</form>
