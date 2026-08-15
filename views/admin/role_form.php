<h2 class="h5 fw-bold mb-3"><?= $role ? 'Edit Role' : 'Create Role' ?></h2>

<form method="post" action="<?= $role ? '/admin/roles/' . $role['id'] . '/edit' : '/admin/roles' ?>">
  <?= $csrfField ?>
  <div class="card mb-3">
    <div class="card-body row g-3">
      <div class="col-md-6">
        <label class="form-label small">Role Name</label>
        <input type="text" name="name" class="form-control" value="<?= e($role['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small">Description</label>
        <input type="text" name="description" class="form-control" value="<?= e($role['description'] ?? '') ?>">
      </div>
      <?php if ($role): ?>
        <div class="col-md-4">
          <label class="form-label small">Status</label>
          <select name="is_active" class="form-select">
            <option value="1" <?= $role['is_active'] ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= !$role['is_active'] ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h3 class="h6 fw-bold mb-3">Permissions</h3>
      <?php foreach ($permissionGroups as $group => $perms): ?>
        <div class="mb-3">
          <div class="fw-semibold small text-uppercase text-muted mb-1"><?= e(str_replace('_', ' ', $group)) ?></div>
          <div class="row">
            <?php foreach ($perms as $p): ?>
              <div class="col-sm-6 col-lg-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" id="perm<?= $p['id'] ?>" <?= in_array($p['id'], $assignedPermissionIds) ? 'checked' : '' ?>>
                  <label class="form-check-label small" for="perm<?= $p['id'] ?>"><?= e($p['name']) ?></label>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="text-end">
    <a href="/admin/roles" class="btn btn-outline-secondary me-2">Cancel</a>
    <button class="btn btn-primary px-4">Save Role</button>
  </div>
</form>
