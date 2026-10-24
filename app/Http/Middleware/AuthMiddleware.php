<?php

declare(strict_types=1);

namespace CampusHub\Http\Middleware;

use CampusHub\Core\Middleware;
use CampusHub\Core\Request;
use CampusHub\Core\Response;

final class AuthMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!self::isAuthenticated($request)) {
            return Response::redirect('/login');
        }

        return $next($request);
    }

    public static function isAuthenticated(Request $request): bool
    {
        return $request->session('is_login') === true
            && $request->session('user_id') !== null
            && $request->session('user_id') !== '';
    }
}
