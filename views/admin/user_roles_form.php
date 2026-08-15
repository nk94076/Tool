<h2 class="h5 fw-bold mb-3">Assign Roles - <?= e($user['full_name']) ?></h2>

<form method="post" action="/admin/employees/<?= $user['id'] ?>/roles">
  <?= $csrfField ?>
  <div class="card mb-3">
    <div class="card-body">
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
