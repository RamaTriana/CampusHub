<?php

require_once __DIR__ . '/auth.php';

/**
 * Memastikan user memiliki role tertentu.
 *
 * @param string|array $roles
 */
function requireRole($roles)
{
    if (!isLoggedIn()) {
        redirect(BASE_URL. '/login.php');
    }

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array($_SESSION['role'], $roles, true)) {

        setFlash(
            'Danger',
            'Anda tidak memiliki hak akses ke halaman tersebut.'
        );

        redirectByRole($_SESSION['role']);
    }
}