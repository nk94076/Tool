<?php $pages = (int) ceil($total / $perPage); ?>
<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Audit Logs</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Audit Logs</h1>
      <p>Track every change made across the system.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-clock-history"></i></span>
    <div class="dir-hero-script">Track<br>Every<br>Change</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-journal-text"></i></div>
      <div><div class="stat-value"><?= number_format($total) ?></div><div class="stat-label">Total Entries</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-green"><i class="bi bi-calendar-day"></i></div>
      <div><div class="stat-value"><?= number_format($todayCount) ?></div><div class="stat-label">Today</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-amber"><i class="bi bi-list-check"></i></div>
      <div><div class="stat-value"><?= count($actions) ?></div><div class="stat-label">Distinct Actions</div></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head-x"><i class="bi bi-journal-text"></i> Activity</div>
  <form method="get" class="filterbar d-flex flex-wrap gap-2 align-items-center px-3 pt-3">
    <div class="flex-grow-1 field-ic" style="min-width:220px">
      <i class="bi bi-search"></i>
      <input type="text" id="auditSearch" class="form-control" placeholder="Search this page (actor, subject, action)...">
    </div>
    <div style="min-width:200px">
      <select name="action" class="form-select" onchange="this.form.submit()">
        <option value="">All Actions</option>
        <?php foreach ($actions as $act): ?>
          <option value="<?= e($act) ?>" <?= ($filters['action'] ?? '') === $act ? 'selected' : '' ?>><?= e($act) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (!empty($filters['action'])): ?>
      <a href="/admin/audit-logs" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
    <?php endif; ?>
  </form>

  <div class="table-responsive mt-2">
    <table class="table admin-table align-middle mb-0 small" id="auditTable">
      <thead><tr><th>When</th><th>Actor</th><th>Subject</th><th>Action</th><th>Field</th><th>Old</th><th>New</th><th>IP</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr data-search="<?= e(mb_strtolower(($log['actor_name'] ?? 'system') . ' ' . ($log['subject_name'] ?? '') . ' ' . $log['action'])) ?>">
            <td class="text-nowrap text-muted"><?= format_date($log['created_at'], 'd M Y, h:i A') ?></td>
            <td class="fw-semibold"><?= e($log['actor_name'] ?? 'System') ?></td>
            <td><?= e($log['subject_name'] ?? '-') ?></td>
            <td><span class="count-pill"><?= e($log['action']) ?></span></td>
            <td class="text-muted"><?= e($log['field_name'] ?? '-') ?></td>
            <td class="text-truncate" style="max-width:120px" title="<?= e($log['old_value'] ?? '') ?>"><?= e($log['old_value'] ?? '-') ?></td>
            <td class="text-truncate" style="max-width:120px" title="<?= e($log['new_value'] ?? '') ?>"><?= e($log['new_value'] ?? '-') ?></td>
            <td class="text-muted"><?= e($log['ip_address'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (empty($logs)): ?>
    <div class="empty-state-lg">
      <i class="bi bi-journal-text mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
      <p class="text-muted mb-0">No activity recorded yet.</p>
    </div>
  <?php else: ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-top small text-muted">
      <span>Showing <?= count($logs) ?> of <?= number_format($total) ?> entries</span>
    </div>
  <?php endif; ?>
</div>

<?php if ($pages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination justify-content-center flex-wrap">
      <?php if ($page > 1): ?>
        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">Previous</a></li>
      <?php endif; ?>
      <li class="page-item disabled"><span class="page-link">Page <?= $page ?> of <?= $pages ?></span></li>
      <?php if ($page < $pages): ?>
        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">Next</a></li>
      <?php endif; ?>
    </ul>
  </nav>
<?php endif; ?>

<script>
(function () {
  var search = document.getElementById('auditSearch');
  var rows = document.querySelectorAll('#auditTable tbody tr');
  search.addEventListener('input', function () {
    var q = (search.value || '').toLowerCase().trim();
    rows.forEach(function (row) {
      row.style.display = (!q || row.dataset.search.indexOf(q) !== -1) ? '' : 'none';
    });
  });
})();
</script>
