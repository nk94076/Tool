<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\EmployeeProfile;
use App\Models\Department;
use App\Models\Designation;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\MailService;
use App\Models\EmailTemplate;

final class EmployeeController extends Controller
{
    /**
     * Employee directory: every logged-in user can browse, but only
     * safe/public fields (photo, name, designation, department) are
     * returned — never address, emergency contact, DOB, etc.
     */
    public function directory(): void
    {
        $this->requireLogin();
        $filters = [
            'q' => trim((string) $this->input('q', '')),
            'department_id' => (int) $this->input('department_id', 0) ?: null,
            'designation_id' => (int) $this->input('designation_id', 0) ?: null,
            'joining_year' => (int) $this->input('joining_year', 0) ?: null,
            'birthday_month' => (int) $this->input('birthday_month', 0) ?: null,
            'status' => $this->input('status', '') ?: null,
        ];
        $page = max(1, (int) $this->input('page', 1));

        $result = (new User())->search($filters, $page, 24);

        $this->view('employee/directory', [
            'title' => 'Employee Directory',
            'employees' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => 24,
            'filters' => $filters,
            'departments' => (new Department())->activeList(),
            'designations' => (new Designation())->activeList(),
        ]);
    }

    /**
     * Admin-facing: list of all employees for management (fuller detail than directory).
     */
    public function adminIndex(): void
    {
        $this->requireLogin();
        $filters = [
            'q' => trim((string) $this->input('q', '')),
            'department_id' => (int) $this->input('department_id', 0) ?: null,
            'status' => $this->input('status', '') ?: null,
        ];
        $page = max(1, (int) $this->input('page', 1));
        $result = (new User())->search($filters, $page, 25);

        $this->view('admin/employees_index', [
            'title' => 'Manage Employees',
            'employees' => $result['rows'],
            'total' => $result['total'],
            'page' => $page,
            'perPage' => 25,
            'filters' => $filters,
            'departments' => (new Department())->activeList(),
        ]);
    }

    public function adminShow(array $params): void
    {
        $this->requireLogin();
        $userId = (int) $params['id'];
        $user = (new User())->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }
        $profile = (new EmployeeProfile())->withUser($userId);

        $this->view('admin/employee_show', [
            'title' => $user['full_name'],
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function adminEdit(array $params): void
    {
        $this->requireLogin();
        $userId = (int) $params['id'];
        $user = (new User())->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }
        $this->guardLockedEditing($user);

        $profile = (new EmployeeProfile())->findByUser($userId);
        $this->view('admin/employee_edit', [
            'title' => 'Edit ' . $user['full_name'],
            'user' => $user,
            'profile' => $profile,
            'departments' => (new Department())->activeList(),
            'designations' => (new Designation())->activeList(),
        ]);
    }

    /**
     * A locked profile can only be edited by the Super Admin. Admin/Manager
     * are blocked even if they hold employees.edit, unless the Super Admin
     * has unlocked that specific profile.
     */
    private function guardLockedEditing(array $user): void
    {
        if ((int) $user['is_super_admin'] === 1 && Auth::id() !== (int) $user['id']) {
            set_flash('error', 'The Super Admin account cannot be modified.');
            $this->redirect('/admin/employees');
        }
        $profile = (new EmployeeProfile())->findByUser((int) $user['id']);
        $locked = $profile && (bool) $profile['is_locked'];
        $unlocked = (bool) $user['profile_unlocked'];
        if ($locked && !$unlocked && !Auth::isSuperAdmin()) {
            set_flash('error', 'This profile is locked. Only the Super Admin can edit it (or must unlock it first).');
            $this->redirect('/admin/employees/' . $user['id']);
        }
    }

    public function adminUpdate(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $userModel = new User();
        $user = $userModel->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }
        $this->guardLockedEditing($user);

        $profileModel = new EmployeeProfile();
        $profile = $profileModel->findByUser($userId);

        $fields = [
            'date_of_birth', 'gender', 'mobile_number', 'personal_email', 'current_address',
            'emergency_contact_name', 'emergency_contact_number', 'employee_code', 'date_of_joining',
            'department_id', 'designation_id', 'reporting_manager_id', 'employment_type', 'work_location',
            'employment_status',
        ];
        $newData = [];
        foreach ($fields as $field) {
            $value = trim((string) $this->input($field, ''));
            $newData[$field] = $value === '' ? null : $value;
        }
        foreach (['department_id', 'designation_id', 'reporting_manager_id'] as $intField) {
            if ($newData[$intField] !== null) {
                $newData[$intField] = (int) $newData[$intField];
            }
        }

        // Field-level audit trail: log only what actually changed.
        foreach ($newData as $field => $newValue) {
            $oldValue = $profile[$field] ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                AuditService::log('employee_profile.field_changed', $userId, $field, $oldValue, $newValue);
            }
        }

