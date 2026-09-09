<div class="admin-breadcrumb"><a href="/profile/edit">Edit Profile</a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Preview</span></div>

<div class="page-hero-title mb-3">
  <span class="bar"></span>
  <div>
    <h1 class="h4 fw-bold mb-1">Preview Your Profile</h1>
    <p class="text-muted small mb-0">Review your details before submitting.</p>
  </div>
</div>

<div class="alert alert-info small">
  <i class="bi bi-info-circle me-1"></i>
  Please review your details carefully. Once submitted, your profile will be <strong>locked</strong> and only the Super Admin will be able to modify it.
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x"><i class="bi bi-person text-primary"></i> Personal Information</div>
      <div class="card-body-x p-3">
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-person-badge"></i></span>
          <div><div class="profile-field-label">Full Name</div><div class="profile-field-value"><?= e($profile['full_name'] ?? '') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-calendar3"></i></span>
          <div><div class="profile-field-label">Date of Birth</div><div class="profile-field-value"><?= format_date($profile['date_of_birth'] ?? null) ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-person-vcard"></i></span>
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
      <div class="card-body-x p-3">
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-person-vcard"></i></span>
          <div><div class="profile-field-label">Employee ID</div><div class="profile-field-value"><?= e($profile['employee_code'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-envelope-at"></i></span>
          <div><div class="profile-field-label">Official Email</div><div class="profile-field-value"><?= e($profile['official_email'] ?? '') ?></div></div>
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
          <span class="profile-field-ic"><i class="bi bi-briefcase"></i></span>
          <div><div class="profile-field-label">Employment Type</div><div class="profile-field-value"><?= e($profile['employment_type'] ?? '-') ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-geo-alt"></i></span>
          <div><div class="profile-field-label">Work Location</div><div class="profile-field-value"><?= e($profile['work_location'] ?? '-') ?></div></div>
        </div>
      </div>
    </div>
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
