<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card"><div class="stat-label">Total Employees</div><div class="stat-value"><?= $counts['total'] ?></div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card"><div class="stat-label">Active</div><div class="stat-value text-success"><?= $counts['active'] ?></div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card"><div class="stat-label">Inactive</div><div class="stat-value text-danger"><?= $counts['inactive'] ?></div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card"><div class="stat-label">Pending Profiles</div><div class="stat-value text-warning"><?= $counts['pending_profiles'] ?></div></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3"><i class="bi bi-cake2 text-primary me-1"></i>Today's Birthdays</h3>
        <?php if (empty($todaysBirthdays)): ?><p class="text-muted small mb-0">None today</p><?php endif; ?>
        <?php foreach ($todaysBirthdays as $b): ?><div class="small mb-1"><?= e($b['full_name']) ?></div><?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3"><i class="bi bi-cake text-primary me-1"></i>Tomorrow's Birthdays</h3>
        <?php if (empty($tomorrowsBirthdays)): ?><p class="text-muted small mb-0">None tomorrow</p><?php endif; ?>
        <?php foreach ($tomorrowsBirthdays as $b): ?><div class="small mb-1"><?= e($b['full_name']) ?></div><?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3"><i class="bi bi-award text-success me-1"></i>Today's Anniversaries</h3>
        <?php if (empty($todaysAnniversaries)): ?><p class="text-muted small mb-0">None today</p><?php endif; ?>
        <?php foreach ($todaysAnniversaries as $a): ?><div class="small mb-1"><?= e($a['full_name']) ?></div><?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3"><i class="bi bi-award-fill text-success me-1"></i>Tomorrow's Anniversaries</h3>
        <?php if (empty($tomorrowsAnniversaries)): ?><p class="text-muted small mb-0">None tomorrow</p><?php endif; ?>
        <?php foreach ($tomorrowsAnniversaries as $a): ?><div class="small mb-1"><?= e($a['full_name']) ?></div><?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3"><i class="bi bi-calendar-event text-primary me-1"></i>Upcoming Events</h3>
        <?php if (empty($upcomingEvents)): ?><p class="text-muted small mb-0">No upcoming events.</p><?php endif; ?>
        <?php foreach ($upcomingEvents as $e): ?>
          <div class="d-flex justify-content-between border-bottom py-2 small">
            <span><?= e($e['title']) ?></span><span class="text-muted"><?= format_date($e['event_date']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
