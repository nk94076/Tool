<?php
// Deterministic (not random) accent per employee, so the same person always
// gets the same card color across page loads.
$cardThemes = [
    ['cover' => 'linear-gradient(135deg,#ede9fe,#ddd6fe)', 'pillBg' => 'var(--brand-tint)', 'pillFg' => 'var(--brand)'],
    ['cover' => 'linear-gradient(135deg,#ffedd5,#fed7aa)', 'pillBg' => '#fff7ed', 'pillFg' => '#c2410c'],
    ['cover' => 'linear-gradient(135deg,#ccfbf1,#99f6e4)', 'pillBg' => '#f0fdfa', 'pillFg' => '#0f766e'],
    ['cover' => 'linear-gradient(135deg,#dbeafe,#bfdbfe)', 'pillBg' => '#eff6ff', 'pillFg' => '#1d4ed8'],
    ['cover' => 'linear-gradient(135deg,#fce7f3,#fbcfe8)', 'pillBg' => '#fdf2f8', 'pillFg' => '#be185d'],
];
$themeFor = fn(int $id) => $cardThemes[$id % count($cardThemes)];

$sortLabels = ['name_asc' => 'Name (A - Z)', 'name_desc' => 'Name (Z - A)', 'department' => 'Department', 'designation' => 'Designation'];
?>
<div class="dir-hero d-flex align-items-center justify-content-between flex-wrap gap-3">
  <div>
    <div class="dir-eyebrow">Team</div>
    <h1>Employee Directory</h1>
    <p>Find and connect with amazing people at <?= e(setting('company_name', 'Adhook Media')) ?>.</p>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-people-fill"></i></span>
    <div class="dir-hero-script">People<br>Power<br>Progress</div>
  </div>
</div>

<form method="get" action="/directory" class="filterbar d-flex flex-wrap gap-2 align-items-center mb-3">
  <div class="flex-grow-1 field-ic" style="min-width:220px">
    <i class="bi bi-search"></i>
    <input type="text" name="q" class="form-control" placeholder="Search by name or employee ID" value="<?= e($filters['q']) ?>">
  </div>
  <div class="field-ic" style="min-width:160px">
    <i class="bi bi-building"></i>
    <select name="department_id" class="form-select">
      <option value="">Department</option>
      <?php foreach ($departments as $d): ?>
        <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field-ic" style="min-width:160px">
    <i class="bi bi-briefcase"></i>
    <select name="designation_id" class="form-select">
      <option value="">Designation</option>
      <?php foreach ($designations as $d): ?>
        <option value="<?= $d['id'] ?>" <?= ($filters['designation_id'] ?? null) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field-ic" style="min-width:170px">
    <i class="bi bi-calendar3"></i>
    <select name="birthday_month" class="form-select">
      <option value="">Birthday Month</option>
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= ($filters['birthday_month'] ?? null) == $m ? 'selected' : '' ?>><?= e(date('F', mktime(0, 0, 0, $m, 1))) ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <input type="hidden" name="sort" value="<?= e($filters['sort'] ?? 'name_asc') ?>">
  <button type="submit" class="btn btn-primary px-4"><i class="bi bi-funnel me-1"></i>Filter</button>
</form>

<?php if (empty($employees)): ?>
  <div class="empty-state"><i class="bi bi-people"></i><p class="mt-2">No employees match your search.</p></div>
