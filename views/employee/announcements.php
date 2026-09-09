<?php
$categoryTheme = [
    'general' => ['ic' => 'bi-megaphone-fill', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'],
    'holiday' => ['ic' => 'bi-tree-fill', 'bg' => 'var(--green-tint)', 'fg' => 'var(--green)'],
    'notice' => ['ic' => 'bi-exclamation-circle-fill', 'bg' => 'var(--amber-tint)', 'fg' => 'var(--amber)'],
    'event' => ['ic' => 'bi-calendar-event-fill', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
];
$themeFor = fn(string $cat) => $categoryTheme[$cat] ?? ['ic' => 'bi-megaphone', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'];
?>
<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Announcements</h1>
      <p>Company news and updates, all in one place.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-megaphone-fill"></i></span>
    <div class="dir-hero-script">Stay<br>Informed<br>Stay Connected</div>
  </div>
</div>

<?php if (empty($announcements)): ?>
  <div class="card">
    <div class="empty-state-lg">
      <i class="bi bi-megaphone mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
      <p class="text-muted mb-0">No announcements yet.</p>
    </div>
  </div>
<?php else: ?>
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
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <h3 class="h6 fw-bold mb-0"><?= e($a['title']) ?></h3>
                <span class="status-tag" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><?= e(ucfirst($a['category'])) ?></span>
              </div>
              <p class="text-muted small mb-0 mt-1"><?= nl2br(e($a['body'])) ?></p>
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
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
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
<?php endif; ?>
