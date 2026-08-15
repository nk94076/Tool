<h2 class="h5 fw-bold mb-1">Your Recipient's Wishlist</h2>
<p class="text-muted small mb-3">Recipient: <strong><?= e($recipient['recipient_name']) ?></strong> &middot; <?= e($recipient['designation_name'] ?? '') ?>, <?= e($recipient['department_name'] ?? '') ?></p>

<div class="card">
  <div class="card-body">
    <?php if (!$preferences): ?>
      <div class="empty-state"><i class="bi bi-gift"></i><p class="mt-2">Your recipient hasn't shared any preferences yet.</p></div>
    <?php else: ?>
      <dl class="row small mb-0">
        <dt class="col-sm-4 text-muted">Things They Like</dt><dd class="col-sm-8"><?= e($preferences['things_i_like'] ?? '-') ?></dd>
        <dt class="col-sm-4 text-muted">Things They Dislike</dt><dd class="col-sm-8"><?= e($preferences['things_i_dislike'] ?? '-') ?></dd>
        <dt class="col-sm-4 text-muted">Favourite Categories</dt><dd class="col-sm-8"><?= e($preferences['favourite_categories'] ?? '-') ?></dd>
        <dt class="col-sm-4 text-muted">Favourite Colours</dt><dd class="col-sm-8"><?= e($preferences['favourite_colours'] ?? '-') ?></dd>
        <dt class="col-sm-4 text-muted">Preferred Brands</dt><dd class="col-sm-8"><?= e($preferences['preferred_brands'] ?? '-') ?></dd>
        <dt class="col-sm-4 text-muted">Wishlist</dt><dd class="col-sm-8"><?= e($preferences['wishlist'] ?? '-') ?></dd>
        <dt class="col-sm-4 text-muted">Budget Preference</dt><dd class="col-sm-8"><?= e($preferences['budget_preference'] ?? '-') ?></dd>
        <dt class="col-sm-4 text-muted">Additional Note</dt><dd class="col-sm-8"><?= e($preferences['additional_note'] ?? '-') ?></dd>
      </dl>
    <?php endif; ?>
  </div>
</div>

<a href="/secret-santa" class="btn btn-outline-secondary btn-sm mt-3"><i class="bi bi-arrow-left me-1"></i>Back</a>
