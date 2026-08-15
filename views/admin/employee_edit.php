<h2 class="h5 fw-bold mb-3">Edit <?= e($user['full_name']) ?></h2>

<form method="post" action="/admin/employees/<?= $user['id'] ?>/edit">
  <?= $csrfField ?>
  <div class="card mb-3">
    <div class="card-body row g-3">
      <div class="col-md-6">
        <label class="form-label small">Full Name</label>
        <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Employee ID</label>
        <input type="text" name="employee_code" class="form-control" value="<?= e($profile['employee_code'] ?? '') ?>">
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
      <div class="col-md-6">
        <label class="form-label small">Personal Email</label>
        <input type="email" name="personal_email" class="form-control" value="<?= e($profile['personal_email'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Address</label>
        <input type="text" name="current_address" class="form-control" value="<?= e($profile['current_address'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Emergency Contact Name</label>
        <input type="text" name="emergency_contact_name" class="form-control" value="<?= e($profile['emergency_contact_name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small">Emergency Contact Number</label>
        <input type="text" name="emergency_contact_number" class="form-control" value="<?= e($profile['emergency_contact_number'] ?? '') ?>">
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body row g-3">
      <div class="col-md-4">
        <label class="form-label small">Date of Joining</label>
        <input type="date" name="date_of_joining" class="form-control" value="<?= e($profile['date_of_joining'] ?? '') ?>">
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
      <div class="col-md-4">
        <label class="form-label small">Work Location</label>
        <input type="text" name="work_location" class="form-control" value="<?= e($profile['work_location'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small">Employment Status</label>
        <select name="employment_status" class="form-select">
          <?php foreach (['active' => 'Active', 'on_leave' => 'On Leave', 'resigned' => 'Resigned', 'terminated' => 'Terminated'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($profile['employment_status'] ?? 'active') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="text-end">
    <button class="btn btn-primary px-4">Save Changes</button>
  </div>
</form>
