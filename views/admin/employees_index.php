<?php
$statusPillClass = fn(string $status) => match ($status) {
    'active' => 'badge-status-active',
    'inactive', 'locked' => 'badge-status-inactive',
    default => 'badge-status-pending',
};
$profilePillClass = fn(string $status) => match ($status) {
    'submitted_locked' => 'badge-status-active',
    'in_progress' => 'badge-status-pending',
    default => 'badge-status-inactive',
};
?>
<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Manage Employees</h1>
      <p>View, search and manage all employees at <?= e(setting('company_name', 'Adhook Media')) ?>.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-people-fill"></i></span>
    <div class="dir-hero-script">People<br>Power<br>Progress</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-people"></i></div>
      <div><div class="stat-value"><?= $counts['total'] ?></div><div class="stat-label">Total Employees</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-green"><i class="bi bi-check-circle"></i></div>
      <div><div class="stat-value"><?= $counts['active'] ?></div><div class="stat-label">Active</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-red"><i class="bi bi-x-circle"></i></div>
      <div><div class="stat-value"><?= $counts['inactive'] ?></div><div class="stat-label">Inactive</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-amber"><i class="bi bi-hourglass-split"></i></div>
      <div><div class="stat-value"><?= $counts['pending_profiles'] ?></div><div class="stat-label">Pending Profiles</div></div>
    </div>
  </div>
</div>

<form method="get" class="filterbar d-flex flex-wrap gap-2 align-items-center mb-3">
  <div class="flex-grow-1 field-ic" style="min-width:220px">
    <i class="bi bi-search"></i>
    <input type="text" name="q" class="form-control" placeholder="Search by name, department or designation..." value="<?= e($filters['q']) ?>">
  </div>
  <div style="min-width:170px">
    <select name="department_id" class="form-select">
      <option value="">All Departments</option>
      <?php foreach ($departments as $d): ?>
        <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div style="min-width:150px">
    <select name="status" class="form-select">
      <option value="">All Status</option>
      <?php foreach (['active','inactive','pending_verification','locked'] as $s): ?>
        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <a href="/admin/employees" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
  <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
  <button type="button" class="btn btn-primary ms-auto" id="copySignupLinkBtn" data-link="<?= e(url('/signup')) ?>">
    <i class="bi bi-link-45deg me-1"></i>Copy Signup Link
  </button>
</form>

<div class="card table-responsive-cards">
  <div class="table-responsive">
    <table class="table admin-table align-middle mb-0">
      <thead><tr><th>Employee</th><th>Department</th><th>Designation</th><th>Status</th><th>Profile</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($employees as $emp): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <?php if (!empty($emp['profile_photo_path'])): ?>
                  <img src="<?= e($emp['profile_photo_path']) ?>" class="avatar-sm" alt="" onerror="this.outerHTML='<span class=&quot;avatar-sm&quot;><?= e(mb_substr($emp['full_name'], 0, 1)) ?></span>'">
                <?php else: ?>
                  <span class="avatar-sm"><?= e(mb_substr($emp['full_name'], 0, 1)) ?></span>
                <?php endif; ?>
                <div>
                  <div class="emp-row-name"><?= e($emp['full_name']) ?></div>
                  <div class="emp-row-email"><?= e($emp['official_email']) ?></div>
                </div>
              </div>
            </td>
            <td><?php if (!empty($emp['department_name'])): ?><span class="dept-pill"><?= e($emp['department_name']) ?></span><?php else: ?>&mdash;<?php endif; ?></td>
            <td class="small"><?= e($emp['designation_name'] ?? '-') ?></td>
            <td><span class="status-dot-pill <?= $statusPillClass($emp['status']) ?>"><span class="dot"></span><?= e(ucfirst(str_replace('_', ' ', $emp['status']))) ?></span></td>
            <td><span class="status-dot-pill <?= $profilePillClass($emp['profile_status']) ?>"><?= e(str_replace('_', ' ', $emp['profile_status'])) ?></span></td>
            <td class="text-end">
              <div class="dropdown">
                <a href="/admin/employees/<?= $emp['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                <?php if (can('employees.edit') || can('roles.edit')): ?>
                  <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown" data-bs-strategy="fixed" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <?php if (can('employees.edit')): ?><li><a class="dropdown-item" href="/admin/employees/<?= $emp['id'] ?>/edit">Edit</a></li><?php endif; ?>
                    <?php if (can('roles.edit')): ?><li><a class="dropdown-item" href="/admin/employees/<?= $emp['id'] ?>/roles">Assign Roles</a></li><?php endif; ?>
                  </ul>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="row-cards p-2">
    <?php foreach ($employees as $emp): ?>
      <a href="/admin/employees/<?= $emp['id'] ?>" class="card mb-2 text-decoration-none text-body">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <?php if (!empty($emp['profile_photo_path'])): ?>
              <img src="<?= e($emp['profile_photo_path']) ?>" class="avatar-sm" alt="" onerror="this.outerHTML='<span class=&quot;avatar-sm&quot;><?= e(mb_substr($emp['full_name'], 0, 1)) ?></span>'">
            <?php else: ?>
              <span class="avatar-sm"><?= e(mb_substr($emp['full_name'], 0, 1)) ?></span>
            <?php endif; ?>
            <div>
              <div class="emp-row-name"><?= e($emp['full_name']) ?></div>
              <div class="emp-row-email"><?= e($emp['designation_name'] ?? '-') ?> &middot; <?= e($emp['department_name'] ?? '-') ?></div>
            </div>
          </div>
          <span class="status-dot-pill <?= $statusPillClass($emp['status']) ?>"><span class="dot"></span><?= e(ucfirst($emp['status'])) ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 px-3 py-2 border-top small text-muted">
    <span>Showing <?= count($employees) ?> of <?= (int) $total ?> employees</span>
  </div>
</div>

<?php $pages = (int) ceil($total / $perPage); ?>
<?php if ($pages > 1): ?>
  <nav class="mt-3"><ul class="pagination justify-content-center flex-wrap">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a></li>
    <?php endfor; ?>
  </ul></nav>
<?php endif; ?>

<script>
document.getElementById('copySignupLinkBtn')?.addEventListener('click', function () {
  const btn = this;
  navigator.clipboard.writeText(btn.dataset.link).then(function () {
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check2 me-1"></i>Copied!';
    setTimeout(() => { btn.innerHTML = original; }, 1500);
  });
});
</script>
