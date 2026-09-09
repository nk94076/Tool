<?php
$deptTheme = [
    'engineering' => ['ic' => 'bi-gear-fill', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'],
    'finance' => ['ic' => 'bi-bar-chart-fill', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
    'human resources' => ['ic' => 'bi-people-fill', 'bg' => 'var(--red-tint)', 'fg' => 'var(--red)'],
    'marketing' => ['ic' => 'bi-megaphone-fill', 'bg' => '#fdf2f8', 'fg' => '#db2777'],
    'operations' => ['ic' => 'bi-diagram-3-fill', 'bg' => '#ecfeff', 'fg' => '#0e7490'],
    'sales' => ['ic' => 'bi-cash-coin', 'bg' => 'var(--green-tint)', 'fg' => 'var(--green)'],
];
$themeFor = fn(string $name) => $deptTheme[mb_strtolower($name)] ?? ['ic' => 'bi-building', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'];
?>
<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Departments</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Departments</h1>
      <p>Organize your teams and manage department details.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-buildings"></i></span>
    <div class="dir-hero-script">Stronger<br>Teams<br>Brighter Tomorrow</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-diagram-3"></i></div>
      <div><div class="stat-value"><?= count($departments) ?></div><div class="stat-label">Total Departments</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-green"><i class="bi bi-check-circle"></i></div>
      <div><div class="stat-value"><?= $activeCount ?></div><div class="stat-label">Active</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-red"><i class="bi bi-x-circle"></i></div>
      <div><div class="stat-value"><?= $inactiveCount ?></div><div class="stat-label">Inactive</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic" style="background:#eff6ff;color:#1d4ed8"><i class="bi bi-award"></i></div>
      <div>
        <div class="stat-value"><?= $largestDepartment ? (int) $largestDepartment['employee_count'] : 0 ?></div>
        <div class="stat-label">Largest Team<?= $largestDepartment ? ' (' . e($largestDepartment['name']) . ')' : '' ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-head-x"><i class="bi bi-diagram-3"></i> Departments</div>
      <div class="filterbar d-flex flex-wrap gap-2 align-items-center px-3 pt-3">
        <div class="flex-grow-1 field-ic" style="min-width:220px">
          <i class="bi bi-search"></i>
          <input type="text" id="deptSearch" class="form-control" placeholder="Search departments...">
        </div>
        <div style="min-width:160px">
          <select id="deptStatusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="table-responsive mt-2">
        <table class="table admin-table align-middle mb-0" id="deptTable">
          <thead><tr><th>#</th><th>Department</th><th>Status</th><th>Employees</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($departments as $i => $d): $theme = $themeFor($d['name']); ?>
              <tr data-name="<?= e(mb_strtolower($d['name'])) ?>" data-status="<?= $d['is_active'] ? 'active' : 'inactive' ?>">
                <td class="text-muted small"><?= $i + 1 ?></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="role-ic" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><i class="bi <?= $theme['ic'] ?>"></i></span>
                    <span class="fw-bold small"><?= e($d['name']) ?></span>
                  </div>
                </td>
                <td><span class="status-dot-pill <?= $d['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><span class="dot"></span><?= $d['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td><i class="bi bi-people text-muted me-1"></i><?= (int) $d['employee_count'] ?></td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDeptModal<?= $d['id'] ?>"><i class="bi bi-pencil me-1"></i>Edit</button>
                  <?php if ($d['is_active']): ?>
                    <form method="post" action="/admin/departments/<?= $d['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Deactivate this department?');">
                      <?= $csrfField ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Deactivate</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="/admin/departments/<?= $d['id'] ?>/edit" class="d-inline">
                      <?= $csrfField ?><input type="hidden" name="is_active" value="1">
                      <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-circle me-1"></i>Activate</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php foreach ($departments as $d): ?>
        <div class="modal fade" id="editDeptModal<?= $d['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:18px;border:none">
              <form method="post" action="/admin/departments/<?= $d['id'] ?>/edit">
                <?= $csrfField ?>
                <div class="modal-header border-0 pb-0">
                  <h5 class="modal-title fw-bold">Edit Department</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <label class="form-label small fw-semibold">Department Name</label>
                  <input type="text" name="name" value="<?= e($d['name']) ?>" class="form-control mb-3" required>
                  <label class="form-label small fw-semibold">Status</label>
                  <select name="is_active" class="form-select mb-3">
                    <option value="1" <?= $d['is_active'] ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= !$d['is_active'] ? 'selected' : '' ?>>Inactive</option>
                  </select>
                  <label class="form-label small fw-semibold">Description (Optional)</label>
                  <textarea name="description" class="form-control" rows="3" placeholder="Enter department description..."><?= e($d['description'] ?? '') ?></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                  <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                  <button class="btn btn-primary">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($departments)): ?>
        <div class="empty-state-lg">
          <i class="bi bi-diagram-3 mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
          <p class="text-muted mb-0">No departments yet. Add one to get started.</p>
        </div>
      <?php else: ?>
        <div class="px-3 py-2 border-top small text-muted">Showing <?= count($departments) ?> of <?= count($departments) ?> departments</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-head-x"><i class="bi bi-plus-circle"></i> Add Department</div>
      <div class="card-body-x px-3 pb-3">
        <form method="post" action="/admin/departments">
          <?= $csrfField ?>
          <label class="form-label small fw-semibold">Department Name</label>
          <input type="text" name="name" class="form-control mb-3" placeholder="e.g. Engineering" required>
          <label class="form-label small fw-semibold">Status</label>
          <select name="is_active" class="form-select mb-3">
            <option value="1" selected>Active</option>
            <option value="0">Inactive</option>
          </select>
          <label class="form-label small fw-semibold">Description (Optional)</label>
          <textarea name="description" class="form-control mb-3" rows="3" placeholder="Enter department description..."></textarea>
          <button class="btn btn-primary w-100">Add Department</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-head-x"><i class="bi bi-lightbulb"></i> Tips</div>
      <div class="card-body-x px-3 pb-3">
        <ul class="list-unstyled mb-0 small">
          <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success"></i><span>Employees are grouped by department across the directory</span></li>
          <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success"></i><span>Deactivate a department instead of deleting it — history is preserved</span></li>
          <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success"></i><span>Department names must be unique</span></li>
          <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Keep your organization structured as you grow</span></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var search = document.getElementById('deptSearch');
  var status = document.getElementById('deptStatusFilter');
  var rows = document.querySelectorAll('#deptTable tbody tr');
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
