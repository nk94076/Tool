<h2 class="h5 fw-bold mb-3"><i class="bi bi-gift-fill text-primary me-2"></i>Secret Santa</h2>

<?php if (!$event): ?>
  <div class="empty-state"><i class="bi bi-gift"></i><p class="mt-2">There is no active Secret Santa event right now. Check back soon!</p></div>
<?php else: ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div>
          <h3 class="h6 fw-bold mb-1"><?= e($event['name']) ?> (<?= e($event['event_year']) ?>)</h3>
          <p class="text-muted small mb-0">
            Registration closes <?= format_date($event['registration_deadline']) ?> &middot;
            Gift exchange on <?= format_date($event['gift_exchange_date']) ?> &middot;
            Budget: ₹<?= number_format((float) $event['min_budget']) ?> - ₹<?= number_format((float) $event['max_budget']) ?>
          </p>
        </div>
        <div>
          <?php if ($event['status'] === 'active'): ?>
            <?php if ($participant && $participant['opted_in']): ?>
              <form method="post" action="/secret-santa/opt-out" class="d-inline">
                <?= $csrfField ?>
                <button class="btn btn-outline-danger btn-sm">Opt Out</button>
              </form>
            <?php else: ?>
              <form method="post" action="/secret-santa/opt-in" class="d-inline">
                <?= $csrfField ?>
                <button class="btn btn-primary btn-sm">Join Secret Santa</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <?php if ($event['rules']): ?><p class="small text-muted mt-2 mb-0"><?= nl2br(e($event['rules'])) ?></p><?php endif; ?>
    </div>
  </div>

  <?php if ($myRecipient): ?>
    <div class="card wishlist-box border-0 mb-3">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-2">Your Secret Santa recipient is <span class="text-primary"><?= e($myRecipient['recipient_name']) ?></span></h3>
        <p class="text-muted small">You can view their wishlist and send them an anonymous message. They will never know it's you!</p>
        <a href="/secret-santa/wishlist" class="btn btn-sm btn-outline-primary me-2">View Their Wishlist</a>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h3 class="h6 fw-bold mb-3">Send an Anonymous Message</h3>
        <form method="post" action="/secret-santa/message">
          <?= $csrfField ?>
          <textarea name="message" class="form-control mb-2" rows="2" maxlength="1000" placeholder="e.g. What kind of books do you like?" required></textarea>
          <button class="btn btn-primary btn-sm">Send Anonymously</button>
        </form>
        <?php if (!empty($messages)): ?>
          <hr>
          <div class="small text-muted mb-2">Messages you've sent:</div>
          <?php foreach ($messages as $m): ?>
            <div class="border-bottom py-2 small"><?= e($m['message']) ?><div class="text-muted" style="font-size:.7rem"><?= format_date($m['created_at'], 'd M, h:i A') ?></div></div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  <?php elseif ($event['status'] === 'matched' && $participant && $participant['opted_in']): ?>
    <div class="alert alert-secondary small">Matching has been generated. Refresh shortly if your recipient isn't showing yet.</div>
  <?php endif; ?>

  <div class="text-end">
    <a href="/secret-santa/inbox" class="small"><i class="bi bi-envelope me-1"></i>Check your Secret Santa inbox</a>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <h3 class="h6 fw-bold mb-3">My Gift Preferences</h3>
      <p class="text-muted small">These are only shown to whoever is assigned as your Secret Santa. Update anytime.</p>
      <form method="post" action="/secret-santa/preferences">
        <?= $csrfField ?>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small">Things I Like</label>
            <textarea name="things_i_like" class="form-control" rows="2"><?= e($preferences['things_i_like'] ?? '') ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label small">Things I Don't Like</label>
            <textarea name="things_i_dislike" class="form-control" rows="2"><?= e($preferences['things_i_dislike'] ?? '') ?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Favourite Categories</label>
            <input type="text" name="favourite_categories" class="form-control" value="<?= e($preferences['favourite_categories'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small">Favourite Colours</label>
            <input type="text" name="favourite_colours" class="form-control" value="<?= e($preferences['favourite_colours'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small">Preferred Brands</label>
            <input type="text" name="preferred_brands" class="form-control" value="<?= e($preferences['preferred_brands'] ?? '') ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label small">Wishlist</label>
            <textarea name="wishlist" class="form-control" rows="2"><?= e($preferences['wishlist'] ?? '') ?></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label small">Budget Preference</label>
            <input type="text" name="budget_preference" class="form-control" value="<?= e($preferences['budget_preference'] ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label small">Additional Note</label>
            <textarea name="additional_note" class="form-control" rows="2"><?= e($preferences['additional_note'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="text-end mt-3">
          <button class="btn btn-primary btn-sm px-3">Save Preferences</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>
