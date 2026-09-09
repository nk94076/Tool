<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Designation;
use App\Models\Department;
use App\Services\AuditService;

final class DesignationController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $rows = (new Designation())->allWithDepartment();

        $activeCount = count(array_filter($rows, fn($d) => (int) $d['is_active'] === 1));
        $departmentsCovered = count(array_unique(array_filter(array_column($rows, 'department_id'))));

        $this->view('admin/designations_index', [
            'title' => 'Designations',
            'designations' => $rows,
            'departments' => (new Department())->activeList(),
            'activeCount' => $activeCount,
            'inactiveCount' => count($rows) - $activeCount,
            'departmentsCovered' => $departmentsCovered,
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $name = trim((string) $this->input('name', ''));
        $departmentId = (int) $this->input('department_id', 0) ?: null;
        if ($name === '') {
            set_flash('error', 'Designation name is required.');
            $this->redirect('/admin/designations');
        }
        $isActive = $this->input('is_active', '1') === '1' ? 1 : 0;
        (new Designation())->insert(['name' => $name, 'department_id' => $departmentId, 'is_active' => $isActive]);
        AuditService::log('designation.created', null, 'designation', null, $name);
        set_flash('success', 'Designation added.');
        $this->redirect('/admin/designations');
    }

    public function update(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new Designation();
        $desig = $model->find((int) $params['id']);
        if (!$desig) {
            (new \App\Core\Router())->abort(404);
        }
        $name = trim((string) $this->input('name', $desig['name']));
        $departmentIdRaw = $this->input('department_id', null);
        $departmentId = $departmentIdRaw !== null ? ((int) $departmentIdRaw ?: null) : $desig['department_id'];
        $isActive = $this->input('is_active', (string) $desig['is_active']) === '1' ? 1 : 0;
        $model->update((int) $desig['id'], ['name' => $name, 'department_id' => $departmentId, 'is_active' => $isActive]);
        AuditService::log('designation.updated', null, 'designation', $desig['name'], $name);
        set_flash('success', 'Designation updated.');
        $this->redirect('/admin/designations');
    }

    public function delete(array $params): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $model = new Designation();
        $desig = $model->find((int) $params['id']);
        if ($desig) {
            $model->update((int) $desig['id'], ['is_active' => 0]);
            AuditService::log('designation.deactivated', null, 'designation', $desig['name'], null);
        }
        set_flash('success', 'Designation deactivated.');
        $this->redirect('/admin/designations');
    }
}
