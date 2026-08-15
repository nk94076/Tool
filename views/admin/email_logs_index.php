<h2 class="h5 fw-bold mb-3">Email Logs</h2>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0 small">
      <thead><tr><th>Recipient</th><th>Subject</th><th>Template</th><th>Status</th><th>Error</th><th>Sent</th></tr></thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td><?= e($log['recipient_email']) ?></td>
            <td><?= e($log['subject']) ?></td>
            <td><?= e($log['template_slug'] ?? '-') ?></td>
            <td><span class="badge <?= $log['status'] === 'sent' ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= e($log['status']) ?></span></td>
            <td class="text-truncate" style="max-width:200px"><?= e($log['error_message'] ?? '-') ?></td>
            <td class="text-nowrap"><?= $log['sent_at'] ? format_date($log['sent_at'], 'd M, h:i A') : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
