<h2 class="h5 fw-bold mb-3">Audit Logs</h2>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0 small">
      <thead><tr><th>When</th><th>Actor</th><th>Subject</th><th>Action</th><th>Field</th><th>Old</th><th>New</th><th>IP</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td class="text-nowrap"><?= format_date($log['created_at'], 'd M Y, h:i A') ?></td>
            <td><?= e($log['actor_name'] ?? 'System') ?></td>
            <td><?= e($log['subject_name'] ?? '-') ?></td>
            <td><code><?= e($log['action']) ?></code></td>
            <td><?= e($log['field_name'] ?? '-') ?></td>
            <td class="text-truncate" style="max-width:120px"><?= e($log['old_value'] ?? '-') ?></td>
            <td class="text-truncate" style="max-width:120px"><?= e($log['new_value'] ?? '-') ?></td>
            <td><?= e($log['ip_address'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
