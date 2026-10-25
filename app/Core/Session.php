<?php

declare(strict_types=1);

namespace CampusHub\Core;

use RuntimeException;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        if (!session_start()) {
            throw new RuntimeException('Unable to start session.');
        }
    }

    public static function authenticate(array $user): void
    {
        self::ensureActive();

        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Unable to rotate the session identifier.');
        }

        unset($_SESSION['_mvc_csrf_token']);
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['foto'] = !empty($user['foto']) ? $user['foto'] : DEFAULT_PROFILE;
        $_SESSION['organisasi'] = $user['id_organisasi'] ?? null;
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        $_SESSION['is_login'] = true;
    }

    public static function logout(): void
    {
        self::ensureActive();
        $_SESSION = [];

        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Unable to invalidate the session identifier.');
        }
    }

    public static function updateProfile(string $name, string $email, ?string $photo = null): void
    {
        self::ensureActive();
        $_SESSION['nama'] = $name;
        $_SESSION['email'] = $email;
        if ($photo !== null) {
            $_SESSION['foto'] = $photo;
        }
    }

    public static function flash(string $type, string $message): void
    {
        self::ensureActive();
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public static function pullFlash(): ?array
    {
        self::ensureActive();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return is_array($flash) ? $flash : null;
    }

    private static function ensureActive(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Start the session before using session data.');
        }
    }
}
