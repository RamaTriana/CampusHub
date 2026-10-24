<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Request;
use CampusHub\Core\Response;

final class StudentController extends Controller
{
    public function dashboard(Request $request): Response
    {
        return $this->view('dashboard/student', [
            'userName' => (string) $request->session('nama', ''),
        ]);
    }
}
