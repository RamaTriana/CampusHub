<?php

require_once dirname(__DIR__) . '/guard.php';
$pageCSS = array_values(array_unique(array_merge(
    is_array($pageCSS ?? null) ? $pageCSS : [],
    ['dashboard-portals.css']
)));
require dirname(__DIR__) . '/head.php';
?>

<body class="ch-dashboard ch-dashboard--admin">
<a class="ch-skip-link" href="#main-content">Lewati ke konten utama</a>
<div class="ch-admin-shell">
    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="ch-main" id="main-content">
        <?php require __DIR__ . '/topbar.php'; ?>
