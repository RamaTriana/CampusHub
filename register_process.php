<?php

require_once 'config/config.php';
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'config/session.php';
require_once 'config/flash.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/register.php');
}

$db = new Database();
$conn = $db->conn;

$errors = [];

/*
|--------------------------------------------------------------------------
| Ambil Data
|--------------------------------------------------------------------------
*/

$nama      = trim($_POST['nama'] ?? '');
$username  = trim($_POST['username'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

/*
|--------------------------------------------------------------------------
| Validasi
|--------------------------------------------------------------------------
*/

if ($nama === '') {
    $errors[] = "Nama wajib diisi.";
}

if ($username === '') {
    $errors[] = "Username wajib diisi.";
}

if ($email === '') {
    $errors[] = "Email wajib diisi.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Format email tidak valid.";
}

if (strlen($password) < 8) {
    $errors[] = "Password minimal 8 karakter.";
}

if ($password !== $confirm) {
    $errors[] = "Konfirmasi password tidak sama.";
}

/*
|--------------------------------------------------------------------------
| Upload Foto
|--------------------------------------------------------------------------
*/

$upload = uploadImage(
    $_FILES['foto'],
    __DIR__ . '/' . UPLOAD_PROFILE,
    'profile_'
);

if (!$upload['success']) {

    $errors[] = $upload['message'];

}

$foto = $upload['filename'];

/*
|--------------------------------------------------------------------------
| Validasi Gagal
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {

    setFlash(
        'danger',
        implode("<br>", $errors)
    );

    redirect(BASE_URL . '/register.php');

}

/*
|--------------------------------------------------------------------------
| Cek Username & Email
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
SELECT id_user
FROM users
WHERE username = ?
   OR email = ?
LIMIT 1
");

$stmt->bind_param(
    "ss",
    $username,
    $email
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $check = $conn->prepare("
    SELECT username,email
    FROM users
    WHERE username = ?
       OR email = ?
    LIMIT 1
    ");

    $check->bind_param(
        "ss",
        $username,
        $email
    );

}

$stmt->close();

$stmt = $conn->prepare("
SELECT username,email
FROM users
WHERE username = ?
   OR email = ?
LIMIT 1
");

$stmt->bind_param(
    "ss",
    $username,
    $email
);

$stmt->execute();

$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {

    if ($user['username'] === $username) {

        $errors[] = "Username sudah digunakan.";

    }

    if ($user['email'] === $email) {

        $errors[] = "Email sudah terdaftar.";

    }

}

$stmt->close();

if (!empty($errors)) {

    setFlash(
        'danger',
        implode("<br>", $errors)
    );

    redirect(BASE_URL . '/register.php');

}

/*
|--------------------------------------------------------------------------
| Hash Password
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);
$idOrganisasi = null;
$nim = null;
$role = ROLE_STUDENT;
$status = STATUS_ACTIVE;
/*$role = ROLE_ADMIN;*/
/*$role = ROLE_COMMITTEE;*/

/*
|--------------------------------------------------------------------------
| Insert User
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
INSERT INTO users
(
id_organisasi,nama,nim,email,username,password,role,foto,status
)
VALUES
(
?,?,?,?,?,?,?,?,?
)
");

$stmt->bind_param(
    "issssssss",
    $idOrganisasi,
    $nama,
    $nim,
    $email,
    $username,
    $passwordHash,
    $role,
    $foto,
    $status
);

if (!$stmt->execute()) {

    $stmt->close();

    setFlash(
        'danger',
        'Registrasi gagal.'
    );

    redirect(BASE_URL . '/register.php');

}else {

    $errors[] = "Gagal menyimpan data.";

}

$stmt->close();

/*
|--------------------------------------------------------------------------
| Upload File
|--------------------------------------------------------------------------
|
| File baru dipindahkan apabila INSERT database berhasil.
|
*/

if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
) {

    saveUploadedFile(
        $upload['tmp_name'],
        $upload['destination']
    ); {

        // Nanti bisa ditambahkan rollback database
        // jika upload file gagal.
    }

}

/*if (
    $foto !== 'default.png'
) {

    move_uploaded_file(

        $_FILES['foto']['tmp_name'],

        $destination

    );

}
$stmt->close();*/

/*
|--------------------------------------------------------------------------
| Success
|--------------------------------------------------------------------------
*/

setFlash(
    'success',
    'Registrasi berhasil. Silakan login.'
);

redirect(BASE_URL . '/login.php');