<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Models\AdminOperationsModel;
use mysqli_sql_exception;
use RuntimeException;

final class AdminController extends Controller
{
    public function dashboard(Request $request): Response
    {
        try {
            $dashboard = (new AdminOperationsModel())->dashboard();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            error_log('CampusHub admin dashboard failed (' . get_class($exception) . ').');
            return Response::text('Ringkasan administrasi sedang tidak tersedia.', 503);
        }

        return $this->view('dashboard/admin', [
            'userName' => (string) $request->session('nama', ''),
            'role' => (string) $request->session('role', ''),
            'photo' => (string) $request->session('foto', basename(DEFAULT_PROFILE)),
            'activeAdminSection' => 'dashboard',
        ] + $dashboard);
    }
}
