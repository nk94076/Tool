<?php
/**
 * Application bootstrap: autoload, env, timezone, error handling.
 * Included by public/index.php and all CLI entry points (cron/*, database/*).
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0'); // never leak errors to output; logged instead

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

// ---------------------------------------------------------------------
// Load .env
// ---------------------------------------------------------------------
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// ---------------------------------------------------------------------
// Config accessor
// ---------------------------------------------------------------------
$GLOBALS['__config'] = require BASE_PATH . '/config/config.php';

date_default_timezone_set(config('app.timezone', 'Asia/Kolkata'));

// ---------------------------------------------------------------------
// Error / exception handling: never leak internals to the browser.
// ---------------------------------------------------------------------
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    app_log("PHP Error [$severity]: $message in $file:$line");
    if ($severity & (E_ERROR | E_USER_ERROR)) {
        http_response_code(500);
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
    return true;
});

set_exception_handler(function (Throwable $e): void {
    app_log('Uncaught exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, $e->getMessage() . "\n");
        return;
    }
    require BASE_PATH . '/views/errors/500.php';
});

/**
 * Log a message to storage/logs/app.log without ever exposing it to users.
 */
function app_log(string $message): void
{
    $dir = BASE_PATH . '/storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
}

/**
 * Dot-notation config getter, e.g. config('mail.host').
 */
function config(string $key, mixed $default = null): mixed
{
    $segments = explode('.', $key);
    $value = $GLOBALS['__config'];
    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        'empty', '(empty)' => '',
        default => $value,
    };
}
