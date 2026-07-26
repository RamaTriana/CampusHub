<?php

/**
 * ==========================================
 * FLASH MESSAGE
 * ==========================================
 */

function setFlash($type, $message)
{
    $_SESSION['flash'] = [

        'type' => $type,

        'message' => $message

    ];
}

function hasFlash()
{
    return isset($_SESSION['flash']);
}

function getFlash()
{

    if (!isset($_SESSION['flash'])) {

        return null;

    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    return $flash;
}