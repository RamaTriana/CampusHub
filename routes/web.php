<?php

declare(strict_types=1);

use CampusHub\Controllers\HomeController;
use CampusHub\Controllers\AuthController;
use CampusHub\Controllers\CertificateController;
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
    $router->get('/admin/documentation', [AdminOperationsController::class, 'documentation'], $adminMiddleware);
    $router->post('/admin/documentation/hapus', [AdminOperationsController::class, 'deleteDocumentation'], $adminWriteMiddleware);
    $router->get('/admin/certificates', [AdminOperationsController::class, 'certificates'], $adminMiddleware);
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
    $committeeController = [CommitteeController::class, 'events'];
    $committeeMiddleware = ['auth', ['role', ROLE_COMMITTEE]];
    $committeeWriteMiddleware = ['auth', ['role', ROLE_COMMITTEE], 'csrf'];
    $router->get('/committee/events', $committeeController, $committeeMiddleware);
    $router->get('/committee/events/detail', [CommitteeController::class, 'eventDetail'], $committeeMiddleware);
    $router->get('/committee/participants', [CommitteeController::class, 'participants'], $committeeMiddleware);
    $router->get('/committee/participants/export', [CommitteeController::class, 'exportParticipants'], $committeeMiddleware);
    $router->get('/committee/attendance', [CommitteeController::class, 'attendance'], $committeeMiddleware);
    $router->post('/committee/attendance/update', [CommitteeController::class, 'updateAttendance'], $committeeWriteMiddleware);
    $router->get('/committee/documentation', [CommitteeController::class, 'documentation'], $committeeMiddleware);
    $router->post('/committee/documentation/save', [CommitteeController::class, 'saveDocumentation'], $committeeWriteMiddleware);
    $router->post('/committee/documentation/delete', [CommitteeController::class, 'deleteDocumentation'], $committeeWriteMiddleware);
    $router->get('/committee/certificates', [CommitteeController::class, 'certificates'], $committeeMiddleware);
    $router->post('/committee/certificates/issue', [CommitteeController::class, 'issueCertificate'], $committeeWriteMiddleware);
    $router->get('/committee/profile', [CommitteeController::class, 'profile'], $committeeMiddleware);
    $router->post('/committee/profile', [CommitteeController::class, 'updateProfile'], $committeeWriteMiddleware);
    $router->get('/student/dashboard', $studentDashboard, ['auth', ['role', ROLE_STUDENT]]);
    $router->get('/student/dashboard.php', $studentDashboard, ['auth', ['role', ROLE_STUDENT]]);
    $studentController = [StudentController::class, 'events'];
    $studentMiddleware = ['auth', ['role', ROLE_STUDENT]];
    $studentWriteMiddleware = ['auth', ['role', ROLE_STUDENT], 'csrf'];
    $router->get('/student/events', $studentController, $studentMiddleware);
    $router->get('/student/events/detail', [StudentController::class, 'eventDetail'], $studentMiddleware);
    $router->post('/student/registration/create', [StudentController::class, 'register'], $studentWriteMiddleware);
    $router->get('/student/registrations', [StudentController::class, 'registrations'], $studentMiddleware);
    $router->post('/student/registration/cancel', [StudentController::class, 'cancelRegistration'], $studentWriteMiddleware);
    $router->get('/student/schedule', [StudentController::class, 'schedule'], $studentMiddleware);
    $router->get('/student/certificates', [StudentController::class, 'certificates'], $studentMiddleware);
    $router->get('/student/profile', [StudentController::class, 'profile'], $studentMiddleware);
    $router->post('/student/profile', [StudentController::class, 'updateProfile'], $studentWriteMiddleware);

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
    $router->get('/certificates/download', [CertificateController::class, 'download'], [
        'auth',
        ['role', ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT],
    ]);

    $router->get('/', [AuthController::class, 'showLogin'], ['guest']);
};
