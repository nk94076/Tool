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
        $departments = (new Department())->withEmployeeCounts();

        $activeCount = count(array_filter($departments, fn($d) => (int) $d['is_active'] === 1));
        $largest = null;
        foreach ($departments as $d) {
            if ($largest === null || (int) $d['employee_count'] > (int) $largest['employee_count']) {
                $largest = $d;
            }
        }

        $this->view('admin/departments_index', [
            'title' => 'Departments',
            'departments' => $departments,
            'activeCount' => $activeCount,
            'inactiveCount' => count($departments) - $activeCount,
            'largestDepartment' => ($largest && (int) $largest['employee_count'] > 0) ? $largest : null,
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
        $description = trim((string) $this->input('description', ''));
        $isActive = $this->input('is_active', '1') === '1' ? 1 : 0;
        $id = (new Department())->insert([
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'is_active' => $isActive,
        ]);
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
        $description = trim((string) $this->input('description', $dept['description'] ?? ''));
        $isActive = $this->input('is_active', (string) $dept['is_active']) === '1' ? 1 : 0;
        $model->update((int) $dept['id'], [
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'is_active' => $isActive,
        ]);
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
