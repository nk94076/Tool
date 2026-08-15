<h2 class="h5 fw-bold mb-3">Preview Your Profile</h2>

<div class="alert alert-info small">
  <i class="bi bi-info-circle me-1"></i>
  Please review your details carefully. Once submitted, your profile will be <strong>locked</strong> and only the Super Admin will be able to modify it.
</div>

<div class="card mb-3">
  <div class="card-body">
    <h3 class="h6 fw-bold mb-3">Personal Information</h3>
    <dl class="row mb-0 small">
      <dt class="col-sm-4 text-muted">Full Name</dt><dd class="col-sm-8"><?= e($profile['full_name'] ?? '') ?></dd>
      <dt class="col-sm-4 text-muted">Date of Birth</dt><dd class="col-sm-8"><?= format_date($profile['date_of_birth'] ?? null) ?></dd>
      <dt class="col-sm-4 text-muted">Gender</dt><dd class="col-sm-8"><?= e($profile['gender'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Mobile Number</dt><dd class="col-sm-8"><?= e($profile['mobile_number'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Personal Email</dt><dd class="col-sm-8"><?= e($profile['personal_email'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Address</dt><dd class="col-sm-8"><?= e($profile['current_address'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Emergency Contact</dt><dd class="col-sm-8"><?= e($profile['emergency_contact_name'] ?? '-') ?> (<?= e($profile['emergency_contact_number'] ?? '-') ?>)</dd>
    </dl>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <h3 class="h6 fw-bold mb-3">Employment Information</h3>
    <dl class="row mb-0 small">
      <dt class="col-sm-4 text-muted">Employee ID</dt><dd class="col-sm-8"><?= e($profile['employee_code'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Official Email</dt><dd class="col-sm-8"><?= e($profile['official_email'] ?? '') ?></dd>
      <dt class="col-sm-4 text-muted">Date of Joining</dt><dd class="col-sm-8"><?= format_date($profile['date_of_joining'] ?? null) ?></dd>
      <dt class="col-sm-4 text-muted">Department</dt><dd class="col-sm-8"><?= e($profile['department_name'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Designation</dt><dd class="col-sm-8"><?= e($profile['designation_name'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Reporting Manager</dt><dd class="col-sm-8"><?= e($profile['manager_name'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Employment Type</dt><dd class="col-sm-8"><?= e($profile['employment_type'] ?? '-') ?></dd>
      <dt class="col-sm-4 text-muted">Work Location</dt><dd class="col-sm-8"><?= e($profile['work_location'] ?? '-') ?></dd>
    </dl>
  </div>
</div>

<?php if (!($profile['is_locked'] ?? 0)): ?>
<div class="d-flex gap-2 justify-content-end flex-wrap">
  <a href="/profile/edit" class="btn btn-outline-secondary">Edit Details</a>
  <form method="post" action="/profile/submit" onsubmit="return confirm('Once submitted, your profile will be locked and only the Super Admin can edit it. Continue?');">
    <?= $csrfField ?>
    <button type="submit" class="btn btn-primary px-4">Submit Profile</button>
  </form>
</div>
<?php else: ?>
<div class="alert alert-secondary small mb-0">
  Your profile has been submitted and locked. Only the Super Admin can modify your information.
</div>
<?php endif; ?>