<?php else: ?>
  <div class="dir-results-bar mb-3">
    <div class="result-count"><?= (int) $total ?> employee<?= $total == 1 ? '' : 's' ?></div>
    <div class="d-flex align-items-center gap-2">
      <label class="small text-muted mb-0" for="dirSort">Sort by</label>
      <select id="dirSort" class="form-select form-select-sm" style="width:auto" onchange="document.getElementById('dirSortForm').submit()">
        <?php foreach ($sortLabels as $val => $label): ?>
          <option value="<?= e($val) ?>" <?= ($filters['sort'] ?? 'name_asc') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
      <form id="dirSortForm" method="get" action="/directory" class="d-none">
        <?php foreach ($filters as $k => $v): if ($k === 'sort' || $v === null || $v === '') continue; ?>
          <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
        <?php endforeach; ?>
        <input type="hidden" name="sort" id="dirSortValue" value="<?= e($filters['sort'] ?? 'name_asc') ?>">
      </form>
      <div class="view-toggle">
        <button type="button" id="viewGrid" class="active" title="Grid view"><i class="bi bi-grid-3x3-gap-fill"></i></button>
        <button type="button" id="viewList" title="List view"><i class="bi bi-list-ul"></i></button>
      </div>
    </div>
  </div>

  <div class="row g-3" id="dirGrid">
    <?php foreach ($employees as $emp): $theme = $themeFor((int) $emp['id']); ?>
      <div class="col-md-6 col-lg-4 col-xl-3 dir-card-col">
        <div class="card employee-card h-100">
          <div class="ecard-cover" style="background:<?= $theme['cover'] ?>"></div>
          <div class="ecard-body text-center">
            <?php if (!empty($emp['profile_photo_path'])): ?>
              <img src="<?= e($emp['profile_photo_path']) ?>" class="avatar-lg mx-auto" alt="" onerror="this.outerHTML='<div class=&quot;avatar-lg mx-auto&quot;><?= e(mb_substr($emp['full_name'], 0, 1)) ?></div>'">
            <?php else: ?>
              <div class="avatar-lg mx-auto"><?= e(mb_substr($emp['full_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div class="fw-bold small mt-2"><?= e($emp['full_name']) ?></div>
            <div class="emp-role"><?= e($emp['designation_name'] ?? '-') ?></div>
            <?php if (!empty($emp['department_name'])): ?>
              <span class="emp-dept-pill" style="background:<?= $theme['pillBg'] ?>;color:<?= $theme['pillFg'] ?>"><i class="bi bi-building"></i><?= e($emp['department_name']) ?></span>
            <?php endif; ?>
            <div class="ecard-actions">
              <button type="button"
                data-bs-toggle="modal" data-bs-target="#employeeViewModal"
                data-name="<?= e($emp['full_name']) ?>"
                data-role="<?= e($emp['designation_name'] ?? '-') ?>"
                data-dept="<?= e($emp['department_name'] ?? '-') ?>"
                data-code="<?= e($emp['employee_code'] ?? '-') ?>"
                data-email="<?= e($emp['official_email'] ?? '') ?>"
                data-photo="<?= e($emp['profile_photo_path'] ?? '') ?>"
                data-initial="<?= e(mb_substr($emp['full_name'], 0, 1)) ?>">
                <i class="bi bi-person"></i>View Profile
              </button>
              <a href="mailto:<?= e($emp['official_email'] ?? '') ?>"><i class="bi bi-envelope"></i>Email</a>
            </div>
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

<div class="modal fade" id="employeeViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;border:none">
      <div class="modal-body text-center p-4">
        <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
        <div id="evmPhotoWrap"></div>
        <div class="fw-bold h5 mt-3 mb-0" id="evmName"></div>
        <div class="text-muted small" id="evmRole"></div>
        <span class="emp-dept-pill" style="background:var(--brand-tint);color:var(--brand)" id="evmDept"></span>
        <hr class="my-3">
        <div class="text-start small">
          <div class="d-flex justify-content-between py-1"><span class="text-muted">Employee ID</span><span class="fw-semibold" id="evmCode"></span></div>
          <div class="d-flex justify-content-between py-1"><span class="text-muted">Official Email</span><span class="fw-semibold" id="evmEmail"></span></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var grid = document.getElementById('dirGrid');
  var gridBtn = document.getElementById('viewGrid');
  var listBtn = document.getElementById('viewList');
  if (gridBtn && listBtn && grid) {
    gridBtn.addEventListener('click', function () {
      grid.classList.remove('dir-list-view');
      gridBtn.classList.add('active');
      listBtn.classList.remove('active');
    });
    listBtn.addEventListener('click', function () {
      grid.classList.add('dir-list-view');
      listBtn.classList.add('active');
      gridBtn.classList.remove('active');
    });
  }

  var sortSelect = document.getElementById('dirSort');
  if (sortSelect) {
    sortSelect.addEventListener('change', function () {
      document.getElementById('dirSortValue').value = sortSelect.value;
    });
  }

  var modal = document.getElementById('employeeViewModal');
  if (modal) {
    modal.addEventListener('show.bs.modal', function (ev) {
      var btn = ev.relatedTarget;
      if (!btn) return;
      document.getElementById('evmName').textContent = btn.dataset.name || '';
      document.getElementById('evmRole').textContent = btn.dataset.role || '';
      document.getElementById('evmDept').textContent = btn.dataset.dept || '';
      document.getElementById('evmCode').textContent = btn.dataset.code || '-';
      document.getElementById('evmEmail').textContent = btn.dataset.email || '-';
      var photo = btn.dataset.photo;
      var wrap = document.getElementById('evmPhotoWrap');
      wrap.innerHTML = photo
        ? '<img src="' + photo + '" class="dir-modal-photo mx-auto" alt="" onerror="this.outerHTML=\'<div class=&quot;dir-modal-photo mx-auto&quot;>' + (btn.dataset.initial || '?') + '</div>\'">'
        : '<div class="dir-modal-photo mx-auto">' + (btn.dataset.initial || '?') + '</div>';
    });
  }
})();
</script>

<style>
  .dir-list-view { display: block !important; }
  .dir-list-view .dir-card-col { width: 100%; max-width: 100%; margin-bottom: .75rem; }
  .dir-list-view .employee-card { display: flex; flex-direction: row; align-items: center; }
  .dir-list-view .ecard-cover { display: none; }
  .dir-list-view .ecard-body { text-align: left !important; display: flex; align-items: center; gap: 1rem; padding: .85rem 1.1rem; width: 100%; }
  .dir-list-view .avatar-lg { margin: 0 !important; width: 52px; height: 52px; font-size: 1rem; flex-shrink: 0; }
  .dir-list-view .emp-dept-pill { margin-top: 0; }
  .dir-list-view .ecard-actions { border-top: none; border-left: 1px solid var(--border-soft); margin-top: 0; margin-left: auto; flex-shrink: 0; width: auto; }
  .dir-list-view .ecard-actions a, .dir-list-view .ecard-actions button { padding: .3rem .9rem; flex: none; }
</style>
