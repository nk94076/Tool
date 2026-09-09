<?php
$templateTheme = [
    'otp_verification' => ['ic' => 'bi-shield-lock-fill', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
    'welcome' => ['ic' => 'bi-envelope-heart-fill', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'],
    'profile_unlock' => ['ic' => 'bi-unlock-fill', 'bg' => 'var(--green-tint)', 'fg' => 'var(--green)'],
    'birthday_reminder' => ['ic' => 'bi-cake2-fill', 'bg' => 'var(--amber-tint)', 'fg' => 'var(--amber)'],
    'birthday_today' => ['ic' => 'bi-cake2-fill', 'bg' => 'var(--amber-tint)', 'fg' => 'var(--amber)'],
    'anniversary_reminder' => ['ic' => 'bi-gift-fill', 'bg' => '#fdf2f8', 'fg' => '#db2777'],
    'anniversary_today' => ['ic' => 'bi-gift-fill', 'bg' => '#fdf2f8', 'fg' => '#db2777'],
];
$themeFor = fn(string $slug) => $templateTheme[$slug] ?? ['ic' => 'bi-envelope', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'];
$activeCount = count(array_filter($templates, fn($t) => (int) $t['is_active'] === 1));
$inactiveCount = count($templates) - $activeCount;
?>
<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Email Templates</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Email Templates</h1>
      <p>Manage email templates used for automated notifications and events.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-envelope-paper-heart-fill"></i></span>
    <div class="dir-hero-script">Right<br>Message<br>Brighter Workplace</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-file-earmark-text"></i></div>
      <div><div class="stat-value"><?= count($templates) ?></div><div class="stat-label">Total Templates</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-green"><i class="bi bi-check-circle"></i></div>
      <div><div class="stat-value"><?= $activeCount ?></div><div class="stat-label">Active Templates</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-amber"><i class="bi bi-clock"></i></div>
      <div><div class="stat-value"><?= $inactiveCount ?></div><div class="stat-label">Inactive Templates</div></div>
    </div>
  </div>
</div>

<div class="filterbar d-flex flex-wrap gap-2 align-items-center mb-3">
  <div class="flex-grow-1 field-ic" style="min-width:220px">
    <i class="bi bi-search"></i>
    <input type="text" id="tplSearch" class="form-control" placeholder="Search templates...">
  </div>
  <div style="min-width:160px">
    <select id="tplStatusFilter" class="form-select">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
    </select>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table admin-table align-middle mb-0" id="tplTable">
      <thead><tr><th>#</th><th>Template Name</th><th>Subject</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($templates as $i => $t): $theme = $themeFor($t['slug']); ?>
          <tr data-name="<?= e(mb_strtolower($t['name'])) ?>" data-status="<?= $t['is_active'] ? 'active' : 'inactive' ?>">
            <td class="text-muted small"><?= $i + 1 ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span class="role-ic" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><i class="bi <?= $theme['ic'] ?>"></i></span>
                <span class="fw-bold small"><?= e($t['name']) ?></span>
              </div>
            </td>
            <td class="text-muted small" style="max-width:340px"><?= e($t['subject']) ?></td>
            <td><span class="status-dot-pill <?= $t['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><span class="dot"></span><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td class="text-end">
              <a href="/admin/email-templates/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#previewModal<?= $t['id'] ?>"><i class="bi bi-eye me-1"></i>Preview</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="px-3 py-2 border-top small text-muted">Showing <?= count($templates) ?> of <?= count($templates) ?> templates</div>
</div>

<?php foreach ($templates as $t): ?>
  <div class="modal fade" id="previewModal<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius:18px;border:none">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold"><?= e($t['name']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-2">Preview with sample data — the real email substitutes each recipient's actual details.</p>
          <div class="border rounded-3 p-3">
            <div class="small text-muted mb-1">Subject</div>
            <div class="fw-bold mb-3"><?= e($t['preview']['subject']) ?></div>
            <div class="small text-muted mb-1">Body</div>
            <div><?= $t['preview']['body_html'] ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>

<script>
(function () {
  var search = document.getElementById('tplSearch');
  var status = document.getElementById('tplStatusFilter');
  var rows = document.querySelectorAll('#tplTable tbody tr');
  function apply() {
    var q = (search.value || '').toLowerCase().trim();
    var st = status.value;
    rows.forEach(function (row) {
      var matchesQ = !q || row.dataset.name.indexOf(q) !== -1;
      var matchesSt = !st || row.dataset.status === st;
      row.style.display = (matchesQ && matchesSt) ? '' : 'none';
    });
  }
  search.addEventListener('input', apply);
  status.addEventListener('change', apply);
})();
</script>
