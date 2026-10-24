<!DOCTYPE html>
<html lang="id">

<head>

    <?php
    require_once __DIR__ . '/guard.php';

    if (!isset($pageTitle)) {
        $pageTitle = "CampusHub";
    }

    ?>

    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= $pageTitle; ?></title>

    <meta
        name="description"
        content="CampusHub - Campus Organization & Event Management System">

    <meta
        name="author"
        content="CampusHub">

    <!-- Favicon -->

    <link
        rel="icon"
        href="/public/assets/images/logo.png">

    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Google Font -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- UI Kit -->

    <link
        rel="stylesheet"
        href="/public/assets/css/style.css">

    <!-- Page CSS -->

    <?php

    if (isset($pageCSS)) {

        if (is_array($pageCSS)) {
            foreach ($pageCSS as $css) {
                echo '<link rel="stylesheet" href="/public/assets/css/' . $css . '">';
            }
        } else {
            echo '<link rel="stylesheet" href="/public/assets/css/' . $pageCSS . '">';
}


    }

    ?>

</head>