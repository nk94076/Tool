<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Services\PermissionService;

final class PermissionMiddleware
{
    /**
     * Usage in routes: [PermissionMiddleware::require('employees.edit')]
     * Every protected route MUST use this server-side — never rely on the
     * frontend hiding a button as the only protection.
     */
    public static function require(string $permissionSlug): callable
    {
        return function () use ($permissionSlug): void {
            $userId = Auth::id();
            if ($userId === null || !PermissionService::userHas($userId, $permissionSlug)) {
                http_response_code(403);
                if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Forbidden']);
                    exit;
                }
                require BASE_PATH . '/views/errors/403.php';
                exit;
            }
        };
    }

    public static function requireSuperAdmin(): callable
    {
        return function (): void {
            if (!Auth::isSuperAdmin()) {
                http_response_code(403);
                require BASE_PATH . '/views/errors/403.php';
                exit;
            }
        };
    }
}
