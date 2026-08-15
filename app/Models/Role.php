<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Role extends Model
{
    protected string $table = 'roles';

    public function findBySlug(string $slug): ?array
    {
        return $this->whereFirst('slug', $slug);
    }

    public function permissions(int $roleId): array
    {
        $stmt = $this->db()->prepare(
            "SELECT p.* FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = :rid ORDER BY p.group_name, p.slug"
        );
        $stmt->execute(['rid' => $roleId]);
        return $stmt->fetchAll();
    }

    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $db = $this->db();
        $db->prepare("DELETE FROM role_permissions WHERE role_id = :rid")->execute(['rid' => $roleId]);
        $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:rid, :pid)");
        foreach ($permissionIds as $pid) {
            $stmt->execute(['rid' => $roleId, 'pid' => (int) $pid]);
        }
    }

    public function activeRoles(): array
    {
        return $this->db()->query("SELECT * FROM roles WHERE is_active = 1 AND slug <> 'super_admin' ORDER BY name")->fetchAll();
    }
}
