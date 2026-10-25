<?php

declare(strict_types=1);

namespace CampusHub\Models;

use RuntimeException;

final class CommitteeCertificateFile
{
    private const MAX_SIZE = 5 * 1024 * 1024;

    public function validate(?array $file): array
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['error' => 'Unggah file sertifikat PDF.', 'temporary_path' => null];
        }

        $temporaryPath = $file['tmp_name'] ?? null;
        if (
            ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_string($temporaryPath)
            || !is_uploaded_file($temporaryPath)
            || (int) ($file['size'] ?? 0) > self::MAX_SIZE
        ) {
            return ['error' => 'File sertifikat tidak valid atau melebihi batas 5 MB.', 'temporary_path' => null];
        }

        $handle = fopen($temporaryPath, 'rb');
        if ($handle === false) {
            return ['error' => 'File sertifikat tidak dapat diperiksa.', 'temporary_path' => null];
        }
        $signature = fread($handle, 5);
        fclose($handle);

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        if ($signature !== '%PDF-' || $mime !== 'application/pdf') {
            return ['error' => 'File sertifikat harus berupa PDF yang valid.', 'temporary_path' => null];
        }

        return ['error' => null, 'temporary_path' => $temporaryPath];
    }

    public function store(string $temporaryPath): string
    {
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . UPLOAD_SERTIFIKAT;
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new RuntimeException('Certificate upload directory could not be created.');
        }
        if (!is_writable($directory)) {
            throw new RuntimeException('Certificate upload directory is not writable.');
        }

        $filename = 'certificate_' . bin2hex(random_bytes(16)) . '.pdf';
        if (!move_uploaded_file($temporaryPath, $directory . $filename)) {
            throw new RuntimeException('Certificate file could not be stored.');
        }

        return $filename;
    }

    public function removeStored(string $filename): void
    {
        if (preg_match('/\Acertificate_[a-f0-9]{32}\.pdf\z/', $filename) !== 1) {
            return;
        }

        $path = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . UPLOAD_SERTIFIKAT
            . $filename;
        if (is_file($path) && !unlink($path)) {
            error_log('CampusHub failed to clean up a Committee certificate file.');
        }
    }
}
