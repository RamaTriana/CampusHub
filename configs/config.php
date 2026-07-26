<?php

// ===============================
// KONFIGURASI APLIKASI
// ===============================

date_default_timezone_set('Asia/Makassar');

define('APP_NAME', 'CampusHub');
define('APP_VERSION', '1.0');
// define(
//     'BASE_URL',
//     (isset($_SERVER['HTTPS']) ? 'https' : 'http')
//     .'://'
//     .$_SERVER['HTTP_HOST']
// );

define('BASE_URL', 'http://campushub.test/');
define('UPLOAD_PATH', 'assets/uploads/');
define('IMG_PATH','assets/img/');

// Folder Upload
define('UPLOAD_PROFILE', UPLOAD_PATH.'profile/');
define('UPLOAD_ORGANISASI', UPLOAD_PATH.'organisasi/');
define('UPLOAD_POSTER', UPLOAD_PATH.'poster/');
define('UPLOAD_DOKUMENTASI', UPLOAD_PATH.'dokumentasi/');
define('UPLOAD_SERTIFIKAT', UPLOAD_PATH.'sertifikat/');

define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('DEFAULT_PROFILE', UPLOAD_PATH.'default-avatar.png');

// Role User
define('ROLE_ADMIN', 'Admin');
define('ROLE_COMMITTEE', 'Panitia');
define('ROLE_STUDENT', 'Mahasiswa');

//tambah Status
define('STATUS_PENDING','Menunggu');
define('STATUS_APPROVED','Disetujui');
define('STATUS_REJECTED','Ditolak');

define('STATUS_ACTIVE','Aktif');
define('STATUS_INACTIVE','Nonaktif');