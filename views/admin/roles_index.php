<?php
$roleTheme = [
    'super_admin' => ['ic' => 'bi-shield-lock-fill', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'],
    'admin' => ['ic' => 'bi-award-fill', 'bg' => 'var(--amber-tint)', 'fg' => 'var(--amber)'],
    'manager' => ['ic' => 'bi-people-fill', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
    'employee' => ['ic' => 'bi-person-fill', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
];
$themeFor = fn(string $slug) => $roleTheme[$slug] ?? ['ic' => 'bi-person-badge', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'];

$activeCount = count(array_filter($roles, fn($r) => (int) $r['is_active'] === 1));
$inactiveCount = count($roles) - $activeCount;
?>
<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Roles &amp; Permissions</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Roles &amp; Permissions</h1>
      <p>Create and manage roles with fine-grained permissions.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-shield-shaded"></i></span>
    <div class="dir-hero-script">Secure<br>People<br>Enable Growth</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-people"></i></div>
      <div><div class="stat-value"><?= count($roles) ?></div><div class="stat-label">Total Roles</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-green"><i class="bi bi-check-circle"></i></div>
      <div><div class="stat-value"><?= $activeCount ?></div><div class="stat-label">Active Roles</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic bg-amber"><i class="bi bi-clock-history"></i></div>
      <div><div class="stat-value"><?= $inactiveCount ?></div><div class="stat-label">Inactive Roles</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card-h">
      <div class="stat-ic" style="background:#eff6ff;color:#1d4ed8"><i class="bi bi-key"></i></div>
      <div><div class="stat-value"><?= (int) $totalPermissions ?></div><div class="stat-label">Total Permissions</div></div>
    </div>
  </div>
</div>

<div class="filterbar d-flex flex-wrap gap-2 align-items-center mb-3">
  <div class="flex-grow-1 field-ic" style="min-width:220px">
    <i class="bi bi-search"></i>
    <input type="text" id="roleSearch" class="form-control" placeholder="Search roles...">
  </div>
  <div style="min-width:160px">
    <select id="roleStatusFilter" class="form-select">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
    </select>
  </div>
  <?php if (can('roles.create')): ?>
    <a href="/admin/roles/create" class="btn btn-primary ms-auto"><i class="bi bi-plus-lg me-1"></i>New Role</a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table admin-table align-middle mb-0" id="rolesTable">
      <thead><tr><th>#</th><th>Role</th><th>Description</th><th>Permissions</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($roles as $i => $role): $theme = $themeFor($role['slug']); ?>
          <tr data-name="<?= e(mb_strtolower($role['name'])) ?>" data-status="<?= $role['is_active'] ? 'active' : 'inactive' ?>">
            <td class="text-muted small"><?= $i + 1 ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span class="role-ic" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><i class="bi <?= $theme['ic'] ?>"></i></span>
                <span class="fw-bold small"><?= e($role['name']) ?></span>
                <?php if ($role['is_system']): ?><span class="system-tag">System</span><?php endif; ?>
              </div>
            </td>
            <td class="text-muted small" style="max-width:280px"><?= e($role['description'] ?? '') ?></td>
            <td><span class="count-pill"><?= $role['permission_count'] ?></span></td>
            <td><span class="status-dot-pill <?= $role['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><span class="dot"></span><?= $role['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td class="text-end">
              <?php if (!$role['is_system']): ?>
                <?php if (can('roles.edit')): ?><a href="/admin/roles/<?= $role['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
                <?php if (can('roles.delete')): ?>
                  <form method="post" action="/admin/roles/<?= $role['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this role?');">
                    <?= $csrfField ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="px-3 py-2 border-top small text-muted">Showing <?= count($roles) ?> of <?= count($roles) ?> roles</div>
</div>

<script>
(function () {
  var search = document.getElementById('roleSearch');
  var status = document.getElementById('roleStatusFilter');
  var rows = document.querySelectorAll('#rolesTable tbody tr');
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
