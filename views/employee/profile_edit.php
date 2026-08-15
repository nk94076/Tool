<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 fw-bold mb-0">Complete Your Profile</h2>
  <a href="/profile/preview" class="btn btn-outline-primary btn-sm">Preview</a>
</div>

<form method="post" action="/profile/edit" enctype="multipart/form-data" novalidate>
  <?= $csrfField ?>

  <div class="card mb-3">
    <div class="card-body">
      <h3 class="h6 fw-bold mb-3">Personal Information</h3>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small">Profile Photo</label>
          <input type="file" name="profile_photo" class="form-control" accept="image/jpeg,image/png,image/webp">
          <?php if (!empty($profile['profile_photo_path'])): ?>
            <img src="<?= e($profile['profile_photo_path']) ?>" class="rounded mt-2" style="width:64px;height:64px;object-fit:cover">
          <?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Date of Birth</label>
          <input type="date" name="date_of_birth" class="form-control" value="<?= e($profile['date_of_birth'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Gender</label>
          <select name="gender" class="form-select">
            <option value="">Select</option>
            <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($profile['gender'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Mobile Number</label>
          <input type="text" name="mobile_number" class="form-control" value="<?= e($profile['mobile_number'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Personal Email (optional)</label>
          <input type="email" name="personal_email" class="form-control" value="<?= e($profile['personal_email'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Emergency Contact Name</label>
          <input type="text" name="emergency_contact_name" class="form-control" value="<?= e($profile['emergency_contact_name'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Emergency Contact Number</label>
          <input type="text" name="emergency_contact_number" class="form-control" value="<?= e($profile['emergency_contact_number'] ?? '') ?>">
        </div>
        <div class="col-md-8">
          <label class="form-label small">Current Address</label>
          <textarea name="current_address" class="form-control" rows="2"><?= e($profile['current_address'] ?? '') ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h3 class="h6 fw-bold mb-3">Employment Information</h3>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label small">Employee ID</label>
          <input type="text" name="employee_code" class="form-control" value="<?= e($profile['employee_code'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Date of Joining</label>
          <input type="date" name="date_of_joining" class="form-control" value="<?= e($profile['date_of_joining'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Work Location</label>
          <input type="text" name="work_location" class="form-control" value="<?= e($profile['work_location'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label small">Department</label>
          <select name="department_id" class="form-select">
            <option value="">Select</option>
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d['id'] ?>" <?= (int) ($profile['department_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Designation</label>
          <select name="designation_id" class="form-select">
            <option value="">Select</option>
            <?php foreach ($designations as $d): ?>
              <option value="<?= $d['id'] ?>" <?= (int) ($profile['designation_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Employment Type</label>
          <select name="employment_type" class="form-select">
            <option value="">Select</option>
            <?php foreach (['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'intern' => 'Intern'] as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($profile['employment_type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h3 class="h6 fw-bold mb-1"><i class="bi bi-gift me-2 text-primary"></i>Secret Santa Preferences</h3>
      <p class="text-muted small">Optional. Only visible to whoever gets assigned as your Secret Santa.</p>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" name="secret_santa_participate" value="1" id="ssParticipate" <?= !empty($preferences) ? 'checked' : '' ?>>
        <label class="form-check-label small" for="ssParticipate">Save these Secret Santa preferences</label>
      </div>
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
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end">
    <button type="submit" class="btn btn-primary px-4">Save &amp; Continue</button>
  </div>
</form>
