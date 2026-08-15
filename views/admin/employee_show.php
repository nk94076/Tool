<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
  <div>
    <h2 class="h5 fw-bold mb-1"><?= e($user['full_name']) ?></h2>
    <span class="badge badge-status-<?= $user['status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($user['status']) ?></span>
    <?php if ($user['is_super_admin']): ?><span class="badge bg-dark ms-1">Super Admin</span><?php endif; ?>
    <?php if ($profile['is_locked'] ?? 0): ?><span class="badge bg-secondary ms-1"><i class="bi bi-lock"></i> Locked</span><?php endif; ?>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if (!$user['is_super_admin']): ?>
      <?php if (can('employees.edit')): ?><a href="/admin/employees/<?= $user['id'] ?>/edit" class="btn btn-sm btn-primary">Edit</a><?php endif; ?>
      <?php if (can('roles.edit')): ?><a href="/admin/employees/<?= $user['id'] ?>/roles" class="btn btn-sm btn-outline-primary">Assign Roles</a><?php endif; ?>

      <?php if (($profile['is_locked'] ?? 0) && can('employees.unlock')): ?>
        <form method="post" action="/admin/employees/<?= $user['id'] ?>/unlock"><?= $csrfField ?><button class="btn btn-sm btn-warning">Unlock Profile</button></form>
      <?php elseif (!($profile['is_locked'] ?? 0) && can('employees.lock')): ?>
        <form method="post" action="/admin/employees/<?= $user['id'] ?>/lock"><?= $csrfField ?><button class="btn btn-sm btn-outline-secondary">Lock Profile</button></form>
      <?php endif; ?>

      <?php if ($user['status'] === 'active' && can('employees.deactivate')): ?>
        <form method="post" action="/admin/employees/<?= $user['id'] ?>/deactivate" onsubmit="return confirm('Deactivate this employee?');"><?= $csrfField ?><button class="btn btn-sm btn-outline-danger">Deactivate</button></form>
      <?php elseif ($user['status'] !== 'active' && can('employees.activate')): ?>
        <form method="post" action="/admin/employees/<?= $user['id'] ?>/activate"><?= $csrfField ?><button class="btn btn-sm btn-outline-success">Activate</button></form>
      <?php endif; ?>

      <?php if (can('employees.delete')): ?>
        <form method="post" action="/admin/employees/<?= $user['id'] ?>/delete" onsubmit="return confirm('This will permanently deactivate and remove this employee from active use. Continue?');"><?= $csrfField ?><button class="btn btn-sm btn-outline-danger">Delete</button></form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card text-center">
      <div class="card-body">
        <?php if (!empty($profile['profile_photo_path'])): ?>
          <img src="<?= e($profile['profile_photo_path']) ?>" class="rounded-circle mb-2" style="width:96px;height:96px;object-fit:cover">
        <?php endif; ?>
        <div class="text-muted small">Official Email</div>
        <div class="fw-semibold small mb-2"><?= e($user['official_email']) ?></div>
        <div class="text-muted small">Employee ID</div>
        <div class="fw-semibold small"><?= e($profile['employee_code'] ?? '-') ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3">Personal Information</h3>
        <dl class="row mb-0 small">
          <dt class="col-sm-4 text-muted">Date of Birth</dt><dd class="col-sm-8"><?= format_date($profile['date_of_birth'] ?? null) ?></dd>
          <dt class="col-sm-4 text-muted">Gender</dt><dd class="col-sm-8"><?= e($profile['gender'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Mobile Number</dt><dd class="col-sm-8"><?= e($profile['mobile_number'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Personal Email</dt><dd class="col-sm-8"><?= e($profile['personal_email'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Address</dt><dd class="col-sm-8"><?= e($profile['current_address'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Emergency Contact</dt><dd class="col-sm-8"><?= e($profile['emergency_contact_name'] ?? '-') ?> (<?= e($profile['emergency_contact_number'] ?? '-') ?>)</dd>
        </dl>
      </div>
    </div>
    <div class="card">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3">Employment Information</h3>
        <dl class="row mb-0 small">
          <dt class="col-sm-4 text-muted">Date of Joining</dt><dd class="col-sm-8"><?= format_date($profile['date_of_joining'] ?? null) ?></dd>
          <dt class="col-sm-4 text-muted">Department</dt><dd class="col-sm-8"><?= e($profile['department_name'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Designation</dt><dd class="col-sm-8"><?= e($profile['designation_name'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Reporting Manager</dt><dd class="col-sm-8"><?= e($profile['manager_name'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Employment Type</dt><dd class="col-sm-8"><?= e($profile['employment_type'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Work Location</dt><dd class="col-sm-8"><?= e($profile['work_location'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Employment Status</dt><dd class="col-sm-8"><?= e($profile['employment_status'] ?? '-') ?></dd>
        </dl>
      </div>
    </div>
  </div>
</div>
