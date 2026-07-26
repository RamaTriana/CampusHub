<?php

function redirect($url)
{
    header("Location: " . BASE_URL . $url);
    exit;
}

function sanitize($data)
{

    return htmlspecialchars(trim($data));

}

function setFlash($type, $message)
{
    $_SESSION['flash'] = [

        'type'=>$type,

        'message'=>$message

    ];
}

function getFlash()
{

    if(isset($_SESSION['flash'])){

        $flash=$_SESSION['flash'];

        unset($_SESSION['flash']);

        return $flash;

    }

    return null;

}