<h2 class="h5 fw-bold mb-3"><?= $event ? 'Edit' : 'Create' ?> Secret Santa Event</h2>

<form method="post" action="<?= $event ? '/admin/secret-santa/' . $event['id'] . '/edit' : '/admin/secret-santa' ?>">
  <?= $csrfField ?>
  <div class="card mb-3">
    <div class="card-body row g-3">
      <div class="col-md-6">
        <label class="form-label small">Event Name</label>
        <input type="text" name="name" class="form-control" value="<?= e($event['name'] ?? 'Secret Santa ' . date('Y')) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small">Year</label>
        <input type="number" name="event_year" class="form-control" value="<?= e((string) ($event['event_year'] ?? date('Y'))) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small">Registration Deadline</label>
        <input type="date" name="registration_deadline" class="form-control" value="<?= e($event['registration_deadline'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small">Gift Exchange Date</label>
        <input type="date" name="gift_exchange_date" class="form-control" value="<?= e($event['gift_exchange_date'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small">Minimum Budget</label>
        <input type="number" name="min_budget" class="form-control" value="<?= e((string) ($event['min_budget'] ?? setting('default_secret_santa_min_budget', 500))) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Maximum Budget</label>
        <input type="number" name="max_budget" class="form-control" value="<?= e((string) ($event['max_budget'] ?? setting('default_secret_santa_max_budget', 1500))) ?>">
      </div>
      <div class="col-12">
        <label class="form-label small">Participant Rules</label>
        <textarea name="rules" class="form-control" rows="3"><?= e($event['rules'] ?? '') ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label small">Status</label>
        <select name="status" class="form-select">
          <?php foreach (['draft' => 'Draft', 'active' => 'Active (open for registration)', 'cancelled' => 'Cancelled'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($event['status'] ?? 'draft') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 form-check align-self-center mt-4">
        <input class="form-check-input" type="checkbox" name="allow_inactive_employees" value="1" id="allowInactive" <?= !empty($event['allow_inactive_employees']) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="allowInactive">Allow inactive employees</label>
      </div>
      <div class="col-md-4 form-check align-self-center mt-4">
        <input class="form-check-input" type="checkbox" name="avoid_previous_year_pairing" value="1" id="avoidPrev" <?= ($event['avoid_previous_year_pairing'] ?? 1) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="avoidPrev">Avoid repeating last year's pairing</label>
      </div>
    </div>
  </div>
  <div class="text-end">
    <a href="/admin/secret-santa" class="btn btn-outline-secondary me-2">Cancel</a>
    <button class="btn btn-primary px-4">Save Event</button>
  </div>
</form>
