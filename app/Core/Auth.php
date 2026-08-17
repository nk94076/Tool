<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authenticated-user accessor. The session only ever stores the user id;
 * every request re-fetches fresh user/role/permission data from the DB so
 * a deactivated/locked account or a permission change takes effect immediately.
 */
final class Auth
{
    private static ?array $userCache = null;

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        $id = self::id();
        if ($id === null) {
            return null;
        }
        if (self::$userCache !== null && self::$userCache['id'] === $id) {
            return self::$userCache;
        }

        $user = (new User())->findWithPhoto($id);
        if (!$user || $user['status'] !== 'active') {
            return null;
        }
        self::$userCache = $user;
        return $user;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        self::$userCache = null;
    }

    public static function logout(): void
    {
        Session::destroy();
        self::$userCache = null;
    }

    public static function isSuperAdmin(): bool
    {
        $user = self::user();
        return $user !== null && (bool) $user['is_super_admin'];
    }
}
