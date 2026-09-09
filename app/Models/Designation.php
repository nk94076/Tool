<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Designation extends Model
{
    protected string $table = 'designations';

    public function activeList(?int $departmentId = null): array
    {
        if ($departmentId) {
            $stmt = $this->db()->prepare("SELECT * FROM designations WHERE is_active = 1 AND department_id = :d ORDER BY name");
            $stmt->execute(['d' => $departmentId]);
            return $stmt->fetchAll();
        }
        return $this->db()->query("SELECT * FROM designations WHERE is_active = 1 ORDER BY name")->fetchAll();
    }

    /**
     * All designations (active and inactive) with their department name,
     * for the admin management list.
     */
    public function allWithDepartment(): array
    {
        return $this->db()->query(
            "SELECT ds.*, d.name AS department_name FROM designations ds
             LEFT JOIN departments d ON d.id = ds.department_id ORDER BY ds.name"
        )->fetchAll();
    }
}
