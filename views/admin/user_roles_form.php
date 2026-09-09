<div class="admin-breadcrumb"><a href="/admin/employees">Manage Employees</a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <a href="/admin/employees/<?= $user['id'] ?>"><?= e($user['full_name']) ?></a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Assign Roles</span></div>

<div class="page-hero-title mb-3">
  <span class="bar"></span>
  <div>
    <h1 class="h4 fw-bold mb-1">Assign Roles</h1>
    <p class="text-muted small mb-0"><?= e($user['full_name']) ?></p>
  </div>
</div>

<form method="post" action="/admin/employees/<?= $user['id'] ?>/roles">
  <?= $csrfField ?>
  <div class="card mb-3">
    <div class="card-head-x"><i class="bi bi-shield-check text-primary"></i> Roles</div>
    <div class="card-body-x p-3">
      <?php foreach ($roles as $role): ?>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $role['id'] ?>" id="role<?= $role['id'] ?>" <?= in_array($role['id'], $assignedRoleIds) ? 'checked' : '' ?>>
          <label class="form-check-label" for="role<?= $role['id'] ?>"><?= e($role['name']) ?> <span class="text-muted small">— <?= e($role['description'] ?? '') ?></span></label>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="text-end">
    <a href="/admin/employees/<?= $user['id'] ?>" class="btn btn-outline-secondary me-2">Cancel</a>
    <button class="btn btn-primary px-4">Save Roles</button>
  </div>
</form>
