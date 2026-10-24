<?php

declare(strict_types=1);

namespace CampusHub\Models;

use RuntimeException;

final class OrganizationLogo
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

        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if (in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return $this->invalid('Ukuran logo maksimal 2 MB.');
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            return $this->invalid('Upload logo gagal. Silakan coba lagi.');
        }

        $temporaryPath = $file['tmp_name'] ?? null;
        $originalName = $file['name'] ?? null;

        if (
            !is_string($temporaryPath)
            || !is_string($originalName)
            || !is_uploaded_file($temporaryPath)
        ) {
            return $this->invalid('File logo tidak valid.');
        }

        $inspection = self::inspect($temporaryPath, $originalName);

        if ($inspection['error'] !== null) {
            return $this->invalid($inspection['error']);
        }

        return [
            'error' => null,
            'temporary_path' => $temporaryPath,
            'extension' => $inspection['extension'],
        ];
    }

    public static function inspect(string $path, string $originalName): array
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $size = is_file($path) ? filesize($path) : false;

        if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            return ['error' => 'Format logo harus JPG, JPEG, atau PNG.', 'extension' => null];
        }

        if (!is_int($size) || $size < 1 || $size > MAX_UPLOAD_SIZE) {
            return ['error' => 'Ukuran logo maksimal 2 MB.', 'extension' => null];
        }

        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $fileInfo->file($path);
        $imageInfo = @getimagesize($path);

        if (
            !is_string($mime)
            || !isset(self::MIME_EXTENSIONS[$mime])
            || !in_array($extension, self::MIME_EXTENSIONS[$mime], true)
            || !is_array($imageInfo)
            || ($imageInfo['mime'] ?? null) !== $mime
        ) {
            return ['error' => 'Isi file bukan gambar JPG atau PNG yang valid.', 'extension' => null];
        }

        return ['error' => null, 'extension' => $extension];
    }

    public function store(string $temporaryPath, string $extension): string
    {
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . UPLOAD_ORGANISASI;

        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Organization logo directory could not be created.');
        }

        if (!is_writable($directory)) {
            throw new RuntimeException('Organization logo directory is not writable.');
        }

        $filename = 'organization_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $directory . $filename;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Organization logo upload could not be moved.');
        }

        return $filename;
    }

    public function removeStored(string $filename): void
    {
        if (preg_match('/^organization_[a-f0-9]{32}\.(?:jpg|jpeg|png)$/', $filename) !== 1) {
            return;
        }

        $path = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . UPLOAD_ORGANISASI
            . $filename;

        if (is_file($path) && !unlink($path)) {
            error_log('CampusHub failed to clean up a staged organization logo.');
        }
    }

    private function invalid(string $message): array
    {
        return ['error' => $message, 'temporary_path' => null, 'extension' => null];
    }
}
