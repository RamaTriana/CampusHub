<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}