<?php

declare(strict_types=1);

namespace CampusHub\Models;

use RuntimeException;

final class StudentProfileImage
{
    private const MIME_EXTENSIONS = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
    ];

    public function validate(?array $file): array
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['error' => null, 'temporary_path' => null, 'extension' => null];
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload foto profil gagal. Ukuran maksimal 2 MB.', 'temporary_path' => null, 'extension' => null];
        }

        $path = $file['tmp_name'] ?? null;
        $name = $file['name'] ?? null;
        if (!is_string($path) || !is_string($name) || !is_uploaded_file($path)) {
            return ['error' => 'File foto profil tidak valid.', 'temporary_path' => null, 'extension' => null];
        }

        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $size = filesize($path);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $image = @getimagesize($path);
        if (!in_array($extension, ALLOWED_IMAGE, true)
            || !is_int($size) || $size < 1 || $size > MAX_UPLOAD_SIZE
            || !is_string($mime) || !isset(self::MIME_EXTENSIONS[$mime])
            || !in_array($extension, self::MIME_EXTENSIONS[$mime], true)
            || !is_array($image) || ($image['mime'] ?? null) !== $mime) {
            return ['error' => 'Foto harus berupa gambar JPG atau PNG yang valid dengan ukuran maksimal 2 MB.', 'temporary_path' => null, 'extension' => null];
        }

        return ['error' => null, 'temporary_path' => $path, 'extension' => $extension];
    }

    public function store(string $temporaryPath, string $extension): string
    {
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . UPLOAD_PROFILE;
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Profile image directory could not be created.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('Profile image directory is not writable.');
        }
        $filename = 'student_' . bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($temporaryPath, $directory . $filename)) {
            throw new RuntimeException('Profile image could not be moved.');
        }

        return $filename;
    }

    public function removeStored(string $filename): void
    {
        if (preg_match('/\Astudent_[a-f0-9]{32}\.(?:jpg|jpeg|png)\z/', $filename) !== 1) {
            return;
        }
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . UPLOAD_PROFILE . $filename;
        if (is_file($path) && !unlink($path)) {
            error_log('CampusHub failed to clean up a Student profile image.');
        }
    }
}
