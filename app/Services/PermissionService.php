<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Auth;

/**
 * Server-side permission checks. Every protected route MUST call
 * PermissionMiddleware (which uses this service) — never trust the
 * frontend to hide a button as the only protection.
 */
final class PermissionService
{
    private static array $cache = [];

    public static function userHas(?int $userId, string $permissionSlug): bool
    {
        if ($userId === null) {
            return false;
        }

        $user = Auth::user();
        if ($user && (int) $user['id'] === $userId && (bool) $user['is_super_admin']) {
            return true; // Super Admin bypasses normal permission restrictions.
        }

        $slugs = self::permissionsForUser($userId);
        return in_array($permissionSlug, $slugs, true);
    }

    public static function permissionsForUser(int $userId): array
    {
        if (isset(self::$cache[$userId])) {
            return self::$cache[$userId];
        }

        $stmt = Database::connection()->prepare(
            "SELECT DISTINCT p.slug
             FROM permissions p
             INNER JOIN role_permissions rp ON rp.permission_id = p.id
             INNER JOIN user_roles ur ON ur.role_id = rp.role_id
             INNER JOIN roles r ON r.id = ur.role_id AND r.is_active = 1
             WHERE ur.user_id = :uid"
        );
        $stmt->execute(['uid' => $userId]);
        $slugs = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        self::$cache[$userId] = $slugs;
        return $slugs;
    }
}
