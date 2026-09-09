<?php
$isLocked = (bool) ($profile['is_locked'] ?? 0);
$isUnlocked = (bool) ($user['profile_unlocked'] ?? 0);

$tenureLabel = '—';
if (!empty($profile['date_of_joining'])) {
    $years = (new DateTime($profile['date_of_joining']))->diff(new DateTime())->y;
    $tenureLabel = $years > 0 ? $years . '+' : 'New';
}
$employmentTypeLabel = $profile['employment_type'] ?? null
    ? ucwords(str_replace('_', ' ', (string) $profile['employment_type']))
    : '—';
?>
<div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-3">
  <div>
    <h2 class="h5 fw-bold mb-1">My Profile</h2>
    <p class="text-muted small mb-0">View your personal and employment details</p>
  </div>
  <?php if ($isLocked && $isUnlocked): ?>
    <a href="/profile/edit" class="btn btn-warning btn-sm"><i class="bi bi-unlock me-1"></i>Unlocked - Edit Now</a>
  <?php elseif ($isLocked): ?>
    <div class="profile-locked-pill">
      <span class="profile-locked-pill-ic"><i class="bi bi-lock-fill"></i></span>
      <div>
        <div class="fw-bold small">Profile Locked</div>
        <div class="text-muted" style="font-size:.72rem">Only Super Admin can modify your information</div>
      </div>
    </div>
  <?php else: ?>
    <a href="/profile/edit" class="btn btn-primary btn-sm">Complete Profile</a>
  <?php endif; ?>
</div>

<div class="profile-hero mb-3">
  <div class="profile-hero-photo-wrap">
    <?php if (!empty($profile['profile_photo_path'])): ?>
      <img src="<?= e($profile['profile_photo_path']) ?>" class="profile-hero-photo" alt="" onerror="this.outerHTML='<div class=&quot;profile-hero-photo&quot;><?= e(mb_substr($profile['full_name'] ?? '?', 0, 1)) ?></div>'">
    <?php else: ?>
      <div class="profile-hero-photo"><?= e(mb_substr($profile['full_name'] ?? '?', 0, 1)) ?></div>
    <?php endif; ?>
    <?php if (($profile['account_status'] ?? '') === 'active'): ?>
      <span class="profile-status-dot"></span>
    <?php endif; ?>
  </div>
  <div style="z-index:1">
    <p class="profile-hero-name"><?= e($profile['full_name'] ?? '') ?></p>
    <p class="profile-hero-role"><?= e($profile['designation_name'] ?? '-') ?></p>
    <?php if (!empty($profile['department_name'])): ?>
      <span class="profile-dept-pill"><i class="bi bi-building"></i><?= e($profile['department_name']) ?></span>
    <?php endif; ?>
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
      <span class="profile-hero-stat-ic"><i class="bi bi-geo-alt"></i></span>
      <div><div class="profile-hero-stat-val"><?= e($profile['work_location'] ?? '-') ?></div><div class="profile-hero-stat-lbl">Work Location</div></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x d-flex justify-content-between align-items-center">
        <span><i class="bi bi-person text-primary"></i> Personal Information</span>
        <span class="view-only-pill"><i class="bi bi-eye"></i>View Only</span>
      </div>
      <div class="card-body-x">
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-calendar3"></i></span>
          <div><div class="profile-field-label">Date of Birth</div><div class="profile-field-value"><?= format_date($profile['date_of_birth'] ?? null) ?></div></div>
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
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x d-flex justify-content-between align-items-center">
        <span><i class="bi bi-briefcase text-primary"></i> Employment Information</span>
        <span class="view-only-pill"><i class="bi bi-eye"></i>View Only</span>
      </div>
      <div class="card-body-x">
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
          <span class="profile-field-ic"><i class="bi bi-briefcase"></i></span>
          <div><div class="profile-field-label">Employment Type</div><div class="profile-field-value"><?= e($employmentTypeLabel) ?></div></div>
        </div>
        <div class="profile-field">
          <span class="profile-field-ic"><i class="bi bi-geo-alt"></i></span>
          <div><div class="profile-field-label">Work Location</div><div class="profile-field-value"><?= e($profile['work_location'] ?? '-') ?></div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($isLocked && !$isUnlocked): ?>
  <div class="profile-locked-banner">
    <span class="profile-locked-banner-ic"><i class="bi bi-shield-lock"></i></span>
    <div>
      <div class="fw-bold small mb-1">Profile is Locked</div>
      <p class="text-muted small mb-0">Your profile has been submitted and locked. Only the Super Admin can modify your information.</p>
    </div>
  </div>
<?php endif; ?>
