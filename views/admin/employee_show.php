<?php
$isLocked = (bool) ($profile['is_locked'] ?? 0);
$isSuperAdmin = (bool) $user['is_super_admin'];

$tenureLabel = '—';
if (!empty($profile['date_of_joining'])) {
    $years = (new DateTime($profile['date_of_joining']))->diff(new DateTime())->y;
    $tenureLabel = $years > 0 ? $years . '+' : 'New';
}
$employmentTypeLabel = $profile['employment_type'] ?? null
    ? ucwords(str_replace('_', ' ', (string) $profile['employment_type']))
    : '—';

$statusTheme = [
    'active' => ['label' => 'Active', 'bg' => 'rgba(22,163,74,.25)'],
    'inactive' => ['label' => 'Inactive', 'bg' => 'rgba(220,38,38,.25)'],
    'locked' => ['label' => 'Locked', 'bg' => 'rgba(220,38,38,.25)'],
    'pending_verification' => ['label' => 'Pending Verification', 'bg' => 'rgba(217,119,6,.25)'],
];
$accountStatus = $statusTheme[$user['status']] ?? ['label' => ucfirst($user['status']), 'bg' => 'rgba(255,255,255,.14)'];

$hasSecondaryActions = !$isSuperAdmin && (
    (($profile['is_locked'] ?? 0) && can('employees.unlock')) ||
    (!($profile['is_locked'] ?? 0) && can('employees.lock')) ||
    ($user['status'] === 'active' && can('employees.deactivate')) ||
    ($user['status'] !== 'active' && can('employees.activate')) ||
    can('employees.delete')
);
?>
<div class="admin-breadcrumb"><a href="/admin/employees">Manage Employees</a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current"><?= e($user['full_name']) ?></span></div>

