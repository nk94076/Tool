<?php
/**
 * Front controller. All requests are rewritten here by public/.htaccess.
 * No ".php" is ever exposed in a public URL.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();
require BASE_PATH . '/routes/web.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'] ?? '/';

$router->dispatch($method, $uri);
