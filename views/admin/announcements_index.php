<h2 class="h5 fw-bold mb-3">Announcements</h2>

<div class="row g-3">
  <div class="col-lg-8">
    <?php if (empty($announcements)): ?>
      <div class="empty-state"><i class="bi bi-megaphone"></i><p class="mt-2">No announcements yet.</p></div>
    <?php endif; ?>
    <?php foreach ($announcements as $a): ?>
      <div class="card mb-2">
        <div class="card-body d-flex justify-content-between align-items-start">
          <div>
            <span class="badge text-bg-light text-capitalize mb-1"><?= e($a['category']) ?></span>
            <h3 class="h6 fw-bold mb-1"><?= e($a['title']) ?></h3>
            <p class="small text-muted mb-0"><?= e(mb_substr(strip_tags($a['body']), 0, 150)) ?></p>
            <?php if ($a['event_date']): ?><div class="text-muted small mt-1"><i class="bi bi-calendar3 me-1"></i><?= format_date($a['event_date']) ?></div><?php endif; ?>
          </div>
          <form method="post" action="/admin/announcements/<?= $a['id'] ?>/delete" onsubmit="return confirm('Delete this announcement?');">
            <?= $csrfField ?><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3">New Announcement</h3>
        <form method="post" action="/admin/announcements">
          <?= $csrfField ?>
          <div class="mb-2">
            <label class="form-label small">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">Body</label>
            <textarea name="body" class="form-control" rows="4" required></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label small">Category</label>
            <select name="category" class="form-select">
              <option value="general">General</option>
              <option value="holiday">Holiday</option>
              <option value="notice">Notice</option>
              <option value="event">Event</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small">Event Date (optional)</label>
            <input type="date" name="event_date" class="form-control">
          </div>
          <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" name="notify_push" value="1" id="notifyPush" checked>
            <label class="form-check-label small" for="notifyPush">Send browser push notification</label>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="notify_email" value="1" id="notifyEmail">
            <label class="form-check-label small" for="notifyEmail">Send email notification</label>
          </div>
          <button class="btn btn-primary w-100">Publish</button>
        </form>
      </div>
    </div>
  </div>
</div>
