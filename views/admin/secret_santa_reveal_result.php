<div class="admin-breadcrumb"><a href="/admin/secret-santa">Secret Santa Events</a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Full Mapping</span></div>

<h2 class="h5 fw-bold mb-3 text-danger"><i class="bi bi-eye me-2"></i>Full Mapping - <?= e($event['name']) ?></h2>
<p class="text-muted small">This reveal has been recorded in the audit log.</p>

<div class="card">
  <div class="table-responsive">
    <table class="table admin-table align-middle mb-0">
      <thead><tr><th>Secret Santa</th><th>Gives a Gift To</th></tr></thead>
      <tbody>
        <?php foreach ($mapping as $row): ?>
          <tr><td><?= e($row['santa_name']) ?></td><td><?= e($row['recipient_name']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<a href="/admin/secret-santa" class="btn btn-outline-secondary btn-sm mt-3">Back</a>
