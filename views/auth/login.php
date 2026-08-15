<h2 class="h5 fw-bold mb-1">Welcome back</h2>
<p class="text-muted small mb-4">Log in with your official email address.</p>

<form method="post" action="/login" novalidate>
  <?= $csrfField ?>
  <div class="mb-3">
    <label class="form-label small fw-medium">Official Email</label>
    <input type="email" name="official_email" class="form-control" value="<?= old('official_email') ?>" required autofocus>
  </div>
  <div class="mb-2">
    <label class="form-label small fw-medium">Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <div class="text-end mb-4">
    <a href="/forgot-password" class="small">Forgot password?</a>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Log In</button>
</form>

<p class="text-center small text-muted mt-4 mb-0">Don't have an account? <a href="/signup">Sign up</a></p>
