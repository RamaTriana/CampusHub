<?php

declare(strict_types=1);

namespace CampusHub\Http\Middleware;

use CampusHub\Core\Middleware;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Core\Session;

final class GuestMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (AuthMiddleware::isAuthenticated($request)) {
            $destination = self::destinationForRole($request->session('role'));

            if ($destination === '/login') {
                Session::logout();
                Session::flash('Danger', 'Role akun tidak dikenali.');
                return Response::redirect('/login');
            }

            return Response::redirect($destination);
        }

        return $next($request);
    }

    public static function destinationForRole(mixed $role): string
    {
        return match ($role) {
            ROLE_ADMIN => '/admin/dashboard',
            ROLE_COMMITTEE => '/committee/dashboard',
            ROLE_STUDENT => '/student/dashboard',
            default => '/login',
        };
    }
}
