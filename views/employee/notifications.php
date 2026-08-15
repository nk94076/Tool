<h2 class="h5 fw-bold mb-3">Notifications</h2>

<?php if (empty($notifications)): ?>
  <div class="empty-state"><i class="bi bi-bell-slash"></i><p class="mt-2">No notifications yet.</p></div>
<?php else: ?>
  <div class="card">
    <div class="list-group list-group-flush">
      <?php foreach ($notifications as $n): ?>
        <div class="list-group-item <?= $n['is_read'] ? '' : 'bg-light' ?>">
          <div class="d-flex justify-content-between">
            <span class="fw-semibold small"><?= e($n['title']) ?></span>
            <span class="text-muted" style="font-size:.72rem"><?= format_date($n['created_at'], 'd M Y, h:i A') ?></span>
          </div>
          <?php if ($n['body']): ?><div class="text-muted small mt-1"><?= e($n['body']) ?></div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
