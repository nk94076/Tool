<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 fw-bold mb-0">Roles &amp; Permissions</h2>
  <?php if (can('roles.create')): ?><a href="/admin/roles/create" class="btn btn-primary btn-sm">+ New Role</a><?php endif; ?>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Role</th><th>Description</th><th>Permissions</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($roles as $role): ?>
          <tr>
            <td class="fw-semibold"><?= e($role['name']) ?> <?php if ($role['is_system']): ?><span class="badge bg-dark ms-1">System</span><?php endif; ?></td>
            <td class="text-muted small"><?= e($role['description'] ?? '') ?></td>
            <td><?= $role['permission_count'] ?></td>
            <td><span class="badge <?= $role['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $role['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td>
              <?php if (!$role['is_system']): ?>
                <?php if (can('roles.edit')): ?><a href="/admin/roles/<?= $role['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a><?php endif; ?>
                <?php if (can('roles.delete')): ?>
                  <form method="post" action="/admin/roles/<?= $role['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this role?');">
                    <?= $csrfField ?><button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
