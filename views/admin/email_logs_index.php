<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Email Logs</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Email Logs</h1>
      <p>Delivery history for every automated email sent by the portal.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-envelope-check-fill"></i></span>
    <div class="dir-hero-script">Sent<br>Tracked<br>Delivered</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-envelope"></i></div>
      <div><div class="stat-value"><?= count($logs) ?></div><div class="stat-label">Total (last 200)</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-green"><i class="bi bi-check-circle"></i></div>
      <div><div class="stat-value"><?= $sentCount ?></div><div class="stat-label">Sent</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-red"><i class="bi bi-x-circle"></i></div>
      <div><div class="stat-value"><?= $failedCount ?></div><div class="stat-label">Failed</div></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head-x"><i class="bi bi-envelope"></i> Delivery History</div>
  <div class="filterbar d-flex flex-wrap gap-2 align-items-center px-3 pt-3">
    <div class="flex-grow-1 field-ic" style="min-width:220px">
      <i class="bi bi-search"></i>
      <input type="text" id="emailLogSearch" class="form-control" placeholder="Search by recipient or subject...">
    </div>
    <div style="min-width:160px">
      <select id="emailLogStatusFilter" class="form-select">
        <option value="">All Status</option>
        <option value="sent">Sent</option>
        <option value="failed">Failed</option>
      </select>
    </div>
  </div>

  <div class="table-responsive mt-2">
    <table class="table admin-table align-middle mb-0 small" id="emailLogTable">
      <thead><tr><th>Recipient</th><th>Subject</th><th>Template</th><th>Status</th><th>Error</th><th>Sent</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr data-search="<?= e(mb_strtolower($log['recipient_email'] . ' ' . $log['subject'])) ?>" data-status="<?= e($log['status']) ?>">
            <td class="fw-semibold"><?= e($log['recipient_email']) ?></td>
            <td><?= e($log['subject']) ?></td>
            <td><?php if (!empty($log['template_slug'])): ?><span class="dept-pill"><?= e($log['template_slug']) ?></span><?php else: ?><span class="text-muted">&mdash;</span><?php endif; ?></td>
            <td><span class="status-dot-pill <?= $log['status'] === 'sent' ? 'badge-status-active' : 'badge-status-inactive' ?>"><span class="dot"></span><?= e(ucfirst($log['status'])) ?></span></td>
            <td class="text-truncate text-muted" style="max-width:200px" title="<?= e($log['error_message'] ?? '') ?>"><?= e($log['error_message'] ?? '-') ?></td>
            <td class="text-nowrap text-muted"><?= $log['sent_at'] ? format_date($log['sent_at'], 'd M, h:i A') : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (empty($logs)): ?>
    <div class="empty-state-lg">
      <i class="bi bi-envelope mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
      <p class="text-muted mb-0">No emails have been sent yet.</p>
    </div>
  <?php else: ?>
    <div class="px-3 py-2 border-top small text-muted">Showing <?= count($logs) ?> of the most recent 200 emails</div>
  <?php endif; ?>
</div>

<script>
(function () {
  var search = document.getElementById('emailLogSearch');
  var status = document.getElementById('emailLogStatusFilter');
  var rows = document.querySelectorAll('#emailLogTable tbody tr');
  function apply() {
    var q = (search.value || '').toLowerCase().trim();
    var st = status.value;
    rows.forEach(function (row) {
      var matchesQ = !q || row.dataset.search.indexOf(q) !== -1;
      var matchesSt = !st || row.dataset.status === st;
      row.style.display = (matchesQ && matchesSt) ? '' : 'none';
    });
  }
  search.addEventListener('input', apply);
  status.addEventListener('change', apply);
})();
</script>
