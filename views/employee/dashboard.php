<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Profile Status</div>
      <div class="stat-value h6 mt-1">
        <?php if (($profile['is_locked'] ?? 0)): ?>
          <span class="badge badge-status-active">Submitted &amp; Locked</span>
        <?php else: ?>
          <span class="badge badge-status-pending">Incomplete</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Today's Birthdays</div>
      <div class="stat-value"><?= count($todaysBirthdays) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Tomorrow's Birthdays</div>
      <div class="stat-value"><?= count($tomorrowsBirthdays) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-label">Notifications</div>
      <div class="stat-value"><?= count($notifications) ?></div>
    </div>
  </div>
</div>

<?php if (!($profile['is_locked'] ?? 0)): ?>
<div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
  <span><i class="bi bi-exclamation-triangle me-2"></i>Your employee profile is not yet complete.</span>
  <a href="/profile/edit" class="btn btn-sm btn-warning">Complete Profile</a>
</div>
<?php endif; ?>

<div class="mb-4">
  <button class="btn btn-outline-primary btn-sm" data-enable-push><i class="bi bi-bell me-1"></i>Enable Browser Notifications</button>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-3"><i class="bi bi-cake2 me-2 text-primary"></i>Birthdays</h2>
        <?php if (empty($todaysBirthdays) && empty($tomorrowsBirthdays)): ?>
          <div class="empty-state py-3"><i class="bi bi-calendar2-x"></i><p class="small mb-0 mt-2">No upcoming birthdays</p></div>
        <?php else: ?>
          <?php foreach ($todaysBirthdays as $b): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="avatar-sm"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <div><div class="small fw-semibold"><?= e($b['full_name']) ?></div><div class="text-muted" style="font-size:.75rem">Today 🎉</div></div>
            </div>
          <?php endforeach; ?>
          <?php foreach ($tomorrowsBirthdays as $b): ?>
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="avatar-sm"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <div><div class="small fw-semibold"><?= e($b['full_name']) ?></div><div class="text-muted" style="font-size:.75rem">Tomorrow 🎂</div></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-3"><i class="bi bi-megaphone me-2 text-primary"></i>Latest Announcements</h2>
        <?php if (empty($announcements)): ?>
          <div class="empty-state py-3"><i class="bi bi-inbox"></i><p class="small mb-0 mt-2">No announcements yet</p></div>
        <?php else: ?>
          <?php foreach (array_slice($announcements, 0, 4) as $a): ?>
            <div class="mb-3">
              <div class="small fw-semibold"><?= e($a['title']) ?></div>
              <div class="text-muted small"><?= e(mb_substr(strip_tags($a['body']), 0, 100)) ?>...</div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($activeEvent && $activeEvent['status'] === 'matched' && $mySecretSanta): ?>
  <div class="col-12">
    <div class="card wishlist-box border-0">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h2 class="h6 fw-bold mb-1"><i class="bi bi-gift-fill me-2 text-primary"></i>Secret Santa</h2>
          <p class="small mb-0">Your Secret Santa recipient is <strong><?= e($mySecretSanta['recipient_name']) ?></strong>.</p>
        </div>
        <a href="/secret-santa" class="btn btn-primary btn-sm">View Details</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
