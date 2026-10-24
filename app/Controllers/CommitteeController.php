<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Models\CommitteeDashboardModel;
use mysqli_sql_exception;
use RuntimeException;

final class CommitteeController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $committeeId = filter_var($request->session('user_id'), FILTER_VALIDATE_INT);

        if (!is_int($committeeId) || $committeeId < 1) {
            return Response::text('Identitas akun panitia tidak valid. Silakan login kembali.', 403);
        }

        try {
            $dashboardData = (new CommitteeDashboardModel())->dashboardData($committeeId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);

            return Response::text('Data dashboard panitia sedang tidak dapat dimuat. Silakan coba lagi.', 503);
        }

        return $this->view('dashboard/committee', [
            'userName' => (string) $request->session('nama', ''),
            'userAvatar' => $this->avatarUrl((string) $request->session('foto', '')),
            'stats' => $dashboardData['stats'],
            'events' => $dashboardData['events'],
            'documentation' => $dashboardData['documentation'],
        ]);
    }

    private function avatarUrl(string $photo): string
    {
        $filename = basename($photo);

        if ($filename !== '' && preg_match('/\A[a-zA-Z0-9._-]+\.(?:jpg|jpeg|png)\z/i', $filename) === 1) {
            $profilePath = dirname(__DIR__, 2)
                . '/assets/uploads/profile/'
                . $filename;

            if (is_file($profilePath)) {
                return '/assets/uploads/profile/' . rawurlencode($filename);
            }
        }

        return '/public/assets/images/image.png';
    }

    private function logDatabaseFailure(mysqli_sql_exception|RuntimeException $exception): void
    {
        error_log('CampusHub committee dashboard failed (' . get_class($exception) . ').');
    }
}
