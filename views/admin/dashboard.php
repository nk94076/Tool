<p class="text-uppercase small fw-bold text-muted mb-3" style="font-size:.72rem;letter-spacing:.05em">Overview</p>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-brand"><i class="bi bi-people"></i></div>
      <div class="stat-value"><?= $counts['total'] ?></div>
      <div class="stat-label">Total employees</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-green"><i class="bi bi-check-circle"></i></div>
      <div class="stat-value"><?= $counts['active'] ?></div>
      <div class="stat-label">Active</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-red"><i class="bi bi-x-circle"></i></div>
      <div class="stat-value"><?= $counts['inactive'] ?></div>
      <div class="stat-label">Inactive</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-ic bg-amber"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-value"><?= $counts['pending_profiles'] ?></div>
      <div class="stat-label">Pending profiles</div>
    </div>
  </div>
</div>

<p class="text-uppercase small fw-bold text-muted mb-3" style="font-size:.72rem;letter-spacing:.05em">Celebrations</p>
<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-head-x"><i class="bi bi-cake2 text-warning"></i>Birthdays</div>
      <div class="card-body-x">
        <?php if (empty($todaysBirthdays) && empty($tomorrowsBirthdays)): ?>
          <div class="empty-state py-3"><i class="bi bi-calendar2-x"></i><p class="small mb-0 mt-2">No birthdays today or tomorrow</p></div>
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
      <div class="card-head-x"><i class="bi bi-award text-success"></i>Work Anniversaries</div>
      <div class="card-body-x">
        <?php if (empty($todaysAnniversaries) && empty($tomorrowsAnniversaries)): ?>
          <div class="empty-state py-3"><i class="bi bi-calendar2-x"></i><p class="small mb-0 mt-2">No anniversaries today or tomorrow</p></div>
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

<p class="text-uppercase small fw-bold text-muted mb-3" style="font-size:.72rem;letter-spacing:.05em">Upcoming events</p>
<div class="card">
  <?php if (empty($upcomingEvents)): ?>
    <div class="empty-state py-4"><i class="bi bi-calendar-event"></i><p class="small mb-0 mt-2">No upcoming events.</p></div>
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
