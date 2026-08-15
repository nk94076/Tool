<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

final class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Auth::check()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthenticated']);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
}
