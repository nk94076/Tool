<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class EmployeeProfile extends Model
{
    protected string $table = 'employee_profiles';

    public function findByUser(int $userId): ?array
    {
        return $this->whereFirst('user_id', $userId);
    }

    public function createForUser(int $userId): int
    {
        return $this->insert(['user_id' => $userId]);
    }

    public function todaysBirthdays(): array
    {
        return $this->birthdaysOn(date('Y-m-d'));
    }

    public function birthdaysOn(string $date): array
    {
        $stmt = $this->db()->prepare(
            "SELECT u.id, u.full_name, ep.date_of_birth, ep.profile_photo_path
             FROM employee_profiles ep
             INNER JOIN users u ON u.id = ep.user_id
             WHERE u.status = 'active' AND ep.date_of_birth IS NOT NULL
               AND DATE_FORMAT(ep.date_of_birth, '%m-%d') = DATE_FORMAT(:d, '%m-%d')"
        );
        $stmt->execute(['d' => $date]);
        return $stmt->fetchAll();
    }

    public function anniversariesOn(string $date): array
    {
        $stmt = $this->db()->prepare(
            "SELECT u.id, u.full_name, ep.date_of_joining, ep.profile_photo_path
             FROM employee_profiles ep
             INNER JOIN users u ON u.id = ep.user_id
             WHERE u.status = 'active' AND ep.date_of_joining IS NOT NULL
               AND ep.date_of_joining <= :d1
               AND DATE_FORMAT(ep.date_of_joining, '%m-%d') = DATE_FORMAT(:d2, '%m-%d')"
        );
        $stmt->execute(['d1' => $date, 'd2' => $date]);
        return $stmt->fetchAll();
    }

    public function withUser(int $userId): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT ep.*, u.full_name, u.official_email, u.status AS account_status,
                    d.name AS department_name, ds.name AS designation_name,
                    m.full_name AS manager_name
             FROM employee_profiles ep
             INNER JOIN users u ON u.id = ep.user_id
             LEFT JOIN departments d ON d.id = ep.department_id
             LEFT JOIN designations ds ON ds.id = ep.designation_id
             LEFT JOIN users m ON m.id = ep.reporting_manager_id
             WHERE ep.user_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
