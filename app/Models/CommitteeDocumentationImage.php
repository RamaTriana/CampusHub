<?php

declare(strict_types=1);

namespace CampusHub\Models;

use RuntimeException;

final class CommitteeDocumentationImage
{
    public function validate(?array $file): array
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['error' => null, 'temporary_path' => null, 'extension' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['error' => 'Upload dokumentasi gagal. Ukuran maksimal 2 MB.', 'temporary_path' => null, 'extension' => null];
        }

        $temporaryPath = $file['tmp_name'] ?? null;
        $originalName = $file['name'] ?? null;
        if (!is_string($temporaryPath) || !is_string($originalName) || !is_uploaded_file($temporaryPath)) {
            return ['error' => 'File dokumentasi tidak valid.', 'temporary_path' => null, 'extension' => null];
        }

        $inspection = EventPoster::inspect($temporaryPath, $originalName);
        if ($inspection['error'] !== null) {
            return ['error' => $inspection['error'], 'temporary_path' => null, 'extension' => null];
        }

        return [
            'error' => null,
            'temporary_path' => $temporaryPath,
            'extension' => $inspection['extension'],
        ];
    }

    public function store(string $temporaryPath, string $extension): string
    {
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . UPLOAD_DOKUMENTASI;
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Documentation upload directory could not be created.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('Documentation upload directory is not writable.');
        }

        $filename = 'documentation_' . bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($temporaryPath, $directory . $filename)) {
            throw new RuntimeException('Documentation image could not be moved.');
        }

        return $filename;
    }

    public function removeStored(string $filename): void
    {
        if (preg_match('/\Adocumentation_[a-f0-9]{32}\.(?:jpg|jpeg|png)\z/', $filename) !== 1) {
            return;
        }
        $path = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . UPLOAD_DOKUMENTASI
            . $filename;
        if (is_file($path) && !unlink($path)) {
            error_log('CampusHub failed to clean up a Committee documentation image.');
        }
    }
}
