<?php
$statusTheme = [
    'draft' => ['label' => 'Draft', 'bg' => 'var(--border-soft)', 'fg' => 'var(--text-muted)'],
    'active' => ['label' => 'Active', 'bg' => 'var(--green-tint)', 'fg' => 'var(--green)'],
    'registration_closed' => ['label' => 'Registration Closed', 'bg' => 'var(--amber-tint)', 'fg' => 'var(--amber)'],
    'matched' => ['label' => 'Matched', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
    'completed' => ['label' => 'Completed', 'bg' => 'var(--brand-tint)', 'fg' => 'var(--brand)'],
    'cancelled' => ['label' => 'Cancelled', 'bg' => 'var(--red-tint)', 'fg' => 'var(--red)'],
];
$themeFor = fn(string $status) => $statusTheme[$status] ?? ['label' => ucfirst($status), 'bg' => 'var(--border-soft)', 'fg' => 'var(--text-muted)'];
$isSuperAdmin = !empty($currentUser['is_super_admin']);
?>
<div class="admin-breadcrumb">Events <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Secret Santa</span></div>

<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Secret Santa Events <span aria-hidden="true">🎁</span></h1>
      <p>Create, manage and spread happiness across teams.</p>
    </div>
  </div>
  <div class="d-flex align-items-center gap-3 flex-wrap">
    <div class="dir-hero-deco">
      <span class="dir-hero-ic"><i class="bi bi-gift-fill"></i></span>
      <div class="dir-hero-script">Small<br>Gifts<br>Big Smiles</div>
    </div>
    <?php if (can('secret_santa.manage')): ?>
      <a href="/admin/secret-santa/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Event</a>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-head-x"><i class="bi bi-gift"></i> All Events (<?= count($events) ?>)</div>
  <div class="filterbar d-flex flex-wrap gap-2 align-items-center px-3 pt-3 pb-3">
    <div class="flex-grow-1 field-ic" style="min-width:220px">
      <i class="bi bi-search"></i>
      <input type="text" id="eventSearch" class="form-control" placeholder="Search events...">
    </div>
    <div style="min-width:180px">
      <select id="eventStatusFilter" class="form-select">
        <option value="">All Status</option>
        <?php foreach ($statusTheme as $val => $t): ?>
          <option value="<?= $val ?>"><?= e($t['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>

<div id="eventsList">
  <?php foreach ($events as $event): $theme = $themeFor($event['status']);
    $hasMoreActions = in_array($event['status'], ['active', 'registration_closed', 'matched', 'completed'], true) && (can('secret_santa.manage') || $isSuperAdmin);
  ?>
    <div class="card event-card mb-3" data-name="<?= e(mb_strtolower($event['name'] . ' ' . $event['event_year'])) ?>" data-status="<?= e($event['status']) ?>">
      <div class="card-body">
        <div class="d-flex gap-3">
          <span class="event-ic"><i class="bi bi-gift-fill"></i></span>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between flex-wrap gap-2">
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h3 class="h6 fw-bold mb-0"><?= e($event['name']) ?></h3>
                  <span class="status-tag" style="background:<?= $theme['bg'] ?>;color:<?= $theme['fg'] ?>"><?= e($theme['label']) ?></span>
                </div>
                <?php if (!empty($event['description'])): ?>
                  <p class="text-muted small mb-0 mt-1"><?= e($event['description']) ?></p>
                <?php endif; ?>
              </div>
              <div class="d-flex gap-2 align-self-start">
                <?php if (!in_array($event['status'], ['matched', 'completed'], true) && can('secret_santa.manage')): ?>
                  <a href="/admin/secret-santa/<?= $event['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
                <?php endif; ?>
                <?php if ($hasMoreActions): ?>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <?php if ($event['status'] === 'active' && can('secret_santa.manage')): ?>
                        <li>
                          <form method="post" action="/admin/secret-santa/<?= $event['id'] ?>/close-registration">
                            <?= $csrfField ?><button type="submit" class="dropdown-item">Close Registration</button>
                          </form>
                        </li>
                      <?php endif; ?>
                      <?php if (in_array($event['status'], ['active', 'registration_closed'], true) && can('secret_santa.manage')): ?>
                        <li>
                          <form method="post" action="/admin/secret-santa/<?= $event['id'] ?>/generate-matching" onsubmit="return confirm('This will generate and LOCK the matching. This cannot be undone. Continue?');">
                            <?= $csrfField ?><button type="submit" class="dropdown-item">Generate Matching</button>
                          </form>
                        </li>
                      <?php endif; ?>
                      <?php if (in_array($event['status'], ['matched', 'completed'], true) && $isSuperAdmin): ?>
                        <li><a class="dropdown-item text-danger" href="/admin/secret-santa/<?= $event['id'] ?>/reveal">Emergency Reveal</a></li>
                      <?php endif; ?>
                    </ul>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="event-meta">
              <div class="event-meta-item">
                <i class="bi bi-calendar-event"></i>
                <div><p class="label mb-0">Registration</p><p class="value mb-0"><?= format_date($event['registration_deadline']) ?></p></div>
              </div>
              <div class="event-meta-item">
                <i class="bi bi-calendar-check"></i>
                <div><p class="label mb-0">Exchange</p><p class="value mb-0"><?= format_date($event['gift_exchange_date']) ?></p></div>
              </div>
              <div class="event-meta-item">
                <i class="bi bi-coin"></i>
                <div><p class="label mb-0">Budget</p><p class="value mb-0">₹<?= number_format((float) $event['min_budget']) ?> – ₹<?= number_format((float) $event['max_budget']) ?></p></div>
              </div>
              <div class="event-meta-item">
                <i class="bi bi-people"></i>
                <div><p class="label mb-0">Participants</p><p class="value mb-0"><?= (int) $event['participant_count'] ?></p></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card cta-panel">
  <div>
    <span class="cta-panel-ic"><i class="bi bi-gift"></i></span>
    <?php if (empty($events)): ?>
      <h3>No events yet</h3>
      <p>Create your first Secret Santa event to spread holiday cheer across the team.</p>
    <?php else: ?>
      <h3>Create more joy!</h3>
      <p>Add another Secret Santa event and keep the festive spirit alive across your teams.</p>
    <?php endif; ?>
    <?php if (can('secret_santa.manage')): ?>
      <a href="/admin/secret-santa/create" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-1"></i>New Event</a>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  var search = document.getElementById('eventSearch');
  var status = document.getElementById('eventStatusFilter');
  var cards = document.querySelectorAll('#eventsList > [data-name]');
  function apply() {
    var q = (search.value || '').toLowerCase().trim();
    var st = status.value;
    cards.forEach(function (card) {
      var matchesQ = !q || card.dataset.name.indexOf(q) !== -1;
      var matchesSt = !st || card.dataset.status === st;
      card.style.display = (matchesQ && matchesSt) ? '' : 'none';
    });
  }
  search.addEventListener('input', apply);
  status.addEventListener('change', apply);
})();
</script>
