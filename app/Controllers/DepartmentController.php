<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Department;
use App\Services\AuditService;

final class DepartmentController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('admin/departments_index', [
            'title' => 'Departments',
            'departments' => (new Department())->all('name'),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            set_flash('error', 'Department name is required.');
            $this->redirect('/admin/departments');
        }
        $id = (new Department())->insert(['name' => $name]);
        AuditService::log('department.created', null, 'department', null, $name);
        set_flash('success', 'Department added.');
        $this->redirect('/admin/departments');
    }

    public function update(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new Department();
        $dept = $model->find((int) $params['id']);
        if (!$dept) {
            (new \App\Core\Router())->abort(404);
        }
        $name = trim((string) $this->input('name', $dept['name']));
        $isActive = $this->input('is_active', '1') === '1' ? 1 : 0;
        $model->update((int) $dept['id'], ['name' => $name, 'is_active' => $isActive]);
        AuditService::log('department.updated', null, 'department', $dept['name'], $name);
        set_flash('success', 'Department updated.');
        $this->redirect('/admin/departments');
    }

    public function delete(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new Department();
        $dept = $model->find((int) $params['id']);
        if ($dept) {
            $model->update((int) $dept['id'], ['is_active' => 0]);
            AuditService::log('department.deactivated', null, 'department', $dept['name'], null);
        }
        set_flash('success', 'Department deactivated.');
        $this->redirect('/admin/departments');
    }
}
