<?php

declare(strict_types=1);

use CampusHub\Controllers\HomeController;
use CampusHub\Controllers\AuthController;
use CampusHub\Controllers\AdminController;
use CampusHub\Controllers\AdminCategoryController;
use CampusHub\Controllers\AdminEventController;
use CampusHub\Controllers\AdminOrganizationController;
use CampusHub\Controllers\AdminOperationsController;
use CampusHub\Controllers\CommitteeController;
use CampusHub\Controllers\StudentController;
use CampusHub\Core\Router;

return static function (Router $router): void {
    $adminDashboard = [AdminController::class, 'dashboard'];
    $committeeDashboard = [CommitteeController::class, 'dashboard'];
    $studentDashboard = [StudentController::class, 'dashboard'];

    $router->get('/admin/dashboard', $adminDashboard, ['auth', ['role', ROLE_ADMIN]]);
    $router->get('/admin/dashboard.php', $adminDashboard, ['auth', ['role', ROLE_ADMIN]]);
    $operationsController = [AdminOperationsController::class, 'users'];
    $adminMiddleware = ['auth', ['role', ROLE_ADMIN]];
    $adminWriteMiddleware = ['auth', ['role', ROLE_ADMIN], 'csrf'];
    $router->get('/admin/users', $operationsController, $adminMiddleware);
    $router->get('/admin/users/detail', [AdminOperationsController::class, 'userDetail'], $adminMiddleware);
    $router->post('/admin/users/perbarui', [AdminOperationsController::class, 'updateUser'], $adminWriteMiddleware);
    $router->get('/admin/participants', [AdminOperationsController::class, 'participants'], $adminMiddleware);
    $router->get('/admin/participants/export', [AdminOperationsController::class, 'exportParticipants'], $adminMiddleware);
    $router->post('/admin/participants/status', [AdminOperationsController::class, 'updateParticipant'], $adminWriteMiddleware);
    $router->get('/admin/documentation', [AdminOperationsController::class, 'documentation'], $adminMiddleware);
    $router->post('/admin/documentation/hapus', [AdminOperationsController::class, 'deleteDocumentation'], $adminWriteMiddleware);
    $router->get('/admin/certificates', [AdminOperationsController::class, 'certificates'], $adminMiddleware);
    $router->post('/admin/certificates/cabut', [AdminOperationsController::class, 'revokeCertificate'], $adminWriteMiddleware);
    $router->get('/admin/reports', [AdminOperationsController::class, 'reports'], $adminMiddleware);
    $router->get('/admin/reports/export', [AdminOperationsController::class, 'exportReports'], $adminMiddleware);
    $router->get('/admin/settings', [AdminOperationsController::class, 'settings'], $adminMiddleware);
    $router->post('/admin/settings', [AdminOperationsController::class, 'updateSettings'], $adminWriteMiddleware);
    $categoryController = [AdminCategoryController::class, 'index'];
    $categoryMiddleware = ['auth', ['role', ROLE_ADMIN]];
    $categoryWriteMiddleware = ['auth', ['role', ROLE_ADMIN], 'csrf'];
    $router->get('/admin/kategori', $categoryController, $categoryMiddleware);
    $router->get('/admin/kategori/index.php', $categoryController, $categoryMiddleware);
    $router->get('/admin/kategori/tambah', [AdminCategoryController::class, 'create'], $categoryMiddleware);
    $router->get('/admin/kategori/edit', [AdminCategoryController::class, 'edit'], $categoryMiddleware);
    $router->post('/admin/kategori/simpan', [AdminCategoryController::class, 'store'], $categoryWriteMiddleware);
    $router->post('/admin/kategori/perbarui', [AdminCategoryController::class, 'update'], $categoryWriteMiddleware);
    $router->post('/admin/kategori/hapus', [AdminCategoryController::class, 'delete'], $categoryWriteMiddleware);
    $organizationController = [AdminOrganizationController::class, 'index'];
    $organizationMiddleware = ['auth', ['role', ROLE_ADMIN]];
    $organizationWriteMiddleware = ['auth', ['role', ROLE_ADMIN], 'csrf'];
    $router->get('/admin/organisasi', $organizationController, $organizationMiddleware);
    $router->get('/admin/organisasi/index.php', $organizationController, $organizationMiddleware);
    $router->get('/admin/organisasi/tambah', [AdminOrganizationController::class, 'create'], $organizationMiddleware);
    $router->get('/admin/organisasi/edit', [AdminOrganizationController::class, 'edit'], $organizationMiddleware);
    $router->post('/admin/organisasi/simpan', [AdminOrganizationController::class, 'store'], $organizationWriteMiddleware);
    $router->post('/admin/organisasi/perbarui', [AdminOrganizationController::class, 'update'], $organizationWriteMiddleware);
    $router->post('/admin/organisasi/hapus', [AdminOrganizationController::class, 'delete'], $organizationWriteMiddleware);
    $eventController = [AdminEventController::class, 'index'];
    $eventMiddleware = ['auth', ['role', ROLE_ADMIN]];
    $eventWriteMiddleware = ['auth', ['role', ROLE_ADMIN], 'csrf'];
    $router->get('/admin/event', $eventController, $eventMiddleware);
    $router->get('/admin/event/index.php', $eventController, $eventMiddleware);
    $router->get('/admin/event/tambah', [AdminEventController::class, 'create'], $eventMiddleware);
    $router->get('/admin/event/edit', [AdminEventController::class, 'edit'], $eventMiddleware);
    $router->get('/admin/event/detail', [AdminEventController::class, 'detail'], $eventMiddleware);
    $router->post('/admin/event/simpan', [AdminEventController::class, 'store'], $eventWriteMiddleware);
    $router->post('/admin/event/perbarui', [AdminEventController::class, 'update'], $eventWriteMiddleware);
    $router->post('/admin/event/hapus', [AdminEventController::class, 'delete'], $eventWriteMiddleware);
    $router->get('/committee/dashboard', $committeeDashboard, ['auth', ['role', ROLE_COMMITTEE]]);
    $router->get('/committee/dashboard.php', $committeeDashboard, ['auth', ['role', ROLE_COMMITTEE]]);
    $router->get('/student/dashboard', $studentDashboard, ['auth', ['role', ROLE_STUDENT]]);
    $router->get('/student/dashboard.php', $studentDashboard, ['auth', ['role', ROLE_STUDENT]]);

    $router->get('/login', [AuthController::class, 'showLogin'], ['guest']);
    $router->get('/login.php', [AuthController::class, 'showLogin'], ['guest']);
    $router->post('/login', [AuthController::class, 'login'], ['guest', 'csrf']);
    $router->post('/login_process.php', [AuthController::class, 'login'], ['guest', 'csrf']);

    $router->get('/register', [AuthController::class, 'showRegister'], ['guest']);
    $router->get('/register.php', [AuthController::class, 'showRegister'], ['guest']);
    $router->post('/register', [AuthController::class, 'register'], ['guest', 'csrf']);
    $router->post('/register_process.php', [AuthController::class, 'register'], ['guest', 'csrf']);

    $router->post('/logout', [AuthController::class, 'logout'], ['auth', 'csrf']);
    $router->get('/logout.php', [AuthController::class, 'confirmLogout'], ['auth']);

    $router->get('/', [HomeController::class, 'index']);
};
