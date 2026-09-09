<div class="admin-breadcrumb"><a href="/admin/email-templates">Email Templates</a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Edit</span></div>

<div class="page-hero-title mb-3">
  <span class="bar"></span>
  <div>
    <h1 class="h4 fw-bold mb-1">Edit Template: <?= e($template['name']) ?></h1>
    <p class="text-muted small mb-0">Available variables: <code>{{employee_name}}</code> <code>{{designation}}</code> <code>{{department}}</code> <code>{{joining_date}}</code> <code>{{years_completed}}</code> <code>{{event_date}}</code> <code>{{otp_code}}</code> <code>{{expiry_minutes}}</code></p>
  </div>
</div>

<form method="post" action="/admin/email-templates/<?= $template['id'] ?>/edit">
  <?= $csrfField ?>
  <div class="card mb-3">
    <div class="card-head-x"><i class="bi bi-envelope text-primary"></i> Template</div>
    <div class="card-body-x p-3">
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
