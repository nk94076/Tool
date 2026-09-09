<?php
$categoryTheme = [
    'general' => ['ic' => 'bi-megaphone-fill', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'],
    'holiday' => ['ic' => 'bi-tree-fill', 'bg' => 'var(--green-tint)', 'fg' => 'var(--green)'],
    'notice' => ['ic' => 'bi-exclamation-circle-fill', 'bg' => 'var(--amber-tint)', 'fg' => 'var(--amber)'],
    'event' => ['ic' => 'bi-calendar-event-fill', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
];
$themeFor = fn(string $cat) => $categoryTheme[$cat] ?? ['ic' => 'bi-megaphone', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'];
?>
<div class="admin-breadcrumb">Administration <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Announcements</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Announcements</h1>
      <p>Publish updates and notify the whole team.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-megaphone-fill"></i></span>
    <div class="dir-hero-script">Stay<br>Informed<br>Stay Connected</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-brand"><i class="bi bi-megaphone"></i></div>
      <div><div class="stat-value"><?= count($announcements) ?></div><div class="stat-label">Total Announcements</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic bg-green"><i class="bi bi-calendar-month"></i></div>
      <div><div class="stat-value"><?= $thisMonthCount ?></div><div class="stat-label">This Month</div></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card-h">
      <div class="stat-ic" style="background:#eff6ff;color:#1d4ed8"><i class="bi bi-calendar-event"></i></div>
      <div><div class="stat-value"><?= $upcomingCount ?></div><div class="stat-label">Upcoming Events</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="filterbar d-flex flex-wrap gap-2 align-items-center mb-3">
      <div class="flex-grow-1 field-ic" style="min-width:220px">
        <i class="bi bi-search"></i>
        <input type="text" id="annSearch" class="form-control" placeholder="Search announcements...">
      </div>
      <div style="min-width:160px">
        <select id="annCategoryFilter" class="form-select">
          <option value="">All Categories</option>
          <option value="general">General</option>
          <option value="holiday">Holiday</option>
          <option value="notice">Notice</option>
          <option value="event">Event</option>
        </select>
      </div>
    </div>

    <div id="announcementsList">
      <?php foreach ($announcements as $a): $theme = $themeFor($a['category']); ?>
        <div class="card event-card mb-3" data-title="<?= e(mb_strtolower($a['title'])) ?>" data-category="<?= e($a['category']) ?>">
          <div class="card-body">
            <div class="d-flex gap-3">
              <span class="event-ic" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><i class="bi <?= $theme['ic'] ?>"></i></span>
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                  <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                      <h3 class="h6 fw-bold mb-0"><?= e($a['title']) ?></h3>
                      <span class="status-tag" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><?= e(ucfirst($a['category'])) ?></span>
                    </div>
                    <p class="text-muted small mb-0 mt-1"><?= e(mb_substr(strip_tags($a['body']), 0, 180)) ?></p>
                  </div>
                  <?php if (can('announcements.manage')): ?>
                    <form method="post" action="/admin/announcements/<?= $a['id'] ?>/delete" onsubmit="return confirm('Delete this announcement?');">
                      <?= $csrfField ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                    </form>
                  <?php endif; ?>
                </div>
                <div class="event-meta">
                  <div class="event-meta-item">
                    <i class="bi bi-calendar3"></i>
                    <div><p class="label mb-0">Published</p><p class="value mb-0"><?= format_date($a['created_at']) ?></p></div>
                  </div>
                  <?php if (!empty($a['event_date'])): ?>
                    <div class="event-meta-item">
                      <i class="bi bi-calendar-event"></i>
                      <div><p class="label mb-0">Event Date</p><p class="value mb-0"><?= format_date($a['event_date']) ?></p></div>
                    </div>
                  <?php endif; ?>
                  <?php if ($a['notify_email']): ?>
                    <div class="event-meta-item">
                      <i class="bi bi-envelope-check"></i>
                      <div><p class="label mb-0">Notified</p><p class="value mb-0">Email + Push</p></div>
                    </div>
                  <?php else: ?>
                    <div class="event-meta-item">
                      <i class="bi bi-bell"></i>
                      <div><p class="label mb-0">Notified</p><p class="value mb-0">Push only</p></div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (empty($announcements)): ?>
      <div class="card">
        <div class="empty-state-lg">
          <i class="bi bi-megaphone mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
          <p class="text-muted mb-0">No announcements yet. Publish your first update.</p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <?php if (can('announcements.manage')): ?>
      <div class="card">
        <div class="card-head-x"><i class="bi bi-plus-circle"></i> New Announcement</div>
        <div class="card-body-x px-3 pb-3">
          <form method="post" action="/admin/announcements">
            <?= $csrfField ?>
            <label class="form-label small fw-semibold">Title</label>
            <input type="text" name="title" class="form-control mb-3" required>
            <label class="form-label small fw-semibold">Body</label>
            <textarea name="body" class="form-control mb-3" rows="4" required></textarea>
            <label class="form-label small fw-semibold">Category</label>
            <select name="category" class="form-select mb-3">
              <option value="general">General</option>
              <option value="holiday">Holiday</option>
              <option value="notice">Notice</option>
              <option value="event">Event</option>
            </select>
            <label class="form-label small fw-semibold">Event Date (optional)</label>
            <input type="date" name="event_date" class="form-control mb-3">
            <div class="form-check mb-1">
              <input class="form-check-input" type="checkbox" name="notify_push" value="1" id="notifyPush" checked>
              <label class="form-check-label small" for="notifyPush">Send browser push notification</label>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="notify_email" value="1" id="notifyEmail">
              <label class="form-check-label small" for="notifyEmail">Send email notification</label>
            </div>
            <button class="btn btn-primary w-100"><i class="bi bi-send me-1"></i>Publish</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  var search = document.getElementById('annSearch');
  var category = document.getElementById('annCategoryFilter');
  var cards = document.querySelectorAll('#announcementsList > [data-title]');
  function apply() {
    var q = (search.value || '').toLowerCase().trim();
    var cat = category.value;
    cards.forEach(function (card) {
      var matchesQ = !q || card.dataset.title.indexOf(q) !== -1;
      var matchesCat = !cat || card.dataset.category === cat;
      card.style.display = (matchesQ && matchesCat) ? '' : 'none';
    });
  }
  search.addEventListener('input', apply);
  category.addEventListener('change', apply);
})();
</script>
