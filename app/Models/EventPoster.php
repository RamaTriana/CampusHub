<?php

declare(strict_types=1);

namespace CampusHub\Models;

use RuntimeException;

final class EventPoster
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
            return $this->invalid('Ukuran poster maksimal 2 MB.');
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            return $this->invalid('Upload poster gagal. Silakan coba lagi.');
        }

        $temporaryPath = $file['tmp_name'] ?? null;
        $originalName = $file['name'] ?? null;

        if (
            !is_string($temporaryPath)
            || !is_string($originalName)
            || !is_uploaded_file($temporaryPath)
        ) {
            return $this->invalid('File poster tidak valid.');
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

        if (!in_array($extension, ALLOWED_IMAGE, true)) {
            return ['error' => 'Format poster harus JPG, JPEG, atau PNG.', 'extension' => null];
        }

        if (!is_int($size) || $size < 1 || $size > MAX_UPLOAD_SIZE) {
            return ['error' => 'Ukuran poster maksimal 2 MB.', 'extension' => null];
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
        $directory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, UPLOAD_POSTER);

        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Event poster directory could not be created.');
        }

        if (!is_writable($directory)) {
            throw new RuntimeException('Event poster directory is not writable.');
        }

        $filename = 'event_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $directory . $filename;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Event poster upload could not be moved.');
        }

        return $filename;
    }

    public function removeStaged(string $filename): void
    {
        if (preg_match('/^event_[a-f0-9]{32}\.(?:jpg|jpeg|png)$/', $filename) !== 1) {
            return;
        }

        $directory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, UPLOAD_POSTER);
        $path = $directory . $filename;

        if (is_file($path) && !unlink($path)) {
            error_log('CampusHub failed to clean up a staged event poster.');
        }
    }

    private function invalid(string $message): array
    {
        return ['error' => $message, 'temporary_path' => null, 'extension' => null];
    }
}
