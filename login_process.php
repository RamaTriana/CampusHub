<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/functions.php';
require_once 'config/validator.php';
require_once 'config/flash.php';

$db = new Database();

$conn = $db->conn;

/*
|--------------------------------------------------------------------------
| REQUEST METHOD
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect(BASE_URL . 'login.php');

}

/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

$username = sanitize($_POST['username'] ?? '');

$password = $_POST['password'] ?? '';

$remember = isset($_POST['remember']);

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (!required($username)) {

    setFlash(

        'Danger',

        'Username wajib diisi.'

    );

    redirect(BASE_URL . '/login.php');

}

if (!required($password)) {

    setFlash(

        'Danger',

        'Password wajib diisi.'

    );

    redirect(BASE_URL . '/login.php');

}

echo "Lewat validasi<br>";

/*
|--------------------------------------------------------------------------
| FIND USER
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM users

WHERE username = ?

LIMIT 1";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $username);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    setFlash(

        'Danger',

        'Username tidak ditemukan.'

    );

    redirect(BASE_URL . '/login.php');

}

$user = $result->fetch_assoc();

$stmt->close();

echo "User ditemukan<br>";

/*
|--------------------------------------------------------------------------
| VERIFY PASSWORD
|--------------------------------------------------------------------------
*/

if (!password_verify($password, $user['password'])) {

    setFlash(
        'Danger',
        'Password yang Anda masukkan salah.'
    );

    redirect(BASE_URL . '/login.php');

}
echo "Password benar<br>";
/*
|--------------------------------------------------------------------------
| ACCOUNT STATUS
|--------------------------------------------------------------------------
*/

if (
    isset($user['status']) &&
    $user['status'] !== STATUS_ACTIVE
) {

    setFlash(
        'Warning',
        'Akun Anda sedang dinonaktifkan.'
    );

    redirect(BASE_URL . '/login.php');

}

/*
|--------------------------------------------------------------------------
| CREATE SESSION
|--------------------------------------------------------------------------
*/

createUserSession($user);


/*
|--------------------------------------------------------------------------
| REDIRECT ROLE
|--------------------------------------------------------------------------
*/

redirectByRole($user['role']);