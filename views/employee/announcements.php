<h2 class="h5 fw-bold mb-3">Announcements</h2>

<?php if (empty($announcements)): ?>
  <div class="empty-state"><i class="bi bi-megaphone"></i><p class="mt-2">No announcements yet.</p></div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($announcements as $a): ?>
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge text-bg-light text-capitalize"><?= e($a['category']) ?></span>
              <?php if ($a['event_date']): ?><span class="text-muted small"><?= format_date($a['event_date']) ?></span><?php endif; ?>
            </div>
            <h3 class="h6 fw-bold"><?= e($a['title']) ?></h3>
            <p class="small text-muted mb-0"><?= nl2br(e($a['body'])) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
