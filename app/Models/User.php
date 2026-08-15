<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->whereFirst('official_email', mb_strtolower(trim($email)));
    }

    public function superAdmin(): ?array
    {
        return $this->whereFirst('is_super_admin', 1);
    }

    public function roles(int $userId): array
    {
        $stmt = $this->db()->prepare(
            "SELECT r.* FROM roles r
             INNER JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = :uid AND r.is_active = 1"
        );
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function assignRole(int $userId, int $roleId, ?int $assignedBy): void
    {
        $stmt = $this->db()->prepare(
            "INSERT IGNORE INTO user_roles (user_id, role_id, assigned_by) VALUES (:uid, :rid, :by)"
        );
        $stmt->execute(['uid' => $userId, 'rid' => $roleId, 'by' => $assignedBy]);
    }

    public function removeRole(int $userId, int $roleId): void
    {
        $stmt = $this->db()->prepare("DELETE FROM user_roles WHERE user_id = :uid AND role_id = :rid");
        $stmt->execute(['uid' => $userId, 'rid' => $roleId]);
    }

    public function syncRoles(int $userId, array $roleIds, ?int $assignedBy): void
    {
        $this->db()->prepare("DELETE FROM user_roles WHERE user_id = :uid")->execute(['uid' => $userId]);
        foreach ($roleIds as $roleId) {
            $this->assignRole($userId, (int) $roleId, $assignedBy);
        }
    }

    public function search(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where = ["u.deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(u.full_name LIKE :q1 OR ep.employee_code LIKE :q2 OR u.official_email LIKE :q3)";
            $like = '%' . $filters['q'] . '%';
            $params['q1'] = $like;
            $params['q2'] = $like;
            $params['q3'] = $like;
        }
        if (!empty($filters['department_id'])) {
            $where[] = "ep.department_id = :department_id";
            $params['department_id'] = $filters['department_id'];
        }
        if (!empty($filters['designation_id'])) {
            $where[] = "ep.designation_id = :designation_id";
            $params['designation_id'] = $filters['designation_id'];
        }
        if (!empty($filters['joining_year'])) {
            $where[] = "YEAR(ep.date_of_joining) = :joining_year";
            $params['joining_year'] = $filters['joining_year'];
        }
        if (!empty($filters['birthday_month'])) {
            $where[] = "MONTH(ep.date_of_birth) = :birthday_month";
            $params['birthday_month'] = $filters['birthday_month'];
        }
        if (!empty($filters['status'])) {
            $where[] = "u.status = :status";
            $params['status'] = $filters['status'];
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $sql = "SELECT u.id, u.full_name, u.official_email, u.status, u.profile_status,
                       ep.employee_code, ep.profile_photo_path, ep.department_id, ep.designation_id,
                       d.name AS department_name, ds.name AS designation_name
                FROM users u
                LEFT JOIN employee_profiles ep ON ep.user_id = u.id
                LEFT JOIN departments d ON d.id = ep.department_id
                LEFT JOIN designations ds ON ds.id = ep.designation_id
                WHERE $whereSql
                ORDER BY u.full_name ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue('limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $countSql = "SELECT COUNT(*) FROM users u LEFT JOIN employee_profiles ep ON ep.user_id = u.id WHERE $whereSql";
        $countStmt = $this->db()->prepare($countSql);
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();

        return ['rows' => $rows, 'total' => (int) $countStmt->fetchColumn()];
    }

    public function counts(): array
    {
        $sql = "SELECT
            COUNT(*) AS total,
            SUM(status = 'active') AS active,
            SUM(status = 'inactive') AS inactive,
            SUM(profile_status <> 'submitted_locked') AS pending_profiles
            FROM users WHERE deleted_at IS NULL";
        $row = $this->db()->query($sql)->fetch();
        return [
            'total' => (int) ($row['total'] ?? 0),
            'active' => (int) ($row['active'] ?? 0),
            'inactive' => (int) ($row['inactive'] ?? 0),
            'pending_profiles' => (int) ($row['pending_profiles'] ?? 0),
        ];
    }
}
