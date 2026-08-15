<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Models\RateLimit;

final class RateLimitMiddleware
{
    public static function require(string $action, int $maxAttempts, int $windowSeconds): callable
    {
        return function () use ($action, $maxAttempts, $windowSeconds): void {
            $key = ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $allowed = (new RateLimit())->attempt($key, $action, $maxAttempts, $windowSeconds);
            if (!$allowed) {
                http_response_code(429);
                require BASE_PATH . '/views/errors/429.php';
                exit;
            }
        };
    }
}
