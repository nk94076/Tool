<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Services\AuditService;

final class RoleController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $roleModel = new Role();
        $roles = $roleModel->all('name');
        foreach ($roles as &$role) {
            $role['permission_count'] = count($roleModel->permissions((int) $role['id']));
        }
        unset($role);

        $this->view('admin/roles_index', [
            'title' => 'Roles & Permissions',
            'roles' => $roles,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->view('admin/role_form', [
            'title' => 'Create Role',
            'role' => null,
            'permissionGroups' => (new Permission())->grouped(),
            'assignedPermissionIds' => [],
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $name = trim((string) $this->input('name', ''));
        $description = trim((string) $this->input('description', ''));

        if ($name === '') {
            set_flash('error', 'Role name is required.');
            $this->redirect('/admin/roles/create');
        }

        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $name), '_'));
        if (in_array($slug, ['super_admin'], true)) {
            set_flash('error', 'This role name is reserved.');
            $this->redirect('/admin/roles/create');
        }

        $roleModel = new Role();
        $roleId = $roleModel->insert(['name' => $name, 'slug' => $slug, 'description' => $description]);

        $permissionIds = array_map('intval', (array) $this->input('permissions', []));
        $roleModel->syncPermissions($roleId, $permissionIds);

        AuditService::log('role.created', null, 'role', null, $name);
        set_flash('success', 'Role created.');
        $this->redirect('/admin/roles');
    }

    public function edit(array $params): void
    {
        $this->requireLogin();
        $roleModel = new Role();
        $role = $roleModel->find((int) $params['id']);
        if (!$role) {
            (new \App\Core\Router())->abort(404);
        }
        if ($role['slug'] === 'super_admin') {
            set_flash('error', 'The Super Admin role cannot be edited.');
            $this->redirect('/admin/roles');
        }

        $assigned = array_column($roleModel->permissions((int) $role['id']), 'id');

        $this->view('admin/role_form', [
            'title' => 'Edit Role',
            'role' => $role,
            'permissionGroups' => (new Permission())->grouped(),
            'assignedPermissionIds' => $assigned,
        ]);
    }

    public function update(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $roleModel = new Role();
        $role = $roleModel->find((int) $params['id']);
        if (!$role || $role['slug'] === 'super_admin') {
            set_flash('error', 'This role cannot be modified.');
            $this->redirect('/admin/roles');
        }

        $name = trim((string) $this->input('name', $role['name']));
        $description = trim((string) $this->input('description', ''));
        $isActive = $this->input('is_active', '1') === '1' ? 1 : 0;

        $roleModel->update((int) $role['id'], ['name' => $name, 'description' => $description, 'is_active' => $isActive]);

        $permissionIds = array_map('intval', (array) $this->input('permissions', []));
        $roleModel->syncPermissions((int) $role['id'], $permissionIds);

        AuditService::log('role.updated', null, 'role', $role['name'], $name);
        set_flash('success', 'Role updated.');
        $this->redirect('/admin/roles');
    }

    public function delete(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $roleModel = new Role();
        $role = $roleModel->find((int) $params['id']);
        if (!$role || $role['is_system']) {
            set_flash('error', 'This role cannot be deleted.');
            $this->redirect('/admin/roles');
        }

        $roleModel->delete((int) $role['id']);
        AuditService::log('role.deleted', null, 'role', $role['name'], null);
        set_flash('success', 'Role deleted.');
        $this->redirect('/admin/roles');
    }

    // ---------------------------------------------------------------
    // Assign roles to a specific user
    // ---------------------------------------------------------------
    public function assignForm(array $params): void
    {
        $this->requireLogin();
        $userId = (int) $params['id'];
        $user = (new User())->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }
        $assigned = array_column((new User())->roles($userId), 'id');

        $this->view('admin/user_roles_form', [
            'title' => 'Assign Roles - ' . $user['full_name'],
            'user' => $user,
            'roles' => (new Role())->activeRoles(),
            'assignedRoleIds' => $assigned,
        ]);
    }

    public function assignUpdate(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $userId = (int) $params['id'];
        $user = (new User())->find($userId);
        if (!$user) {
            (new \App\Core\Router())->abort(404);
        }
        if ((int) $user['is_super_admin'] === 1) {
            set_flash('error', 'The Super Admin role assignment cannot be changed.');
            $this->redirect('/admin/employees/' . $userId);
        }

        $roleIds = array_map('intval', (array) $this->input('roles', []));
        (new User())->syncRoles($userId, $roleIds, \App\Core\Auth::id());

        AuditService::log('user.roles_changed', $userId);
        set_flash('success', 'Roles updated.');
        $this->redirect('/admin/employees/' . $userId);
    }
}
