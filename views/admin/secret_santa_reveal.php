<h2 class="h5 fw-bold mb-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Emergency Reveal</h2>

<div class="alert alert-danger">
  <strong>Warning:</strong> Revealing the full Secret Santa mapping for "<?= e($event['name']) ?>" breaks the anonymity that participants were promised.
  Only do this in a genuine emergency (e.g. resolving a serious dispute). This action is fully logged in the audit trail, including your identity and the time of reveal.
</div>

<form method="post" action="/admin/secret-santa/<?= $event['id'] ?>/reveal" class="card">
  <div class="card-body">
    <?= $csrfField ?>
    <label class="form-label small">Confirm your password to continue</label>
    <input type="password" name="password" class="form-control mb-3" required autofocus>
    <div class="d-flex justify-content-end gap-2">
      <a href="/admin/secret-santa" class="btn btn-outline-secondary">Cancel</a>
      <button class="btn btn-danger">Reveal Full Mapping</button>
    </div>
  </div>
</form>