        $profileModel->update((int) $profile['id'], $newData);

        $fullName = trim((string) $this->input('full_name', $user['full_name']));
        if ($fullName !== $user['full_name']) {
            AuditService::log('user.field_changed', $userId, 'full_name', $user['full_name'], $fullName);
            $userModel->update($userId, ['full_name' => $fullName]);
        }

        set_flash('success', 'Employee updated successfully.');
        $this->redirect('/admin/employees/' . $userId);
    }

    // ---------------------------------------------------------------
    // Lock / Unlock (Super Admin only — enforced via route middleware)
    // ---------------------------------------------------------------
    public function unlock(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $userModel = new User();
        $user = $userModel->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }

        $userModel->update($userId, ['profile_unlocked' => 1]);
        AuditService::log('profile.unlocked', $userId);

        NotificationService::notify($userId, 'profile_unlock', 'Your profile has been unlocked', 'You may now edit and resubmit your profile.', '/profile/edit');

        $template = (new EmailTemplate())->findBySlug('profile_unlock');
        if ($template && $template['is_active'] && setting('email_notifications_enabled', true)) {
            $rendered = (new EmailTemplate())->render($template, ['employee_name' => $user['full_name']]);
            MailService::queue($user['official_email'], $rendered['subject'], $rendered['body_html'], 'profile_unlock');
        }

        set_flash('success', 'Profile unlocked. The employee can now edit and resubmit.');
        $this->redirect('/admin/employees/' . $userId);
    }

    public function lock(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $userModel = new User();
        $user = $userModel->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }

        $profileModel = new EmployeeProfile();
        $profile = $profileModel->findByUser($userId);
        if ($profile) {
            $profileModel->update((int) $profile['id'], ['is_locked' => 1]);
        }
        $userModel->update($userId, ['profile_unlocked' => 0, 'profile_status' => 'submitted_locked']);
        AuditService::log('profile.locked', $userId);

        set_flash('success', 'Profile locked.');
        $this->redirect('/admin/employees/' . $userId);
    }

    public function activate(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $this->guardSuperAdminAccount($userId, 'activated');
        (new User())->update($userId, ['status' => 'active']);
        AuditService::log('user.activated', $userId);
        set_flash('success', 'Employee activated.');
        $this->redirect('/admin/employees/' . $userId);
    }

    public function deactivate(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $this->guardSuperAdminAccount($userId, 'deactivated');
        (new User())->update($userId, ['status' => 'inactive']);
        AuditService::log('user.deactivated', $userId);
        set_flash('success', 'Employee deactivated.');
        $this->redirect('/admin/employees/' . $userId);
    }

    public function lockAccount(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $this->guardSuperAdminAccount($userId, 'locked');
        (new User())->update($userId, ['status' => 'locked']);
        AuditService::log('user.locked', $userId);
        set_flash('success', 'Employee account locked.');
        $this->redirect('/admin/employees/' . $userId);
    }

    public function delete(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $this->guardSuperAdminAccount($userId, 'deleted');

        // Soft delete: preserves audit trail integrity.
        (new User())->update($userId, ['status' => 'inactive', 'deleted_at' => date('Y-m-d H:i:s')]);
        AuditService::log('user.deleted', $userId);

        set_flash('success', 'Employee deleted.');
        $this->redirect('/admin/employees');
    }

    private function guardSuperAdminAccount(int $userId, string $action): void
    {
        $user = (new User())->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }
        if ((int) $user['is_super_admin'] === 1) {
            set_flash('error', "The Super Admin account cannot be $action.");
            $this->redirect('/admin/employees/' . $userId);
        }
    }
}
