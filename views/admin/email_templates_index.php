<h2 class="h5 fw-bold mb-3">Email Templates</h2>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Name</th><th>Subject</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($templates as $t): ?>
          <tr>
            <td class="fw-semibold small"><?= e($t['name']) ?></td>
            <td class="small text-muted"><?= e($t['subject']) ?></td>
            <td><span class="badge <?= $t['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $t['is_active'] ? 'Active' : 'Inactive' ?></span></td>
            <td><a href="/admin/email-templates/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
