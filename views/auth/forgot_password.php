<h2 class="h5 fw-bold mb-1">Forgot password</h2>
<p class="text-muted small mb-4">Enter your official email and we'll send you a reset link.</p>

<form method="post" action="/forgot-password" novalidate>
  <?= $csrfField ?>
  <div class="mb-4">
    <label class="form-label small fw-medium">Official Email</label>
    <input type="email" name="official_email" class="form-control" required autofocus>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Send Reset Link</button>
</form>

<p class="text-center small text-muted mt-4 mb-0"><a href="/login">Back to login</a></p>
