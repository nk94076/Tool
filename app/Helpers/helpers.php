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
    return '/assets/' . ltrim($path, '/');
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
