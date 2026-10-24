<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$request = \CampusHub\Core\Request::fromGlobals();
$response = $router->dispatch($request);
$response->send();
