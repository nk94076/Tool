<h2 class="h5 fw-bold mb-1">Verify your email</h2>
<p class="text-muted small mb-4">Enter the 6-digit code we sent to <strong><?= e($email) ?></strong>.</p>

<form method="post" action="/verify-otp" novalidate>
  <?= $csrfField ?>
  <div class="mb-4">
    <label class="form-label small fw-medium">Verification Code</label>
    <input type="text" name="otp_code" class="form-control form-control-lg text-center letter-spacing-2" style="letter-spacing:.4em;font-weight:700" required maxlength="6" pattern="\d{6}" inputmode="numeric" autofocus>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Verify</button>
</form>

<form method="post" action="/resend-otp" class="text-center small mt-3 mb-0">
  <?= $csrfField ?>
  <span class="text-muted">Didn't get a code?</span>
  <button type="submit" class="btn btn-link btn-sm p-0 align-baseline">Resend code</button>
</form>
