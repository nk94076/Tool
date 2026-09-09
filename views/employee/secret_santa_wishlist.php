<div class="admin-breadcrumb"><a href="/secret-santa">Secret Santa</a> <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i> <span class="current">Recipient's Wishlist</span></div>

<div class="page-hero-title mb-3">
  <span class="bar"></span>
  <div>
    <h1 class="h4 fw-bold mb-1">Your Recipient's Wishlist</h1>
    <p class="text-muted small mb-0">Recipient: <strong><?= e($recipient['recipient_name']) ?></strong> &middot; <?= e($recipient['designation_name'] ?? '') ?>, <?= e($recipient['department_name'] ?? '') ?></p>
  </div>
</div>

<div class="card">
  <?php if (!$preferences): ?>
    <div class="empty-state-lg">
      <i class="bi bi-gift mb-2" style="font-size:2rem;color:var(--brand-light)"></i>
      <p class="text-muted mb-0">Your recipient hasn't shared any preferences yet.</p>
    </div>
  <?php else: ?>
    <div class="card-body-x p-3">
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-hand-thumbs-up"></i></span>
        <div><div class="profile-field-label">Things They Like</div><div class="profile-field-value"><?= e($preferences['things_i_like'] ?? '-') ?></div></div>
      </div>
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-hand-thumbs-down"></i></span>
        <div><div class="profile-field-label">Things They Dislike</div><div class="profile-field-value"><?= e($preferences['things_i_dislike'] ?? '-') ?></div></div>
      </div>
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-tags"></i></span>
        <div><div class="profile-field-label">Favourite Categories</div><div class="profile-field-value"><?= e($preferences['favourite_categories'] ?? '-') ?></div></div>
      </div>
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-palette"></i></span>
        <div><div class="profile-field-label">Favourite Colours</div><div class="profile-field-value"><?= e($preferences['favourite_colours'] ?? '-') ?></div></div>
      </div>
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-bag"></i></span>
        <div><div class="profile-field-label">Preferred Brands</div><div class="profile-field-value"><?= e($preferences['preferred_brands'] ?? '-') ?></div></div>
      </div>
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-list-check"></i></span>
        <div><div class="profile-field-label">Wishlist</div><div class="profile-field-value"><?= e($preferences['wishlist'] ?? '-') ?></div></div>
      </div>
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-coin"></i></span>
        <div><div class="profile-field-label">Budget Preference</div><div class="profile-field-value"><?= e($preferences['budget_preference'] ?? '-') ?></div></div>
      </div>
      <div class="profile-field">
        <span class="profile-field-ic"><i class="bi bi-chat-text"></i></span>
        <div><div class="profile-field-label">Additional Note</div><div class="profile-field-value"><?= e($preferences['additional_note'] ?? '-') ?></div></div>
      </div>
    </div>
  <?php endif; ?>
</div>

<a href="/secret-santa" class="btn btn-outline-secondary btn-sm mt-3"><i class="bi bi-arrow-left me-1"></i>Back</a>
