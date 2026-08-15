<h2 class="h5 fw-bold mb-3">Departments</h2>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Name</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($departments as $d): ?>
              <tr>
                <td>
                  <form method="post" action="/admin/departments/<?= $d['id'] ?>/edit" class="d-flex gap-2 align-items-center">
                    <?= $csrfField ?>
                    <input type="text" name="name" value="<?= e($d['name']) ?>" class="form-control form-control-sm" style="max-width:220px">
                    <select name="is_active" class="form-select form-select-sm" style="max-width:110px">
                      <option value="1" <?= $d['is_active'] ? 'selected' : '' ?>>Active</option>
                      <option value="0" <?= !$d['is_active'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary">Save</button>
                  </form>
                </td>
                <td><span class="badge <?= $d['is_active'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $d['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td>
                  <form method="post" action="/admin/departments/<?= $d['id'] ?>/delete" onsubmit="return confirm('Deactivate this department?');">
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
        <h3 class="h6 fw-bold mb-3">Add Department</h3>
        <form method="post" action="/admin/departments">
          <?= $csrfField ?>
          <input type="text" name="name" class="form-control mb-2" placeholder="Department name" required>
          <button class="btn btn-primary w-100">Add</button>
        </form>
      </div>
    </div>
  </div>
</div>
