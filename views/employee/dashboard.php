<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
  <div>
    <h2 class="h5 fw-bold mb-1"><?= 'Welcome back, ' . e(explode(' ', $currentUser['full_name'] ?? '')[0] ?? '') ?></h2>
    <p class="text-muted small mb-0"><?= e(date('l, j F Y')) ?></p>
  </div>
  <button class="badge-soft border-0" data-enable-push><i class="bi bi-bell-fill"></i>Enable Browser Notifications</button>
</div>

<?php if (!($profile['is_locked'] ?? 0)): ?>
<div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
  <span><i class="bi bi-exclamation-triangle me-2"></i>Your employee profile is not yet complete.</span>
  <a href="/profile/edit" class="btn btn-sm btn-warning">Complete Profile</a>
</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-green"><i class="bi bi-shield-check"></i></div>
      <div class="stat-value" style="font-size:1.05rem">
        <?= ($profile['is_locked'] ?? 0) ? 'Locked' : 'Incomplete' ?>
      </div>
      <div class="stat-label">Profile status</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-amber"><i class="bi bi-cake2"></i></div>
      <div class="stat-value"><?= count($todaysBirthdays) ?></div>
      <div class="stat-label">Today's birthdays</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-brand"><i class="bi bi-cake"></i></div>
      <div class="stat-value"><?= count($tomorrowsBirthdays) ?></div>
      <div class="stat-label">Tomorrow's birthdays</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-red"><i class="bi bi-bell"></i></div>
      <div class="stat-value"><?= count($notifications) ?></div>
      <div class="stat-label">Notifications</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x"><i class="bi bi-cake2 text-primary"></i>Birthdays</div>
      <div class="card-body-x">
        <?php if (empty($todaysBirthdays) && empty($tomorrowsBirthdays)): ?>
          <div class="empty-state py-3"><i class="bi bi-calendar2-x"></i><p class="small mb-0 mt-2">No upcoming birthdays</p></div>
        <?php else: ?>
          <?php foreach ($todaysBirthdays as $b): ?>
            <div class="row-item">
              <?php if (!empty($b['profile_photo_path'])): ?>
                <img src="<?= e($b['profile_photo_path']) ?>" class="avatar-sm" style="width:38px;height:38px" alt="" onerror="this.outerHTML='<span class=&quot;avatar-sm&quot; style=&quot;width:38px;height:38px&quot;><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>'">
              <?php else: ?>
                <span class="avatar-sm" style="width:38px;height:38px"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <?php endif; ?>
              <div><div class="row-name"><?= e($b['full_name']) ?></div><div class="row-sub">Today 🎉</div></div>
              <span class="chip chip-today">Today</span>
            </div>
          <?php endforeach; ?>
          <?php foreach ($tomorrowsBirthdays as $b): ?>
            <div class="row-item">
              <?php if (!empty($b['profile_photo_path'])): ?>
                <img src="<?= e($b['profile_photo_path']) ?>" class="avatar-sm" style="width:38px;height:38px" alt="" onerror="this.outerHTML='<span class=&quot;avatar-sm&quot; style=&quot;width:38px;height:38px&quot;><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>'">
              <?php else: ?>
                <span class="avatar-sm" style="width:38px;height:38px"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <?php endif; ?>
              <div><div class="row-name"><?= e($b['full_name']) ?></div><div class="row-sub">Tomorrow 🎂</div></div>
              <span class="chip chip-tomorrow">Tomorrow</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x"><i class="bi bi-megaphone text-primary"></i>Latest Announcements</div>
      <div class="card-body-x">
        <?php if (empty($announcements)): ?>
          <div class="empty-state py-3"><i class="bi bi-inbox"></i><p class="small mb-0 mt-2">No announcements yet</p></div>
        <?php else: ?>
          <?php foreach (array_slice($announcements, 0, 4) as $a): ?>
            <div class="row-item" style="align-items:flex-start">
              <div>
                <div class="row-name"><?= e($a['title']) ?></div>
                <div class="row-sub mt-1"><?= e(mb_substr(strip_tags($a['body']), 0, 100)) ?>...</div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if ($activeEvent && $activeEvent['status'] === 'matched' && $mySecretSanta): ?>
<div class="santa-banner">
  <div class="d-flex align-items-center gap-3">
    <div class="santa-ic"><i class="bi bi-gift-fill"></i></div>
    <div class="santa-text">
      <h3>Secret Santa 2026 is live 🎁</h3>
      <p>Your recipient is <strong><?= e($mySecretSanta['recipient_name']) ?></strong> — view their wishlist and send an anonymous note.</p>
    </div>
  </div>
  <a href="/secret-santa" class="btn-white">View details</a>
</div>
<?php endif; ?>
