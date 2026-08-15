<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        $data['currentUser'] = Auth::user();
        $data['csrfField'] = Csrf::field();
        $data['csrfToken'] = Csrf::token();
        View::render($template, $data, $layout);
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        $source = $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
        return $source[$key] ?? $default;
    }

    protected function all(): array
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Csrf::verify($token)) {
            (new Router())->abort(419, 'CSRF token mismatch');
        }
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }
}
