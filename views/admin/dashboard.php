<div class="dash-hero">
  <div>
    <h1>Welcome back, <?= e(explode(' ', $currentUser['full_name'] ?? '')[0] ?? '') ?>! 👋</h1>
    <p>Here's what's happening at <?= e(setting('company_name', 'Adhook Media')) ?> today.</p>
  </div>
  <div class="dash-hero-deco">
    <figure class="dash-hero-quote mb-0">
      &ldquo;Great teams build great things.&rdquo;
      <footer>&mdash; <?= e(setting('company_name', 'Adhook Media')) ?></footer>
    </figure>
    <span class="dash-hero-ic"><i class="bi bi-rocket-takeoff-fill"></i></span>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-people-fill stat-watermark"></i>
      <div class="stat-ic bg-brand"><i class="bi bi-people"></i></div>
      <div class="stat-value"><?= $counts['total'] ?></div>
      <div class="stat-label">Total employees</div>
      <?php if ($counts['new_this_month'] > 0): ?>
        <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i>+<?= $counts['new_this_month'] ?> this month</div>
      <?php else: ?>
        <div class="stat-trend"><span class="trend-muted">No new hires this month</span></div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-check-circle-fill stat-watermark"></i>
      <div class="stat-ic bg-green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-value"><?= $counts['active'] ?></div>
      <div class="stat-label">Active</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-x-circle-fill stat-watermark"></i>
      <div class="stat-ic bg-red"><i class="bi bi-x-circle"></i></div>
      <div class="stat-value"><?= $counts['inactive'] ?></div>
      <div class="stat-label">Inactive</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card-x">
      <i class="bi bi-hourglass-split stat-watermark"></i>
      <div class="stat-ic bg-amber"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-value"><?= $counts['pending_profiles'] ?></div>
      <div class="stat-label">Pending profiles</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6">
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
              <span class="avatar-sm" style="width:34px;height:34px"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <div class="row-name"><?= e($b['full_name']) ?></div>
              <span class="chip chip-today">Today</span>
            </div>
          <?php endforeach; ?>
          <?php foreach ($tomorrowsBirthdays as $b): ?>
            <div class="row-item">
              <span class="avatar-sm" style="width:34px;height:34px"><?= e(mb_substr($b['full_name'], 0, 1)) ?></span>
              <div class="row-name"><?= e($b['full_name']) ?></div>
              <span class="chip chip-tomorrow">Tomorrow</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-head-x d-flex justify-content-between align-items-center">
        <span><i class="bi bi-award text-success"></i> Work Anniversaries</span>
        <a href="/calendar" class="pill-link">View All</a>
      </div>
      <div class="card-body-x">
        <?php if (empty($todaysAnniversaries) && empty($tomorrowsAnniversaries)): ?>
          <div class="empty-state-lg">
            <div class="empty-ic-lg" style="background:var(--green-tint);color:var(--green)"><i class="bi bi-award"></i></div>
            <div class="empty-title-lg">No anniversaries today or tomorrow</div>
            <div class="empty-sub-lg">Let's celebrate our amazing team members!</div>
          </div>
        <?php else: ?>
          <?php foreach ($todaysAnniversaries as $a): ?>
            <div class="row-item">
              <span class="avatar-sm" style="width:34px;height:34px;background:linear-gradient(135deg,#16a34a,#0f7a37)"><?= e(mb_substr($a['full_name'], 0, 1)) ?></span>
              <div class="row-name"><?= e($a['full_name']) ?></div>
              <span class="chip chip-today">Today</span>
            </div>
          <?php endforeach; ?>
          <?php foreach ($tomorrowsAnniversaries as $a): ?>
            <div class="row-item">
              <span class="avatar-sm" style="width:34px;height:34px;background:linear-gradient(135deg,#16a34a,#0f7a37)"><?= e(mb_substr($a['full_name'], 0, 1)) ?></span>
              <div class="row-name"><?= e($a['full_name']) ?></div>
              <span class="chip chip-tomorrow">Tomorrow</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head-x d-flex justify-content-between align-items-center">
    <span><i class="bi bi-calendar-event text-primary"></i> Upcoming Events</span>
    <a href="/announcements" class="pill-link">View All</a>
  </div>
  <?php if (empty($upcomingEvents)): ?>
    <div class="empty-state-lg">
      <div class="empty-ic-lg" style="background:var(--brand-tint);color:var(--brand)"><i class="bi bi-calendar-event"></i></div>
      <div class="empty-title-lg">No upcoming events</div>
      <div class="empty-sub-lg">Stay tuned for exciting events!</div>
    </div>
  <?php else: ?>
    <?php foreach ($upcomingEvents as $ev): ?>
      <div class="ev-item">
        <div class="ev-date"><b><?= e(date('d', strtotime($ev['event_date']))) ?></b><span><?= e(date('M', strtotime($ev['event_date']))) ?></span></div>
        <div>
          <div class="row-name"><?= e($ev['title']) ?></div>
          <div class="row-sub mt-1"><?= format_date($ev['event_date']) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
