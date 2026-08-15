<h2 class="h5 fw-bold mb-1">Create your account</h2>
<p class="text-muted small mb-4">Use your official <strong><?= e(implode(', ', (new App\Models\SystemSetting())->allowedEmailDomains())) ?></strong> email address to sign up.</p>

<form method="post" action="/signup" novalidate>
  <?= $csrfField ?>
  <div class="mb-3">
    <label class="form-label small fw-medium">Full Name</label>
    <input type="text" name="full_name" class="form-control" value="<?= old('full_name') ?>" required maxlength="150">
  </div>
  <div class="mb-3">
    <label class="form-label small fw-medium">Official Email</label>
    <input type="email" name="official_email" class="form-control" value="<?= old('official_email') ?>" required placeholder="you@adhookmedia.com">
  </div>
  <div class="mb-3">
    <label class="form-label small fw-medium">Password</label>
    <input type="password" name="password" class="form-control" required minlength="8">
    <div class="form-text">Minimum 8 characters.</div>
  </div>
  <div class="mb-4">
    <label class="form-label small fw-medium">Confirm Password</label>
    <input type="password" name="confirm_password" class="form-control" required minlength="8">
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Sign Up</button>
</form>

<p class="text-center small text-muted mt-4 mb-0">Already have an account? <a href="/login">Log in</a></p>
