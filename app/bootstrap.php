<?php

declare(strict_types=1);

use CampusHub\Core\Router;
use CampusHub\Core\Session;
use CampusHub\Http\Middleware\AuthMiddleware;
use CampusHub\Http\Middleware\CsrfMiddleware;
use CampusHub\Http\Middleware\GuestMiddleware;
use CampusHub\Http\Middleware\RoleMiddleware;

define('CAMPUSHUB_BOOTSTRAPPED', true);

spl_autoload_register(static function (string $class): void {
    $prefix = 'CampusHub\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../config/config.php';
Session::start();

$router = new Router();
$router->registerMiddleware('auth', static fn (): AuthMiddleware => new AuthMiddleware());
$router->registerMiddleware('guest', static fn (): GuestMiddleware => new GuestMiddleware());
$router->registerMiddleware('role', static fn (string ...$roles): RoleMiddleware => new RoleMiddleware($roles));
$router->registerMiddleware('csrf', static fn (): CsrfMiddleware => new CsrfMiddleware());

$registerRoutes = require __DIR__ . '/../routes/web.php';
$registerRoutes($router);
