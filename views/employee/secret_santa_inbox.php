<div class="admin-breadcrumb"><a href="/secret-santa">Secret Santa</a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Inbox</span></div>

<div class="page-hero-title mb-3">
  <span class="bar"></span>
  <div>
    <h1 class="h4 fw-bold mb-1">Secret Santa Inbox</h1>
    <p class="text-muted small mb-0">Anonymous messages from your Secret Santa. Their identity is never revealed.</p>
  </div>
</div>

<?php if (empty($messages)): ?>
  <div class="card">
    <div class="empty-state-lg">
      <i class="bi bi-envelope mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
      <p class="text-muted mb-0">No messages yet.</p>
    </div>
  </div>
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
