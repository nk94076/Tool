<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h2 class="h5 fw-bold mb-0">Manage Employees</h2>
</div>

<form method="get" class="card mb-3">
  <div class="card-body row g-2">
    <div class="col-12 col-md-5"><input type="text" name="q" class="form-control" placeholder="Search" value="<?= e($filters['q']) ?>"></div>
    <div class="col-6 col-md-3">
      <select name="department_id" class="form-select">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <?php foreach (['active','inactive','pending_verification','locked'] as $s): ?>
          <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2 d-grid"><button class="btn btn-primary">Filter</button></div>
  </div>
</form>

<div class="card table-responsive-cards">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Name</th><th>Department</th><th>Designation</th><th>Status</th><th>Profile</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($employees as $emp): ?>
          <tr>
            <td><?= e($emp['full_name']) ?></td>
            <td><?= e($emp['department_name'] ?? '-') ?></td>
            <td><?= e($emp['designation_name'] ?? '-') ?></td>
            <td><span class="badge badge-status-<?= $emp['status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($emp['status']) ?></span></td>
            <td><?= e($emp['profile_status']) ?></td>
            <td><a href="/admin/employees/<?= $emp['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="row-cards p-2">
    <?php foreach ($employees as $emp): ?>
      <a href="/admin/employees/<?= $emp['id'] ?>" class="card mb-2 text-decoration-none text-body">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-semibold small"><?= e($emp['full_name']) ?></div>
            <div class="text-muted" style="font-size:.72rem"><?= e($emp['designation_name'] ?? '-') ?> &middot; <?= e($emp['department_name'] ?? '-') ?></div>
          </div>
          <span class="badge badge-status-<?= $emp['status'] === 'active' ? 'active' : 'inactive' ?>"><?= e($emp['status']) ?></span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php $pages = (int) ceil($total / $perPage); ?>
<?php if ($pages > 1): ?>
  <nav class="mt-3"><ul class="pagination justify-content-center flex-wrap">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a></li>
    <?php endfor; ?>
  </ul></nav>
<?php endif; ?>
