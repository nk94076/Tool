<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<title><?= e($title ?? 'Dashboard') ?> - <?= e(setting('company_name', 'Adhook Media')) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link href="<?= asset('css/app.css') ?>" rel="stylesheet">
<meta name="csrf-token" content="<?= e($csrfToken) ?>">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#5b3df6">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar d-none d-lg-flex flex-column">
    <div class="sidebar-brand">
      <div class="brand-badge">AH</div>
      <div>
        <div class="fw-bold small"><?= e(setting('company_name', 'Adhook Media')) ?></div>
        <div class="text-muted" style="font-size:.7rem">Employee Portal</div>
      </div>
    </div>
    <nav class="sidebar-nav flex-grow-1">
      <?php App\Core\View::partial('partials/nav_links', ['currentUser' => $currentUser]); ?>
    </nav>
    <div class="sidebar-footer small text-muted px-3 pb-3">
      &copy; <?= date('Y') ?> Adhook Media
    </div>
  </aside>

  <div class="app-main">
    <header class="app-header d-flex align-items-center justify-content-between">
      <button class="btn btn-icon d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-label="Menu">
        <i class="bi bi-list fs-4"></i>
      </button>
      <div class="d-none d-md-block">
        <h1 class="h5 mb-0"><?= e($title ?? '') ?></h1>
      </div>
      <div class="d-flex align-items-center gap-2 ms-auto">
        <div class="dropdown">
          <button class="btn btn-icon position-relative" type="button" id="notifBell" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell fs-5"></i>
            <span id="notifBadge" class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle d-none">0</span>
          </button>
          <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0" aria-labelledby="notifBell">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
              <span class="fw-semibold small">Notifications</span>
              <button class="btn btn-link btn-sm p-0" id="markAllReadBtn">Mark all read</button>
            </div>
            <div id="notifList" class="notif-list"><div class="text-center text-muted small py-4">Loading...</div></div>
            <div class="text-center border-top py-2">
              <a href="/notifications" class="small">View all</a>
            </div>
          </div>
        </div>
        <div class="dropdown">
          <button class="btn btn-icon d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
            <span class="avatar-sm"><?= e(mb_substr($currentUser['full_name'] ?? '?', 0, 1)) ?></span>
            <span class="d-none d-md-inline small fw-medium"><?= e($currentUser['full_name'] ?? '') ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <form action="/logout" method="post" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
              </form>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <main class="app-content">
      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>
      <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>

      <?= $content ?>
    </main>

    <nav class="mobile-bottom-nav d-lg-none">
      <a href="/dashboard" class="mobile-nav-item"><i class="bi bi-speedometer2"></i><span>Home</span></a>
      <a href="/directory" class="mobile-nav-item"><i class="bi bi-people"></i><span>Directory</span></a>
      <a href="/calendar" class="mobile-nav-item"><i class="bi bi-calendar3"></i><span>Calendar</span></a>
      <a href="/secret-santa" class="mobile-nav-item"><i class="bi bi-gift"></i><span>Santa</span></a>
      <a href="/profile" class="mobile-nav-item"><i class="bi bi-person"></i><span>Profile</span></a>
    </nav>
  </div>
</div>

<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileNav">
  <div class="offcanvas-header">
    <div class="d-flex align-items-center gap-2">
      <div class="brand-badge">AH</div>
      <span class="fw-bold"><?= e(setting('company_name', 'Adhook Media')) ?></span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <?php App\Core\View::partial('partials/nav_links', ['currentUser' => $currentUser]); ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<script src="<?= asset('js/push.js') ?>"></script>
</body>
</html>
