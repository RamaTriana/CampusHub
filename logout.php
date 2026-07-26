<?php

require_once 'config/config.php';
require_once 'config/session.php';
require_once 'config/functions.php';
require_once 'config/flash.php';

destroyUserSession();

setFlash(

    'Success',

    'Anda berhasil logout.'

);

redirect(BASE_URL . '/login.php');