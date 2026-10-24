<?php

declare(strict_types=1);

namespace CampusHub\Http\Middleware;

use CampusHub\Core\Middleware;
use CampusHub\Core\Request;
use CampusHub\Core\Response;

final class RoleMiddleware implements Middleware
{
    public function __construct(private array $allowedRoles)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!AuthMiddleware::isAuthenticated($request)) {
            return Response::redirect('/login');
        }

        $role = $request->session('role');

        if (!in_array($role, $this->allowedRoles, true)) {
            if (in_array($role, [ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT], true)) {
                return Response::redirect(GuestMiddleware::destinationForRole($role));
            }

            return Response::text('Forbidden', 403);
        }

        return $next($request);
    }
}
