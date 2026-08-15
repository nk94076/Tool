<h2 class="h5 fw-bold mb-3">Secret Santa Inbox</h2>
<p class="text-muted small mb-3">Anonymous messages from your Secret Santa. Their identity is never revealed.</p>

<?php if (empty($messages)): ?>
  <div class="empty-state"><i class="bi bi-envelope"></i><p class="mt-2">No messages yet.</p></div>
<?php else: ?>
  <div class="card">
    <div class="list-group list-group-flush">
      <?php foreach ($messages as $m): ?>
        <div class="list-group-item small">
          <?= e($m['message']) ?>
          <div class="text-muted" style="font-size:.7rem"><?= format_date($m['created_at'], 'd M, h:i A') ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<a href="/secret-santa" class="btn btn-outline-secondary btn-sm mt-3"><i class="bi bi-arrow-left me-1"></i>Back</a>
