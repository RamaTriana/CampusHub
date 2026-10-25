<?php

declare(strict_types=1);

namespace CampusHub\Http\Middleware;

use Closure;
use CampusHub\Core\Middleware;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Core\Session;
use RuntimeException;

final class AuthMiddleware implements Middleware
{
    private Closure $accountLookup;

    public function __construct(?Closure $accountLookup = null)
    {
        $this->accountLookup = $accountLookup
            ?? static fn (int $userId, Request $_request): ?array => self::findAccount($userId);
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!self::isAuthenticated($request)) {
            return Response::redirect('/login');
        }

        $userId = filter_var(
            $request->session('user_id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if (!is_int($userId)) {
            self::invalidateSession();

            return Response::redirect('/login');
        }

        try {
            $account = ($this->accountLookup)($userId, $request);
        } catch (RuntimeException $exception) {
            error_log('CampusHub could not verify the authenticated account (' . get_class($exception) . ').');

            return Response::text('Authentication service unavailable.', 503);
        }

        $allowedRoles = [ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT];

        if (
            !is_array($account)
            || !in_array($account['role'] ?? null, $allowedRoles, true)
            || ($account['status'] ?? null) !== STATUS_ACTIVE
            || $account['role'] !== $request->session('role')
        ) {
            self::invalidateSession();

            return Response::redirect('/login');
        }

        return $next($request);
    }

    private static function findAccount(int $userId): ?array
    {
        require_once dirname(__DIR__, 3) . '/config/database.php';
        $database = new \Database();
        $statement = $database->conn->prepare(
            'SELECT role, status FROM users WHERE id_user = ? LIMIT 1'
        );

        try {
            $statement->bind_param('i', $userId);
            $statement->execute();
            $account = $statement->get_result()->fetch_assoc();

            return is_array($account) ? $account : null;
        } finally {
            $statement->close();
        }
    }

    private static function invalidateSession(): void
    {
        Session::logout();
        Session::flash('Danger', 'Sesi berakhir karena status atau hak akses akun berubah. Silakan masuk kembali.');
    }

    public static function isAuthenticated(Request $request): bool
    {
        return $request->session('is_login') === true
            && $request->session('user_id') !== null
            && $request->session('user_id') !== '';
    }
}
