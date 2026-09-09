<?php
declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Session;

/**
 * Escape output for safe HTML rendering (XSS protection).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function initials(string $fullName, int $max = 2): string
{
    $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY);
    if (empty($parts)) {
        return '?';
    }
    $letters = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, $max));
    return implode('', $letters);
}

function old(string $key, string $default = ''): string
{
    $old = Session::get('_old', []);
    return e($old[$key] ?? $default);
}

function csrf_field(): string
{
    return Csrf::field();
}

function csrf_token(): string
{
    return Csrf::token();
}

function flash(string $key): ?string
{
    return Session::flash($key);
}

function set_flash(string $key, string $message): void
{
    Session::flash($key, $message);
}

function asset(string $path): string
{
    $path = ltrim($path, '/');
    $full = BASE_PATH . '/public/assets/' . $path;
    $version = is_file($full) ? filemtime($full) : null;
    return '/assets/' . $path . ($version ? '?v=' . $version : '');
}

function url(string $path = ''): string
{
    return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
}

function format_date(?string $date, string $format = 'd M Y'): string
{
    if (!$date) {
        return '-';
    }
    try {
        return (new DateTime($date))->format($format);
    } catch (Exception) {
        return '-';
    }
}

/**
 * Fetch a system_settings value with type-casting, falling back to $default.
 * Cached per-request to avoid repeated queries.
 */
function setting(string $key, mixed $default = null): mixed
{
    static $cache = null;
    if ($cache === null) {
        $cache = (new \App\Models\SystemSetting())->allAsMap();
    }
    return $cache[$key] ?? $default;
}

/**
 * Has-permission helper for use in views (server already enforces via middleware;
 * this is only for hiding/showing UI elements).
 */
function can(string $permissionSlug): bool
{
    return \App\Services\PermissionService::userHas(\App\Core\Auth::id(), $permissionSlug);
}
