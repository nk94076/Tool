<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimal regex-based router. Routes are registered as clean paths
 * (e.g. "/employees/{id}") and dispatched to "Controller@method".
 * No ".php" ever appears in a public URL — see public/.htaccess.
 */
final class Router
{
    private array $routes = [];
    /** @var callable[] */
    private array $globalMiddleware = [];

    public function middleware(callable $middleware): void
    {
        $this->globalMiddleware[] = $middleware;
    }

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    public function any(array $methods, string $path, string $handler, array $middleware = []): void
    {
        foreach ($methods as $method) {
            $this->add($method, $path, $handler, $middleware);
        }
    }

    private function add(string $method, string $path, string $handler, array $middleware): void
    {
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', rtrim($path, '/') ?: '/');
        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . ($pattern === '' ? '/' : $pattern) . '$#',
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->globalMiddleware as $mw) {
            $mw();
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $mw) {
                    $mw($params);
                }

                [$controllerName, $action] = explode('@', $route['handler']);
                $class = 'App\\Controllers\\' . $controllerName;

                if (!class_exists($class)) {
                    $this->abort(500, 'Controller not found');
                }
                $controller = new $class();
                if (!method_exists($controller, $action)) {
                    $this->abort(500, 'Action not found');
                }
                $controller->$action($params);
                return;
            }
        }

        $this->abort(404, 'Not found');
    }

    public function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $file = BASE_PATH . "/views/errors/{$code}.php";
        if (file_exists($file)) {
            require $file;
        } else {
            echo htmlspecialchars($message ?: 'Error', ENT_QUOTES, 'UTF-8');
        }
        exit;
    }
}
