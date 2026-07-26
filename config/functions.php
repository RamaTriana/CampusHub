<?php

/**
 * Membersihkan input
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect
 */
function redirect($url)
{
    if (headers_sent($file, $line)) {
        die("Header sudah dikirim di file: $file pada baris: $line");
    }

    header("Location: " . $url);
    exit;
}

/**
 * Cek Login
 */
function isLoggedIn()
{
    // return isset($_SESSION['user_id']);
    return isset($_SESSION['is_login']) &&
        $_SESSION['is_login'] === true;
}

/**
 * Escape Output
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format Tanggal Indonesia
 */
function formatTanggal($tanggal)
{
    return date('d-m-Y', strtotime($tanggal));
}

/**
 * Upload Image
 * @param array $file
 * @param string $folder
 * @return array
 */
function uploadImage($file, $folder, $prefix = 'image_')
{
    // if ($file['error'] != 0) {
    //     return false;
    // }

    // $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // if (!in_array($extension, ALLOWED_IMAGE)) {
    //     return false;
    // }

    // if ($file['size'] > MAX_UPLOAD_SIZE) {
    //     return false;
    // }

    // $filename = uniqid() . "." . $extension;

    // move_uploaded_file(
    //     $file['tmp_name'],
    //     $folder . $filename
    // );

    // return $filename;
     $result = [

        'success'     => false,
        'filename'    => basename(DEFAULT_PROFILE),
        'destination' => '',
        'tmp_name'    => '',
        'message'     => ''

    ];

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {

        $result['success'] = true;

        return $result;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {

        $result['message'] = 'Terjadi kesalahan saat upload file.';

        return $result;
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {

        $result['message'] = 'Ukuran file maksimal 2 MB.';

        return $result;
    }

    $extension = strtolower(
        pathinfo($file['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($extension, ALLOWED_IMAGE, true)) {

        $result['message'] = 'Format file harus JPG, JPEG, atau PNG.';

        return $result;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    $mime = finfo_file($finfo, $file['tmp_name']);

    finfo_close($finfo);

    if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {

        $result['message'] = 'File gambar tidak valid.';

        return $result;
    }

    if (!is_dir($folder)) {

        mkdir($folder, 0777, true);

    }

    $filename =
        uniqid($prefix, true)
        . '.'
        . $extension;

    $destination =
        rtrim($folder, '/\\')
        . DIRECTORY_SEPARATOR
        . $filename;

    $result['success'] = true;
    $result['filename'] = $filename;
    $result['destination'] = $destination;
    $result['tmp_name'] = $file['tmp_name'];

    return $result;
}

function saveUploadedFile($tmpName,$destination)
{
    return move_uploaded_file(
        $tmpName,
        $destination
    );
}

function deleteUploadedFile($path)
{
    if(
        file_exists($path) &&
        is_file($path)
    ){
        unlink($path);
        return true;
    }

    return false;
}

function userExists(
    $conn,
    $username,
    $email
)
{

    $stmt=$conn->prepare("
    SELECT username,email
    FROM users
    WHERE username=?
    OR email=?
    LIMIT 1
    ");

    $stmt->bind_param(
        "ss",
        $username,
        $email
    );

    $stmt->execute();

    $result=$stmt->get_result();

    $user=$result->fetch_assoc();

    $stmt->close();

    return $user;

}

function createUser($conn,$data)
{

    $stmt=$conn->prepare("
    INSERT INTO users(
    id_organisasi,
    nama,
    nim,
    email,
    username,
    password,
    role,
    foto,
    status
    )
    VALUES(
    ?,?,?,?,?,?,?,?,?
    )
    ");

    $stmt->bind_param(
        "issssssss",
        $data['id_organisasi'],
        $data['nama'],
        $data['nim'],
        $data['email'],
        $data['username'],
        $data['password'],
        $data['role'],
        $data['foto'],
        $data['status']
    );

    $success=$stmt->execute();

    $stmt->close();

    return $success;

}

/**
 * ==========================================
 * CREATE USER SESSION
 * ==========================================
 */

function createUserSession($user)
{
    session_regenerate_id(true);

    $_SESSION['user_id']      = $user['id_user'];
    $_SESSION['nama']         = $user['nama'];
    $_SESSION['username']     = $user['username'];
    $_SESSION['email']        = $user['email'];
    $_SESSION['role']         = $user['role'];

    $_SESSION['foto'] = !empty($user['foto'])
        ? $user['foto']
        : DEFAULT_PROFILE;

    $_SESSION['organisasi'] = $user['id_organisasi'] ?? null;

    $_SESSION['login_time'] = date('Y-m-d H:i:s');

    $_SESSION['is_login'] = true;
}

/**
 * ==========================================
 * DESTROY SESSION
 * ==========================================
 */

function destroyUserSession()
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {

        $params = session_get_cookie_params();

        setcookie(

            session_name(),

            '',

            time() - 42000,

            $params["path"],

            $params["domain"],

            $params["secure"],

            $params["httponly"]

        );
    }

    session_destroy();
}

function isAdmin()
{
    return isLoggedIn() &&
        $_SESSION['role'] === ROLE_ADMIN;
}

function isCommittee()
{
    return isLoggedIn() &&
        $_SESSION['role'] === ROLE_COMMITTEE;
}

function isStudent()
{
    return isLoggedIn() &&
        $_SESSION['role'] === ROLE_STUDENT;
}

/**
 * ==========================================
 * REDIRECT USER BERDASARKAN ROLE
 * ==========================================
 */

function redirectByRole($role)
{
    switch ($role) {

        case ROLE_ADMIN:
            redirect(BASE_URL . '/admin/dashboard.php');
            break;

        case ROLE_COMMITTEE:
            redirect(BASE_URL . '/committee/dashboard.php');
            break;

        case ROLE_STUDENT:
            redirect(BASE_URL . '/student/dashboard.php');
            break;

        default:

            destroyUserSession();

            setFlash(
                'Danger',
                'Role tidak dikenali.'
            );

            redirect(BASE_URL . '/login.php');
    }
}