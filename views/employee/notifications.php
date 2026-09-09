<div class="page-hero">
  <div class="page-hero-title">
    <span class="bar"></span>
    <div>
      <h1>Notifications</h1>
      <p>Everything the portal has sent your way.</p>
    </div>
  </div>
  <div class="dir-hero-deco">
    <span class="dir-hero-ic"><i class="bi bi-bell-fill"></i></span>
    <div class="dir-hero-script">Stay<br>In The<br>Loop</div>
  </div>
</div>

<?php if (empty($notifications)): ?>
  <div class="card">
    <div class="empty-state-lg">
      <i class="bi bi-bell-slash mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
      <p class="text-muted mb-0">No notifications yet.</p>
    </div>
  </div>
<?php else: ?>
  <div class="card">
    <?php foreach ($notifications as $n): ?>
      <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold"><?= e($n['title']) ?></span>
          <span class="text-muted" style="font-size:.72rem"><?= format_date($n['created_at'], 'd M Y, h:i A') ?></span>
        </div>
        <?php if ($n['body']): ?><div class="text-muted mt-1"><?= e($n['body']) ?></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
