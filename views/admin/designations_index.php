<?php
$deptTheme = [
    'engineering' => ['bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'],
    'finance' => ['bg' => '#eff6ff', 'fg' => '#1d4ed8'],
    'human resources' => ['bg' => 'var(--red-tint)', 'fg' => 'var(--red)'],
    'marketing' => ['bg' => '#fdf2f8', 'fg' => '#db2777'],
    'operations' => ['bg' => '#ecfeff', 'fg' => '#0e7490'],
    'sales' => ['bg' => 'var(--green-tint)', 'fg' => 'var(--green)'],
];
$themeFor = fn(?string $dept) => $dept ? ($deptTheme[mb_strtolower($dept)] ?? ['bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)']) : ['bg' => 'var(--border-soft)', 'fg' => 'var(--text-muted)'];
?>
<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Designations</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Designations</h1>
      <p>Manage job titles and the departments they belong to.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-briefcase-fill"></i></span>
    <div class="dir-hero-script">Clear<br>Roles<br>Better Growth</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-briefcase"></i></div>
      <div><div class="stat-value"><?= count($designations) ?></div><div class="stat-label">Total Designations</div></div>
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
      <div class="stat-ic" style="background:#eff6ff;color:#1d4ed8"><i class="bi bi-diagram-3"></i></div>
      <div><div class="stat-value"><?= $departmentsCovered ?></div><div class="stat-label">Departments Covered</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-head-x"><i class="bi bi-briefcase"></i> Designations</div>
      <div class="filterbar d-flex flex-wrap gap-2 align-items-center px-3 pt-3">
        <div class="flex-grow-1 field-ic" style="min-width:220px">
          <i class="bi bi-search"></i>
          <input type="text" id="desigSearch" class="form-control" placeholder="Search designations...">
        </div>
        <div style="min-width:160px">
          <select id="desigStatusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="table-responsive mt-2">
        <table class="table admin-table align-middle mb-0" id="desigTable">
          <thead><tr><th>#</th><th>Designation</th><th>Department</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
            <?php foreach ($designations as $i => $d): $theme = $themeFor($d['department_name'] ?? null); ?>
              <tr data-name="<?= e(mb_strtolower($d['name'])) ?>" data-status="<?= $d['is_active'] ? 'active' : 'inactive' ?>">
                <td class="text-muted small"><?= $i + 1 ?></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="role-ic" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><i class="bi bi-person-badge"></i></span>
                    <span class="fw-bold small"><?= e($d['name']) ?></span>
                  </div>
                </td>
                <td><?php if (!empty($d['department_name'])): ?><span class="dept-pill"><?= e($d['department_name']) ?></span><?php else: ?><span class="text-muted small">&mdash;</span><?php endif; ?></td>
                <td><span class="status-dot-pill <?= $d['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><span class="dot"></span><?= $d['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDesigModal<?= $d['id'] ?>"><i class="bi bi-pencil me-1"></i>Edit</button>
                  <?php if ($d['is_active']): ?>
                    <form method="post" action="/admin/designations/<?= $d['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Deactivate this designation?');">
                      <?= $csrfField ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Deactivate</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="/admin/designations/<?= $d['id'] ?>/edit" class="d-inline">
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
      <?php foreach ($designations as $d): ?>
        <div class="modal fade" id="editDesigModal<?= $d['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:18px;border:none">
              <form method="post" action="/admin/designations/<?= $d['id'] ?>/edit">
                <?= $csrfField ?>
                <div class="modal-header border-0 pb-0">
                  <h5 class="modal-title fw-bold">Edit Designation</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <label class="form-label small fw-semibold">Designation Name</label>
                  <input type="text" name="name" value="<?= e($d['name']) ?>" class="form-control mb-3" required>
                  <label class="form-label small fw-semibold">Department</label>
                  <select name="department_id" class="form-select mb-3">
                    <option value="">No Department</option>
                    <?php foreach ($departments as $dep): ?>
                      <option value="<?= $dep['id'] ?>" <?= (int) ($d['department_id'] ?? 0) === (int) $dep['id'] ? 'selected' : '' ?>><?= e($dep['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <label class="form-label small fw-semibold">Status</label>
                  <select name="is_active" class="form-select">
                    <option value="1" <?= $d['is_active'] ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= !$d['is_active'] ? 'selected' : '' ?>>Inactive</option>
                  </select>
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
      <?php if (empty($designations)): ?>
        <div class="empty-state-lg">
          <i class="bi bi-briefcase mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
          <p class="text-muted mb-0">No designations yet. Add one to get started.</p>
        </div>
      <?php else: ?>
        <div class="px-3 py-2 border-top small text-muted">Showing <?= count($designations) ?> of <?= count($designations) ?> designations</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card mb-3">
      <div class="card-head-x"><i class="bi bi-plus-circle"></i> Add Designation</div>
      <div class="card-body-x px-3 pb-3">
        <form method="post" action="/admin/designations">
          <?= $csrfField ?>
          <label class="form-label small fw-semibold">Designation Name</label>
          <input type="text" name="name" class="form-control mb-3" placeholder="e.g. Software Engineer" required>
          <label class="form-label small fw-semibold">Department</label>
          <select name="department_id" class="form-select mb-3">
            <option value="">No Department</option>
            <?php foreach ($departments as $dep): ?><option value="<?= $dep['id'] ?>"><?= e($dep['name']) ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-primary w-100">Add Designation</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-head-x"><i class="bi bi-lightbulb"></i> Tips</div>
      <div class="card-body-x px-3 pb-3">
        <ul class="list-unstyled mb-0 small">
          <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success"></i><span>Designations feed the directory, employee profile, and reports</span></li>
          <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success"></i><span>Linking a department keeps job titles organized by team</span></li>
          <li class="d-flex gap-2 mb-2"><i class="bi bi-check-circle-fill text-success"></i><span>Deactivate instead of deleting — history is preserved</span></li>
          <li class="d-flex gap-2"><i class="bi bi-check-circle-fill text-success"></i><span>Keep titles consistent to avoid duplicates</span></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var search = document.getElementById('desigSearch');
  var status = document.getElementById('desigStatusFilter');
  var rows = document.querySelectorAll('#desigTable tbody tr');
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
