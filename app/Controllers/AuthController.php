<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Flash;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Core\Session;
use CampusHub\Http\Middleware\GuestMiddleware;
use CampusHub\Models\AuthModel;
use mysqli_sql_exception;
use RuntimeException;

final class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        return $this->view('auth/login', [
            'flash' => Flash::pull(),
        ]);
    }

    public function login(Request $request): Response
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $username = is_string($username) ? trim($username) : '';
        $password = is_string($password) ? $password : '';

        if ($username === '' || $password === '') {
            Flash::set('Danger', 'Username dan password wajib diisi.');
            return Response::redirect('/login');
        }

        try {
            $model = new AuthModel();
            $user = $model->findByUsername($username);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Layanan autentikasi sedang tidak tersedia. Silakan coba lagi.');
            return Response::redirect('/login');
        }

        if (
            $user === null
            || !password_verify($password, (string) $user['password'])
        ) {
            Flash::set('Danger', 'Username atau password salah.');
            return Response::redirect('/login');
        }

        if (($user['status'] ?? STATUS_ACTIVE) !== STATUS_ACTIVE) {
            Flash::set('Warning', 'Akun Anda sedang dinonaktifkan.');
            return Response::redirect('/login');
        }

        $destination = GuestMiddleware::destinationForRole($user['role'] ?? null);

        if ($destination === '/login') {
            Flash::set('Danger', 'Role akun tidak dikenali.');
            return Response::redirect('/login');
        }

        try {
            Session::authenticate($user);
        } catch (RuntimeException $exception) {
            error_log('CampusHub session authentication failed (' . get_class($exception) . ').');
            Flash::set('Danger', 'Login tidak dapat diselesaikan. Silakan coba lagi.');
            return Response::redirect('/login');
        }

        return Response::redirect($destination);
    }

    public function showRegister(Request $request): Response
    {
        return $this->view('auth/register', [
            'flash' => Flash::pull(),
        ]);
    }

    public function register(Request $request): Response
    {
        $name = $this->postString($request, 'nama');
        $username = $this->postString($request, 'username');
        $email = $this->postString($request, 'email');
        $password = $request->post('password');
        $confirmation = $request->post('confirm_password');
        $password = is_string($password) ? $password : '';
        $confirmation = is_string($confirmation) ? $confirmation : '';
        $errors = [];

        if ($name === '') {
            $errors[] = 'Nama wajib diisi.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Nama maksimal 100 karakter.';
        }

        if ($username === '') {
            $errors[] = 'Username wajib diisi.';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Username maksimal 50 karakter.';
        }

        if ($email === '') {
            $errors[] = 'Email wajib diisi.';
        } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Format email tidak valid.';
        } elseif (strlen($email) > 100) {
            $errors[] = 'Email maksimal 100 karakter.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password minimal 8 karakter.';
        }

        if ($password !== $confirmation) {
            $errors[] = 'Konfirmasi password tidak sama.';
        }

        $upload = $this->validateProfilePhoto($request->file('foto'));

        if ($upload['error'] !== null) {
            $errors[] = $upload['error'];
        }

        if ($errors !== []) {
            Flash::set('Danger', implode("\n", $errors));
            return Response::redirect('/register');
        }

        try {
            $model = new AuthModel();
            $conflicts = $model->findIdentityConflicts($username, $email);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Registrasi belum dapat diproses. Silakan coba lagi.');
            return Response::redirect('/register');
        }

        foreach ($conflicts as $conflict) {
            if ($conflict['username'] === $username) {
                $errors[] = 'Username sudah digunakan.';
            }

            if ($conflict['email'] === $email) {
                $errors[] = 'Email sudah terdaftar.';
            }
        }

        if ($errors !== []) {
            Flash::set('Danger', implode("\n", $errors));
            return Response::redirect('/register');
        }

        $photoName = $upload['filename'];
        $storedPhotoPath = null;

        if ($upload['temporary_path'] !== null) {
            $storedPhotoPath = $this->storeProfilePhoto(
                $upload['temporary_path'],
                $upload['extension']
            );

            if ($storedPhotoPath === null) {
                Flash::set('Danger', 'Foto profil gagal disimpan. Silakan coba lagi.');
                return Response::redirect('/register');
            }

            $photoName = basename($storedPhotoPath);
        }

        try {
            $model->createStudent(
                $name,
                $email,
                $username,
                password_hash($password, PASSWORD_DEFAULT),
                $photoName
            );
        } catch (mysqli_sql_exception $exception) {
            $this->removeStoredPhoto($storedPhotoPath);
            $this->logDatabaseFailure($exception);

            if ((int) $exception->getCode() === 1062) {
                Flash::set('Danger', 'Username atau email sudah digunakan.');
            } else {
                Flash::set('Danger', 'Registrasi belum dapat diselesaikan. Silakan coba lagi.');
            }

            return Response::redirect('/register');
        } catch (RuntimeException $exception) {
            $this->removeStoredPhoto($storedPhotoPath);
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Registrasi belum dapat diselesaikan. Silakan coba lagi.');
            return Response::redirect('/register');
        }

        Flash::set('Success', 'Registrasi berhasil. Silakan login.');
        return Response::redirect('/login');
    }

    public function logout(Request $request): Response
    {
        try {
            Session::logout();
        } catch (RuntimeException $exception) {
            error_log('CampusHub session logout failed (' . get_class($exception) . ').');
            return Response::text('Logout tidak dapat diselesaikan. Silakan coba lagi.', 500);
        }

        Flash::set('Success', 'Anda berhasil logout.');
        return Response::redirect('/login');
    }


    public function confirmLogout(Request $request): Response
    {
        $destination = GuestMiddleware::destinationForRole(
            $request->session('role')
        );

        if ($destination === '/login') {
            return Response::redirect('/login');
        }

        return $this->view('auth/logout', [
            'backUrl' => $destination,
        ]);
    }


    private function postString(Request $request, string $key): string
    {
        $value = $request->post($key);

        return is_string($value) ? trim($value) : '';
    }

    private function validateProfilePhoto(?array $file): array
    {
        $defaultName = basename(DEFAULT_PROFILE);
        $result = [
            'error' => null,
            'filename' => $defaultName,
            'temporary_path' => null,
            'extension' => null,
        ];

        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $result;
        }

        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if (in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            $result['error'] = 'Ukuran file maksimal 2 MB.';
            return $result;
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            $result['error'] = 'Terjadi kesalahan saat upload file.';
            return $result;
        }

        $temporaryPath = $file['tmp_name'] ?? '';
        $originalName = $file['name'] ?? '';
        $fileSize = $file['size'] ?? 0;
        $extension = strtolower(pathinfo((string) $originalName, PATHINFO_EXTENSION));

        if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
            $result['error'] = 'File foto profil tidak valid.';
            return $result;
        }

        if (!is_int($fileSize) || $fileSize < 1 || $fileSize > MAX_UPLOAD_SIZE) {
            $result['error'] = 'Ukuran file maksimal 2 MB.';
            return $result;
        }

        if (!in_array($extension, ALLOWED_IMAGE, true)) {
            $result['error'] = 'Format file harus JPG, JPEG, atau PNG.';
            return $result;
        }

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $fileInfo->file($temporaryPath);
        $expectedMime = $extension === 'png' ? 'image/png' : 'image/jpeg';

        if ($mime !== $expectedMime || @getimagesize($temporaryPath) === false) {
            $result['error'] = 'File gambar tidak valid.';
            return $result;
        }

        $result['temporary_path'] = $temporaryPath;
        $result['extension'] = $extension;

        return $result;
    }

    private function storeProfilePhoto(string $temporaryPath, string $extension): ?string
    {
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . UPLOAD_PROFILE;

        if (!is_dir($directory) || !is_writable($directory)) {
            return null;
        }

        $filename = 'profile_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $directory . $filename;

        return move_uploaded_file($temporaryPath, $destination) ? $destination : null;
    }

    private function removeStoredPhoto(?string $path): void
    {
        if ($path !== null && is_file($path) && !unlink($path)) {
            error_log('CampusHub failed to remove a temporary profile upload after registration failure.');
        }
    }

    private function logDatabaseFailure(mysqli_sql_exception | RuntimeException $exception): void
    {
        error_log('CampusHub authentication storage failed (' . get_class($exception) . ').');
    }
}
