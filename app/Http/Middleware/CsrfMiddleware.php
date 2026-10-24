<?php

declare(strict_types=1);

namespace CampusHub\Http\Middleware;

use CampusHub\Core\CsrfToken;
use CampusHub\Core\Middleware;
use CampusHub\Core\Request;
use CampusHub\Core\Response;

final class CsrfMiddleware implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        if (!CsrfToken::isValid(
            $request->post('_csrf_token'),
            $request->session('_mvc_csrf_token')
        )) {
            return Response::text('CSRF token mismatch.', 403);
        }

        return $next($request);
    }
}
