<?php

require_once __DIR__ . '/auth.php';

function requireRole($role)
{
    if ($_SESSION['role'] != $role) {

        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}