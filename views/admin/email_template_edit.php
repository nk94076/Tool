<h2 class="h5 fw-bold mb-3">Edit Template: <?= e($template['name']) ?></h2>
<p class="text-muted small">Available variables: <code>{{employee_name}}</code> <code>{{designation}}</code> <code>{{department}}</code> <code>{{joining_date}}</code> <code>{{years_completed}}</code> <code>{{event_date}}</code> <code>{{otp_code}}</code> <code>{{expiry_minutes}}</code></p>

<form method="post" action="/admin/email-templates/<?= $template['id'] ?>/edit">
  <?= $csrfField ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="mb-3">
        <label class="form-label small">Subject</label>
        <input type="text" name="subject" class="form-control" value="<?= e($template['subject']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label small">Body (HTML)</label>
        <textarea name="body_html" class="form-control" rows="12" required><?= e($template['body_html']) ?></textarea>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tplActive" <?= $template['is_active'] ? 'checked' : '' ?>>
        <label class="form-check-label small" for="tplActive">Active</label>
      </div>
    </div>
  </div>
  <div class="text-end">
    <a href="/admin/email-templates" class="btn btn-outline-secondary me-2">Cancel</a>
    <button class="btn btn-primary px-4">Save Template</button>
  </div>
</form>
