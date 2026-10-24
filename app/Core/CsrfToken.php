<?php

declare(strict_types=1);

namespace CampusHub\Core;

use RuntimeException;

final class CsrfToken
{
    private const SESSION_KEY = '_mvc_csrf_token';

    public static function get(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Start the session before generating a CSRF token.');
        }

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function isValid(mixed $provided, mixed $expected): bool
    {
        return is_string($provided)
            && is_string($expected)
            && $provided !== ''
            && hash_equals($expected, $provided);
    }
}
