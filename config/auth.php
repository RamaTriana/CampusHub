<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/flash.php';

if (!isLoggedIn()) {

    setFlash(

        'Warning',

        'Silakan login terlebih dahulu.'

    );

    redirect(BASE_URL . '/login.php');

}