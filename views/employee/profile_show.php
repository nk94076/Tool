<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h2 class="h5 fw-bold mb-0">My Profile</h2>
  <?php if ($profile['is_locked'] ?? 0): ?>
    <?php if ($user['profile_unlocked'] ?? 0): ?>
      <a href="/profile/edit" class="btn btn-warning btn-sm"><i class="bi bi-unlock me-1"></i>Unlocked - Edit Now</a>
    <?php else: ?>
      <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Locked</span>
    <?php endif; ?>
  <?php else: ?>
    <a href="/profile/edit" class="btn btn-primary btn-sm">Complete Profile</a>
  <?php endif; ?>
</div>

<?php if (($profile['is_locked'] ?? 0) && !($user['profile_unlocked'] ?? 0)): ?>
  <div class="alert alert-secondary small">
    Your profile has been submitted and locked. Only the Super Admin can modify your information.
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="card text-center">
      <div class="card-body">
        <?php if (!empty($profile['profile_photo_path'])): ?>
          <img src="<?= e($profile['profile_photo_path']) ?>" class="rounded-circle mb-2" style="width:96px;height:96px;object-fit:cover">
        <?php else: ?>
          <div class="avatar-lg mx-auto mb-2" style="width:96px;height:96px;font-size:2rem"><?= e(mb_substr($profile['full_name'] ?? '?', 0, 1)) ?></div>
        <?php endif; ?>
        <div class="fw-bold"><?= e($profile['full_name'] ?? '') ?></div>
        <div class="text-muted small"><?= e($profile['designation_name'] ?? '-') ?></div>
        <div class="text-muted small"><?= e($profile['department_name'] ?? '-') ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3">Personal Information</h3>
        <dl class="row mb-0 small">
          <dt class="col-sm-4 text-muted">Date of Birth</dt><dd class="col-sm-8"><?= format_date($profile['date_of_birth'] ?? null) ?></dd>
          <dt class="col-sm-4 text-muted">Mobile Number</dt><dd class="col-sm-8"><?= e($profile['mobile_number'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Personal Email</dt><dd class="col-sm-8"><?= e($profile['personal_email'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Address</dt><dd class="col-sm-8"><?= e($profile['current_address'] ?? '-') ?></dd>
        </dl>
      </div>
    </div>
    <div class="card">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3">Employment Information</h3>
        <dl class="row mb-0 small">
          <dt class="col-sm-4 text-muted">Employee ID</dt><dd class="col-sm-8"><?= e($profile['employee_code'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Official Email</dt><dd class="col-sm-8"><?= e($profile['official_email'] ?? '') ?></dd>
          <dt class="col-sm-4 text-muted">Date of Joining</dt><dd class="col-sm-8"><?= format_date($profile['date_of_joining'] ?? null) ?></dd>
          <dt class="col-sm-4 text-muted">Employment Type</dt><dd class="col-sm-8"><?= e($profile['employment_type'] ?? '-') ?></dd>
          <dt class="col-sm-4 text-muted">Work Location</dt><dd class="col-sm-8"><?= e($profile['work_location'] ?? '-') ?></dd>
        </dl>
      </div>
    </div>
  </div>
</div>
