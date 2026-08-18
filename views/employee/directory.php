<form method="get" action="/directory" class="filterbar d-flex flex-wrap gap-2 align-items-center mb-4">
  <div class="flex-grow-1" style="min-width:220px">
    <input type="text" name="q" class="form-control" placeholder="Search name or employee ID" value="<?= e($filters['q']) ?>">
  </div>
  <div style="min-width:160px">
    <select name="department_id" class="form-select">
      <option value="">Department</option>
      <?php foreach ($departments as $d): ?>
        <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div style="min-width:160px">
    <select name="designation_id" class="form-select">
      <option value="">Designation</option>
      <?php foreach ($designations as $d): ?>
        <option value="<?= $d['id'] ?>" <?= ($filters['designation_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div style="min-width:160px">
    <select name="birthday_month" class="form-select">
      <option value="">Birthday Month</option>
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= ($filters['birthday_month'] ?? null) == $m ? 'selected' : '' ?>><?= e(date('F', mktime(0, 0, 0, $m, 1))) ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <button type="submit" class="btn btn-primary px-4"><i class="bi bi-funnel me-1"></i>Filter</button>
</form>

<?php if (empty($employees)): ?>
  <div class="empty-state"><i class="bi bi-people"></i><p class="mt-2">No employees match your search.</p></div>
<?php else: ?>
  <div class="result-count mb-3"><?= (int) $total ?> employee<?= $total == 1 ? '' : 's' ?></div>

  <div class="row g-3">
    <?php foreach ($employees as $emp): ?>
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card employee-card h-100 text-center">
          <div class="card-body">
            <?php if (!empty($emp['profile_photo_path'])): ?>
              <img src="<?= e($emp['profile_photo_path']) ?>" class="avatar-lg mx-auto mb-3" alt="">
            <?php else: ?>
              <div class="avatar-lg mx-auto mb-3"><?= e(mb_substr($emp['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div class="fw-bold small"><?= e($emp['full_name']) ?></div>
            <div class="emp-role mt-1"><?= e($emp['designation_name'] ?? '-') ?></div>
            <div class="emp-dept mt-1"><?= e($emp['department_name'] ?? '-') ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php $pages = (int) ceil($total / $perPage); ?>
  <?php if ($pages > 1): ?>
    <nav class="mt-4">
      <ul class="pagination justify-content-center flex-wrap">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
<?php endif; ?>
