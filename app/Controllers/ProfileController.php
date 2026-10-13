<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\EmployeeProfile;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SecretSantaPreference;
use App\Services\AuditService;
use App\Services\FileUploadService;

final class ProfileController extends Controller
{
    /**
     * Whether the full profile form (every field) is open for editing:
     * not yet locked, or the Super Admin has temporarily unlocked it.
     */
    private function isFullyEditable(array $user, array $profile): bool
    {
        $locked = (bool) $profile['is_locked'];
        $unlocked = (bool) $user['profile_unlocked'];
        return !$locked || $unlocked;
    }

    /**
     * Resubmitting (re-locking) only makes sense from a fully-editable
     * profile — otherwise this is re-checked server-side, never trusting a
     * client-side "editable" flag.
     */
    private function assertFullyEditable(array $user, array $profile): void
    {
        if (!$this->isFullyEditable($user, $profile)) {
            set_flash('error', 'Your profile has been submitted and locked. Only the Super Admin can modify your information.');
            $this->redirect('/profile');
        }
    }

    /**
     * Even on a locked profile, some corrections shouldn't require a full
     * Super Admin unlock: the profile photo, the designation/role (which
     * legitimately changes on a promotion), and any field still blank
     * (so an incomplete profile can be finished). Everything already on
     * file otherwise stays as submitted until unlocked.
     */
    private function restrictToAlwaysEditable(array $data, array $profile): array
    {
        foreach ($data as $field => $value) {
            if ($field === 'designation_id') {
                continue;
            }
            $current = $profile[$field] ?? null;
            if ($current !== null && $current !== '') {
                $data[$field] = $current;
            }
        }
        return $data;
    }

    public function edit(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $profile = (new EmployeeProfile())->findByUser((int) $user['id']);

        $this->view('employee/profile_edit', [
            'title' => 'Complete Your Profile',
            'profile' => $profile,
            'departments' => (new Department())->activeList(),
            'designations' => (new Designation())->activeList(),
            'preferences' => (new SecretSantaPreference())->findForUser((int) $user['id']),
            'locked' => !$this->isFullyEditable($user, $profile),
        ]);
    }

    public function update(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $user = Auth::user();
        $profileModel = new EmployeeProfile();
        $profile = $profileModel->findByUser((int) $user['id']);
        $fullyEditable = $this->isFullyEditable($user, $profile);

        $data = $this->collectProfileInput();
        if (!$fullyEditable) {
            $data = $this->restrictToAlwaysEditable($data, $profile);
        }

        $errors = $this->validateProfile($data);
        if (!empty($errors)) {
            Session::set('_old', $data);
            set_flash('error', implode(' ', $errors));
            $this->redirect('/profile/edit');
        }

        if (!empty($_FILES['profile_photo']['name'])) {
            $path = FileUploadService::handleProfilePhoto($_FILES['profile_photo'], (int) $user['id']);
            if ($path === null) {
                set_flash('error', 'Profile photo upload failed. Use JPG/PNG/WEBP under the size limit.');
                $this->redirect('/profile/edit');
            }
            $data['profile_photo_path'] = $path;
        }

        if (!$fullyEditable) {
            foreach ($data as $field => $newValue) {
                $oldValue = $profile[$field] ?? null;
                if ((string) $oldValue !== (string) $newValue) {
                    AuditService::log('employee_profile.field_changed', (int) $user['id'], $field, $oldValue, $newValue);
                }
            }
        }

        $profileModel->update((int) $profile['id'], $data);

        if ($fullyEditable) {
            $userModel = new User();
            $userModel->update((int) $user['id'], ['profile_status' => 'in_progress']);
        }

        $ssPrefs = $this->collectSecretSantaPreferences();
        if ($ssPrefs !== null) {
            (new SecretSantaPreference())->upsert((int) $user['id'], $ssPrefs);
        }

        if ($fullyEditable) {
            set_flash('success', 'Profile saved. Review and submit when ready.');
            $this->redirect('/profile/preview');
        }

        set_flash('success', 'Saved. Your photo, role and any details you just filled in are updated — the rest of your locked profile is unchanged.');
        $this->redirect('/profile');
    }

    private function collectProfileInput(): array
    {
        $fields = [
            'date_of_birth', 'gender', 'mobile_number', 'personal_email', 'current_address',
            'emergency_contact_name', 'emergency_contact_number', 'employee_code', 'date_of_joining',
            'department_id', 'designation_id', 'reporting_manager_id', 'employment_type', 'work_location',
        ];
        $data = [];
        foreach ($fields as $field) {
            $value = trim((string) $this->input($field, ''));
            $data[$field] = $value === '' ? null : $value;
        }
        foreach (['department_id', 'designation_id', 'reporting_manager_id'] as $intField) {
            if ($data[$intField] !== null) {
                $data[$intField] = (int) $data[$intField];
            }
        }
        return $data;
    }

    private function validateProfile(array $data): array
    {
        $errors = [];
        if ($data['date_of_birth'] && strtotime($data['date_of_birth']) > time()) {
            $errors[] = 'Date of birth cannot be in the future.';
        }
        if ($data['date_of_joining'] && strtotime($data['date_of_joining']) > strtotime('+1 day')) {
            $errors[] = 'Date of joining cannot be in the future.';
        }
        if ($data['personal_email'] && !filter_var($data['personal_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Personal email is invalid.';
        }
        if ($data['mobile_number'] && !preg_match('/^[0-9+\-\s]{7,20}$/', $data['mobile_number'])) {
            $errors[] = 'Mobile number is invalid.';
        }
        return $errors;
    }

    private function collectSecretSantaPreferences(): ?array
    {
        if ($this->input('secret_santa_participate', null) === null) {
            return null;
        }
        return [
            'things_i_like' => trim((string) $this->input('things_i_like', '')) ?: null,
            'things_i_dislike' => trim((string) $this->input('things_i_dislike', '')) ?: null,
            'favourite_categories' => trim((string) $this->input('favourite_categories', '')) ?: null,
            'favourite_colours' => trim((string) $this->input('favourite_colours', '')) ?: null,
            'preferred_brands' => trim((string) $this->input('preferred_brands', '')) ?: null,
            'wishlist' => trim((string) $this->input('wishlist', '')) ?: null,
            'budget_preference' => trim((string) $this->input('budget_preference', '')) ?: null,
            'additional_note' => trim((string) $this->input('additional_note', '')) ?: null,
        ];
    }

    public function preview(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $profile = (new EmployeeProfile())->withUser((int) $user['id']);

        $this->view('employee/profile_preview', [
            'title' => 'Preview Profile',
            'profile' => $profile,
            'user' => $user,
        ]);
    }

    public function submit(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $user = Auth::user();
        $profileModel = new EmployeeProfile();
        $profile = $profileModel->findByUser((int) $user['id']);
        $this->assertFullyEditable($user, $profile);

        $profileModel->update((int) $profile['id'], [
            'is_locked' => 1,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        $wasUnlocked = (bool) $user['profile_unlocked'];
        (new User())->update((int) $user['id'], [
            'profile_status' => 'submitted_locked',
            'profile_unlocked' => 0,
        ]);

        AuditService::log($wasUnlocked ? 'profile.resubmitted' : 'profile.submitted', (int) $user['id']);

        set_flash('success', 'Your profile has been submitted and locked. Only the Super Admin can modify your information.');
        $this->redirect('/profile');
    }

    public function show(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $profile = (new EmployeeProfile())->withUser((int) $user['id']);

        $this->view('employee/profile_show', [
            'title' => 'My Profile',
            'profile' => $profile,
            'user' => $user,
        ]);
    }
}