<div class="d-flex justify-content-end gap-2 flex-wrap mb-3">
  <?php if (!$isSuperAdmin): ?>
    <?php if (can('employees.edit')): ?><a href="/admin/employees/<?= $user['id'] ?>/edit" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
    <?php if (can('roles.edit')): ?><a href="/admin/employees/<?= $user['id'] ?>/roles" class="btn btn-outline-primary btn-sm"><i class="bi bi-shield-check me-1"></i>Assign Roles</a><?php endif; ?>
    <?php if ($hasSecondaryActions): ?>
      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php if (($profile['is_locked'] ?? 0) && can('employees.unlock')): ?>
            <li>
              <form method="post" action="/admin/employees/<?= $user['id'] ?>/unlock"><?= $csrfField ?><button type="submit" class="dropdown-item">Unlock Profile</button></form>
            </li>
          <?php elseif (!($profile['is_locked'] ?? 0) && can('employees.lock')): ?>
            <li>
              <form method="post" action="/admin/employees/<?= $user['id'] ?>/lock"><?= $csrfField ?><button type="submit" class="dropdown-item">Lock Profile</button></form>
            </li>
          <?php endif; ?>
          <?php if ($user['status'] === 'active' && can('employees.deactivate')): ?>
            <li>
              <form method="post" action="/admin/employees/<?= $user['id'] ?>/deactivate" onsubmit="return confirm('Deactivate this employee?');"><?= $csrfField ?><button type="submit" class="dropdown-item text-danger">Deactivate</button></form>
            </li>
          <?php elseif ($user['status'] !== 'active' && can('employees.activate')): ?>
            <li>
              <form method="post" action="/admin/employees/<?= $user['id'] ?>/activate"><?= $csrfField ?><button type="submit" class="dropdown-item text-success">Activate</button></form>
            </li>
          <?php endif; ?>
          <?php if (can('employees.delete')): ?>
            <li>
              <form method="post" action="/admin/employees/<?= $user['id'] ?>/delete" onsubmit="return confirm('This will permanently deactivate and remove this employee from active use. Continue?');"><?= $csrfField ?><button type="submit" class="dropdown-item text-danger">Delete</button></form>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<div class="profile-hero mb-3">
  <div class="profile-hero-photo-wrap">
    <?php if (!empty($profile['profile_photo_path'])): ?>
      <img src="<?= e($profile['profile_photo_path']) ?>" class="profile-hero-photo" alt="" onerror="this.outerHTML='<div class=&quot;profile-hero-photo&quot;><?= e(mb_substr($user['full_name'], 0, 1)) ?></div>'">
    <?php else: ?>
      <div class="profile-hero-photo"><?= e(mb_substr($user['full_name'], 0, 1)) ?></div>
    <?php endif; ?>
    <?php if ($user['status'] === 'active'): ?><span class="profile-status-dot"></span><?php endif; ?>
  </div>
  <div style="z-index:1">
    <p class="profile-hero-name"><?= e($user['full_name']) ?></p>
    <p class="profile-hero-role"><?= e($profile['designation_name'] ?? '-') ?></p>
    <div class="d-flex flex-wrap gap-2 mt-2">
      <?php if (!empty($profile['department_name'])): ?><span class="profile-dept-pill"><i class="bi bi-building"></i><?= e($profile['department_name']) ?></span><?php endif; ?>
      <span class="profile-dept-pill" style="background:<?= $accountStatus['bg'] ?>"><?= e($accountStatus['label']) ?></span>
      <?php if ($isSuperAdmin): ?><span class="profile-dept-pill"><i class="bi bi-shield-lock-fill"></i>Super Admin</span><?php endif; ?>
      <?php if ($isLocked): ?><span class="profile-dept-pill"><i class="bi bi-lock-fill"></i>Profile Locked</span><?php endif; ?>
    </div>
  </div>
  <div class="profile-hero-stats">
    <div class="profile-hero-stat">
      <span class="profile-hero-stat-ic"><i class="bi bi-calendar3"></i></span>
      <div><div class="profile-hero-stat-val"><?= e($tenureLabel) ?></div><div class="profile-hero-stat-lbl">Year<?= $tenureLabel === '1+' ? '' : 's' ?> at Adhook</div></div>
    </div>
    <div class="profile-hero-stat">
      <span class="profile-hero-stat-ic"><i class="bi bi-briefcase"></i></span>
      <div><div class="profile-hero-stat-val"><?= e($employmentTypeLabel) ?></div><div class="profile-hero-stat-lbl">Employment Type</div></div>
    </div>
    <div class="profile-hero-stat">
      <span class="profile-hero-stat-ic"><i class="bi bi-person-vcard"></i></span>
      <div><div class="profile-hero-stat-val"><?= e($profile['employee_code'] ?? '-') ?></div><div class="profile-hero-stat-lbl">Employee ID</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x"><i class="bi bi-person text-primary"></i> Personal Information</div>
      <div class="card-body-x">
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-calendar3"></i></span>
          <div><div class="profile-field-label">Date of Birth</div><div class="profile-field-value"><?= format_date($profile['date_of_birth'] ?? null) ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-person-badge"></i></span>
          <div><div class="profile-field-label">Gender</div><div class="profile-field-value"><?= e($profile['gender'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-telephone"></i></span>
          <div><div class="profile-field-label">Mobile Number</div><div class="profile-field-value"><?= e($profile['mobile_number'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-envelope"></i></span>
          <div><div class="profile-field-label">Personal Email</div><div class="profile-field-value"><?= e($profile['personal_email'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-geo-alt"></i></span>
          <div><div class="profile-field-label">Address</div><div class="profile-field-value"><?= e($profile['current_address'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-person-lines-fill"></i></span>
          <div><div class="profile-field-label">Emergency Contact</div><div class="profile-field-value"><?= e($profile['emergency_contact_name'] ?? '-') ?> (<?= e($profile['emergency_contact_number'] ?? '-') ?>)</div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x"><i class="bi bi-briefcase text-primary"></i> Employment Information</div>
      <div class="card-body-x">
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-envelope-at"></i></span>
          <div><div class="profile-field-label">Official Email</div><div class="profile-field-value"><?= e($user['official_email']) ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-calendar-check"></i></span>
          <div><div class="profile-field-label">Date of Joining</div><div class="profile-field-value"><?= format_date($profile['date_of_joining'] ?? null) ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-building"></i></span>
          <div><div class="profile-field-label">Department</div><div class="profile-field-value"><?= e($profile['department_name'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-briefcase"></i></span>
          <div><div class="profile-field-label">Designation</div><div class="profile-field-value"><?= e($profile['designation_name'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-person-workspace"></i></span>
          <div><div class="profile-field-label">Reporting Manager</div><div class="profile-field-value"><?= e($profile['manager_name'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-geo-alt"></i></span>
          <div><div class="profile-field-label">Work Location</div><div class="profile-field-value"><?= e($profile['work_location'] ?? '-') ?></div></div>
        </div>
      </div>
    </div>
  </div>
</div>
