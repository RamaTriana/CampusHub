<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Models\CertificateAccessModel;
use mysqli_sql_exception;
use RuntimeException;

final class CertificateController extends Controller
{
    public function download(Request $request): Response
    {
        $certificateId = filter_var($request->input('id'), FILTER_VALIDATE_INT);
        $userId = filter_var($request->session('user_id'), FILTER_VALIDATE_INT);
        $role = $request->session('role');
        if (!is_int($certificateId) || $certificateId < 1
            || !is_int($userId) || $userId < 1) {
            return Response::text('Sertifikat tidak ditemukan.', 404);
        }
        if (!is_string($role) || !in_array($role, [ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT], true)) {
            return Response::text('Akun ini tidak berwenang mengakses sertifikat.', 403);
        }

        try {
            $filename = (new CertificateAccessModel())->filename($certificateId, $userId, $role);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            error_log('CampusHub certificate download failed (' . get_class($exception) . ').');
            return Response::text('Sertifikat sedang tidak dapat diakses.', 503);
        }
        if ($filename === null || preg_match('/\A[a-zA-Z0-9._-]+\.pdf\z/i', $filename) !== 1) {
            return Response::text('Sertifikat tidak ditemukan atau tidak dapat diakses oleh akun ini.', 404);
        }

        $directory = realpath(dirname(__DIR__, 2) . '/assets/uploads/sertifikat');
        $path = $directory === false ? false : realpath($directory . DIRECTORY_SEPARATOR . $filename);
        if ($directory === false || $path === false
            || strcasecmp(dirname($path), $directory) !== 0
            || !is_file($path) || !is_readable($path)) {
            return Response::text('File sertifikat belum tersedia.', 404);
        }
        $contents = file_get_contents($path);
        if (!is_string($contents)) {
            return Response::text('File sertifikat tidak dapat dibaca.', 500);
        }

        return new Response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
