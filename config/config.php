<?php

/**
 * ==========================================
 * CAMPUSHUB CONFIGURATION
 * ==========================================
 */

date_default_timezone_set('Asia/Makassar');

/*
|--------------------------------------------------------------------------
| APP
|--------------------------------------------------------------------------
*/

define('APP_NAME', 'CampusHub');
define('APP_VERSION', '1.0.0');

define('BASE_URL', 'http://campushub.test');

/*
|--------------------------------------------------------------------------
| PATH UPLOAD
|--------------------------------------------------------------------------
*/

define('UPLOAD_PATH', 'assets/uploads/');

define('UPLOAD_PROFILE', UPLOAD_PATH . 'profile/');
define('UPLOAD_ORGANISASI', UPLOAD_PATH . 'organisasi/');
define('UPLOAD_POSTER', UPLOAD_PATH . 'poster/');
define('UPLOAD_DOKUMENTASI', UPLOAD_PATH . 'dokumentasi/');
define('UPLOAD_SERTIFIKAT', UPLOAD_PATH . 'sertifikat/');

/*
|--------------------------------------------------------------------------
| IMAGE
|--------------------------------------------------------------------------
*/

define('IMAGE_PATH', 'public/assets/images/');
define('DEFAULT_PROFILE', IMAGE_PATH . 'default-avatar.png');

/*
|--------------------------------------------------------------------------
| UPLOAD
|--------------------------------------------------------------------------
*/

define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB

define('ALLOWED_IMAGE', ['jpg', 'jpeg', 'png']);

define('ALLOWED_DOCUMENT', ['pdf']);

/*
|--------------------------------------------------------------------------
| ROLE
|--------------------------------------------------------------------------
*/

define('ROLE_ADMIN', 'Admin');
define('ROLE_COMMITTEE', 'Panitia');
define('ROLE_STUDENT', 'Mahasiswa');

/*
|--------------------------------------------------------------------------
| STATUS USER
|--------------------------------------------------------------------------
*/

define('STATUS_ACTIVE', 'Aktif');
define('STATUS_INACTIVE', 'Nonaktif');

/*
|--------------------------------------------------------------------------
| STATUS PENDAFTARAN
|--------------------------------------------------------------------------
*/

define('STATUS_PENDING', 'Menunggu');
define('STATUS_APPROVED', 'Disetujui');
define('STATUS_REJECTED', 'Ditolak');

/*
|--------------------------------------------------------------------------
| STATUS KEHADIRAN
|--------------------------------------------------------------------------
*/

define('ATTENDANCE_PRESENT', 'Hadir');
define('ATTENDANCE_ABSENT', 'Tidak Hadir');