    <?php

    require_once '../config/config.php';
    require_once '../config/session.php';
    require_once '../config/functions.php';
    require_once '../config/flash.php';
    require_once '../config/auth.php';
    require_once '../config/role.php';

    if (!isCommittee()) {

    redirect(BASE_URL . '/login.php');
    }

    ?>

    <!DOCTYPE html>

    <html>

    <head>

    <title>Committee Dashboard</title>

    </head>

    <body>

    <h1>

    Committee Portal

    </h1>

    <p>

    <?= e($_SESSION['nama']); ?>

    </p>

    <a href="../logout.php">

    Logout

    </a>

    </body>

    </html>