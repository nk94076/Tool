<h2 class="h5 fw-bold mb-3">Designations</h2>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Name</th><th>Department</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($designations as $d): ?>
              <tr>
                <td>
                  <form method="post" action="/admin/designations/<?= $d['id'] ?>/edit" class="d-flex gap-2 align-items-center flex-wrap">
                    <?= $csrfField ?>
                    <input type="text" name="name" value="<?= e($d['name']) ?>" class="form-control form-control-sm" style="max-width:180px">
                    <select name="department_id" class="form-select form-select-sm" style="max-width:160px">
                      <option value="">None</option>
                      <?php foreach ($departments as $dep): ?>
                        <option value="<?= $dep['id'] ?>" <?= (int) $d['department_id'] === (int) $dep['id'] ? 'selected' : '' ?>><?= e($dep['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <select name="is_active" class="form-select form-select-sm" style="max-width:110px">
                      <option value="1" <?= $d['is_active'] ? 'selected' : '' ?>>Active</option>
                      <option value="0" <?= !$d['is_active'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Save</button>
                  </form>
                </td>
                <td class="text-muted small"><?= e($d['department_name'] ?? '-') ?></td>
                <td><span class="badge <?= $d['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $d['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td>
                  <form method="post" action="/admin/designations/<?= $d['id'] ?>/delete" onsubmit="return confirm('Deactivate this designation?');">
                    <?= $csrfField ?><button class="btn btn-sm btn-outline-danger">Deactivate</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3">Add Designation</h3>
        <form method="post" action="/admin/designations">
          <?= $csrfField ?>
          <input type="text" name="name" class="form-control mb-2" placeholder="Designation name" required>
          <select name="department_id" class="form-select mb-2">
            <option value="">No Department</option>
            <?php foreach ($departments as $dep): ?><option value="<?= $dep['id'] ?>"><?= e($dep['name']) ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-primary w-100">Add</button>
        </form>
      </div>
    </div>
  </div>
</div>
