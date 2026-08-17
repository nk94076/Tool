<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<title><?= e($title ?? 'Adhook Employee Portal') ?> - <?= e(setting('company_name', 'Adhook Media')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="auth-body">
<div class="auth-wrapper d-flex align-items-center justify-content-center min-vh-100 py-4">
  <div class="auth-card card shadow-sm border-0">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <img src="<?= asset('img/logo.png') ?>" alt="<?= e(setting('company_name', 'Adhook Media')) ?>" class="auth-logo mb-2">
        <p class="text-muted small mb-0">Employee Portal</p>
      </div>

      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success py-2 small"><?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger py-2 small"><?= e($msg) ?></div>
      <?php endif; ?>

      <?= $content ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
