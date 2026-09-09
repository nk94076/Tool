<div class="d-flex justify-content-end mb-2">
  <button class="badge-soft border-0" data-enable-push><i class="bi bi-bell-fill"></i>Enable Browser Notifications</button>
</div>

<div class="dash-hero">
  <div>
    <h1>Welcome back, <?= e(explode(' ', $currentUser['full_name'] ?? '')[0] ?? '') ?>! 👋</h1>
    <p><?= e(date('l, j F Y')) ?></p>
  </div>
  <div class="dash-hero-deco">
    <figure class="dash-hero-quote mb-0">
      &ldquo;Great teams build great things.&rdquo;
      <footer>&mdash; <?= e(setting('company_name', 'Adhook Media')) ?></footer>
    </figure>
    <span class="dash-hero-ic"><i class="bi bi-rocket-takeoff-fill"></i></span>
  </div>
</div>

<?php if (!($profile['is_locked'] ?? 0)): ?>
<div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
  <span><i class="bi bi-exclamation-triangle me-2"></i>Your employee profile is not yet complete.</span>
  <a href="/profile/edit" class="btn btn-sm btn-warning">Complete Profile</a>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-shield-check stat-watermark"></i>
      <div class="stat-ic bg-green"><i class="bi bi-shield-check"></i></div>
      <div class="stat-value" style="font-size:1.05rem"><?= ($profile['is_locked'] ?? 0) ? 'Locked' : 'Incomplete' ?></div>
      <div class="stat-label">Profile status</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-cake2-fill stat-watermark"></i>
      <div class="stat-ic bg-amber"><i class="bi bi-cake2"></i></div>
      <div class="stat-value"><?= count($todaysBirthdays) ?></div>
      <div class="stat-label">Today's birthdays</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-cake-fill stat-watermark"></i>
      <div class="stat-ic bg-brand"><i class="bi bi-cake"></i></div>
      <div class="stat-value"><?= count($tomorrowsBirthdays) ?></div>
      <div class="stat-label">Tomorrow's birthdays</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-bell-fill stat-watermark"></i>
      <div class="stat-ic bg-red"><i class="bi bi-bell"></i></div>
      <div class="stat-value"><?= count($notifications) ?></div>
      <div class="stat-label">Notifications</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cake2 text-warning"></i> Birthdays</span>
        <a href="/calendar" class="pill-link">View All</a>
      </div>
      <div class="card-body-x">
        <?php if (empty($todaysBirthdays) && empty($tomorrowsBirthdays)): ?>
          <div class="empty-state-lg">
            <div class="empty-ic-lg" style="background:var(--amber-tint);color:var(--amber)"><i class="bi bi-cake2"></i></div>
            <div class="empty-title-lg">No birthdays today or tomorrow</div>
            <div class="empty-sub-lg">We'll notify you when someone is celebrating!</div>
          </div>
        <?php else: ?>
          <?php foreach ($todaysBirthdays as $b): ?>
            <div class="row-item">
              <?php if (!empty($b['profile_photo_path'])): ?>
                <img src="<?= e($b['profile_photo_path']) ?>" class="avatar-sm" style="width:34px;height:34px" alt="" onerror="this.outerHTML='<span class=&quot;avatar-sm&quot; style=&quot;width:34px;height:34px&quot;><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>'">
              <?php else: ?>
                <span class="avatar-sm" style="width:34px;height:34px"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <?php endif; ?>
              <div class="row-name"><?= e($b['full_name']) ?></div>
              <span class="chip chip-today">Today</span>
            </div>
          <?php endforeach; ?>
          <?php foreach ($tomorrowsBirthdays as $b): ?>
            <div class="row-item">
              <?php if (!empty($b['profile_photo_path'])): ?>
                <img src="<?= e($b['profile_photo_path']) ?>" class="avatar-sm" style="width:34px;height:34px" alt="" onerror="this.outerHTML='<span class=&quot;avatar-sm&quot; style=&quot;width:34px;height:34px&quot;><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>'">
              <?php else: ?>
                <span class="avatar-sm" style="width:34px;height:34px"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <?php endif; ?>
              <div class="row-name"><?= e($b['full_name']) ?></div>
              <span class="chip chip-tomorrow">Tomorrow</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-head-x d-flex justify-content-between align-items-center">
        <span><i class="bi bi-megaphone text-primary"></i> Latest Announcements</span>
        <a href="/announcements" class="pill-link">View All</a>
      </div>
      <div class="card-body-x">
        <?php if (empty($announcements)): ?>
          <div class="empty-state-lg">
            <div class="empty-ic-lg" style="background:var(--brand-tint);color:var(--brand)"><i class="bi bi-megaphone"></i></div>
            <div class="empty-title-lg">No announcements yet</div>
            <div class="empty-sub-lg">We'll let you know when something's new!</div>
          </div>
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
      <h3>Secret Santa <?= e($activeEvent['event_year']) ?> is live 🎁</h3>
      <p>Your recipient is <strong><?= e($mySecretSanta['recipient_name']) ?></strong> — view their wishlist and send an anonymous note.</p>
    </div>
  </div>
  <a href="/secret-santa" class="btn-white">View details</a>
</div>
<?php endif; ?>
