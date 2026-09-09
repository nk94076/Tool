<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Department extends Model
{
    protected string $table = 'departments';

    public function activeList(): array
    {
        return $this->db()->query("SELECT * FROM departments WHERE is_active = 1 ORDER BY name")->fetchAll();
    }

    public function withEmployeeCounts(): array
    {
        return $this->db()->query(
            "SELECT d.*, COUNT(u.id) AS employee_count
             FROM departments d
             LEFT JOIN employee_profiles ep ON ep.department_id = d.id
             LEFT JOIN users u ON u.id = ep.user_id AND u.deleted_at IS NULL
             GROUP BY d.id
             ORDER BY d.name"
        )->fetchAll();
    }
}
