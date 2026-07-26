<?php

function redirect($url)
{
    header("Location: " . $url);
    exit;
}

function sanitize($data)
{
    return htmlspecialchars(trim($data));
}

function isLoggedIn()
{
    return isset($_SESSION['id_user']);
}

function userRole()
{
    return $_SESSION['role'] ?? null;
}

function formatTanggal($tanggal)
{
    return date('d M Y', strtotime($tanggal));
}