<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 fw-bold mb-0">Secret Santa Events</h2>
  <a href="/admin/secret-santa/create" class="btn btn-primary btn-sm">+ New Event</a>
</div>

<?php if (empty($events)): ?>
  <div class="empty-state"><i class="bi bi-gift"></i><p class="mt-2">No Secret Santa events yet.</p></div>
<?php endif; ?>

<?php foreach ($events as $event): ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
          <h3 class="h6 fw-bold mb-1"><?= e($event['name']) ?> (<?= e($event['event_year']) ?>) <span class="badge text-bg-light text-capitalize"><?= e(str_replace('_',' ',$event['status'])) ?></span></h3>
          <p class="text-muted small mb-0">
            Registration: <?= format_date($event['registration_deadline']) ?> &middot;
            Exchange: <?= format_date($event['gift_exchange_date']) ?> &middot;
            Budget ₹<?= number_format((float) $event['min_budget']) ?>-₹<?= number_format((float) $event['max_budget']) ?>
          </p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-self-start">
          <?php if (!in_array($event['status'], ['matched', 'completed'], true)): ?>
            <a href="/admin/secret-santa/<?= $event['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
          <?php endif; ?>
          <?php if ($event['status'] === 'active'): ?>
            <form method="post" action="/admin/secret-santa/<?= $event['id'] ?>/close-registration"><?= $csrfField ?><button class="btn btn-sm btn-outline-secondary">Close Registration</button></form>
          <?php endif; ?>
          <?php if (in_array($event['status'], ['active', 'registration_closed'], true)): ?>
            <form method="post" action="/admin/secret-santa/<?= $event['id'] ?>/generate-matching" onsubmit="return confirm('This will generate and LOCK the matching. This cannot be undone. Continue?');">
              <?= $csrfField ?><button class="btn btn-sm btn-success">Generate Matching</button>
            </form>
          <?php endif; ?>
          <?php if (in_array($event['status'], ['matched', 'completed'], true)): ?>
            <a href="/admin/secret-santa/<?= $event['id'] ?>/reveal" class="btn btn-sm btn-outline-danger">Emergency Reveal</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>
