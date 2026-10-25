<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Flash;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Models\AdminOperationsModel;
use mysqli_sql_exception;
use RuntimeException;

final class AdminOperationsController extends Controller
{
    public function users(Request $request): Response
    {
        $model = $this->model();
        if ($model instanceof Response) {
            return $model;
        }

        $filters = [
            'q' => $this->queryString($request, 'q'),
            'role' => $this->queryString($request, 'role'),
            'status' => $this->queryString($request, 'status'),
            'organization' => $this->queryString($request, 'organization'),
        ];
        $page = $this->pageNumber($request->input('page'));

        try {
            $result = $model->users($filters, 20, ($page - 1) * 20);
            $organizations = $model->organizations();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Data pengguna sedang tidak tersedia.', 503);
        }

        return $this->view('admin/operations/users', $this->layoutData($request, 'users') + [
            'users' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 20)),
            'filters' => $filters,
            'organizations' => $organizations,
            'flash' => Flash::pull(),
        ]);
    }

    public function userDetail(Request $request): Response
    {
        $id = $this->positiveId($request->input('id'));
        if ($id === null) {
            return Response::text('Pengguna tidak ditemukan.', 404);
        }

        try {
            $user = (new AdminOperationsModel())->user($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Detail pengguna sedang tidak tersedia.', 503);
        }

        if (!is_array($user)) {
            return Response::text('Pengguna tidak ditemukan.', 404);
        }

        return $this->view('admin/operations/user-detail', $this->layoutData($request, 'users') + [
            'user' => $user,
        ]);
    }

    public function updateUser(Request $request): Response
    {
        $id = $this->positiveId($request->post('id'));
        $actorId = $this->positiveId($request->session('user_id'));
        $role = $request->post('role');
        $status = $request->post('status');
        $organizationValue = $request->post('id_organisasi');
        $organizationId = $organizationValue === '' || $organizationValue === null
            ? null
            : $this->positiveId($organizationValue);

        if (
            $id === null
            || $actorId === null
            || !in_array($role, [ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT], true)
            || !in_array($status, [STATUS_ACTIVE, STATUS_INACTIVE], true)
            || ($organizationValue !== '' && $organizationValue !== null && $organizationId === null)
        ) {
            Flash::set('Danger', 'Perubahan pengguna tidak valid.');
            return Response::redirect('/admin/users');
        }

        try {
            $result = (new AdminOperationsModel())->updateUser(
                $id,
                $actorId,
                $role,
                $status,
                $organizationId
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            Flash::set('Danger', 'Data pengguna tidak dapat diperbarui.');
            return Response::redirect('/admin/users');
        }

        $messages = [
            'updated' => ['Success', 'Data pengguna berhasil diperbarui.'],
            'not_found' => ['Danger', 'Pengguna tidak ditemukan.'],
            'self_change' => ['Warning', 'Anda tidak dapat menonaktifkan akun sendiri atau mengubah role akun sendiri.'],
            'last_admin' => ['Warning', 'Admin aktif terakhir tidak dapat dinonaktifkan atau diturunkan role-nya.'],
        ];
        [$type, $message] = $messages[$result];
        Flash::set($type, $message);

        return Response::redirect('/admin/users');
    }

    public function participants(Request $request): Response
    {
        $model = $this->model();
        if ($model instanceof Response) {
            return $model;
        }

        $filters = $this->registrationFilters($request);
        $page = $this->pageNumber($request->input('page'));

        try {
            $result = $model->registrations($filters, 25, ($page - 1) * 25);
            $events = $model->eventOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Data pendaftaran sedang tidak tersedia.', 503);
        }

        return $this->view('admin/operations/participants', $this->layoutData($request, 'participants') + [
            'registrations' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 25)),
            'filters' => $filters,
            'events' => $events,
            'flash' => Flash::pull(),
        ]);
    }

    public function exportParticipants(Request $request): Response
    {
        try {
            $rows = (new AdminOperationsModel())->registrationExport($this->registrationFilters($request));
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Daftar peserta tidak dapat diekspor saat ini.', 503);
        }

        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            return Response::text('File peserta tidak dapat dibuat.', 500);
        }

        fputcsv($output, ['Event', 'Tanggal event', 'Nama peserta', 'NIM', 'Email', 'Tanggal daftar', 'Status pendaftaran', 'Kehadiran', 'Waktu check-in']);
        foreach ($rows as $row) {
            fputcsv($output, array_map([$this, 'csvValue'], [
                $row['judul'],
                $row['tanggal_event'],
                $row['nama'],
                $row['nim'],
                $row['email'],
                $row['tanggal_daftar'],
                $row['status'],
                $row['status_hadir'],
                $row['waktu_checkin'],
            ]));
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return new Response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="campushub-peserta.csv"',
        ]);
    }

    public function documentation(Request $request): Response
    {
        $model = $this->model();
        if ($model instanceof Response) {
            return $model;
        }

        $filters = $this->relatedFilters($request);
        $page = $this->pageNumber($request->input('page'));

        try {
            $result = $model->documentation($filters, 25, ($page - 1) * 25);
            $events = $model->eventOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Dokumentasi sedang tidak tersedia.', 503);
        }

        return $this->view('admin/operations/documentation', $this->layoutData($request, 'documentation') + [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 25)),
            'filters' => $filters,
            'events' => $events,
            'flash' => Flash::pull(),
        ]);
    }

    public function deleteDocumentation(Request $request): Response
    {
        $id = $this->positiveId($request->post('id'));
        if ($id === null) {
            return Response::text('Dokumentasi tidak ditemukan.', 404);
        }

        try {
            $filename = (new AdminOperationsModel())->deleteDocumentation($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            Flash::set('Danger', 'Dokumentasi tidak dapat dihapus.');
            return Response::redirect('/admin/documentation');
        }

        if ($filename === null) {
            return Response::text('Dokumentasi tidak ditemukan.', 404);
        }

        $path = dirname(__DIR__, 2) . '/assets/uploads/dokumentasi/' . $filename;
        if (is_file($path) && !unlink($path)) {
            error_log('CampusHub documentation file cleanup failed.');
        }
        Flash::set('Success', 'Dokumentasi berhasil dihapus.');

        return Response::redirect('/admin/documentation');
    }

    public function certificates(Request $request): Response
    {
        $model = $this->model();
        if ($model instanceof Response) {
            return $model;
        }

        $filters = $this->relatedFilters($request) + [
            'status' => $this->queryString($request, 'status'),
        ];
        $page = $this->pageNumber($request->input('page'));

        try {
            $result = $model->certificates($filters, 25, ($page - 1) * 25);
            $events = $model->eventOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Data sertifikat sedang tidak tersedia.', 503);
        }

        return $this->view('admin/operations/certificates', $this->layoutData($request, 'certificates') + [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 25)),
            'filters' => $filters,
            'events' => $events,
            'flash' => Flash::pull(),
        ]);
    }

    public function reports(Request $request): Response
    {
        $model = $this->model();
        if ($model instanceof Response) {
            return $model;
        }

        $filters = $this->reportFilters($request);

        try {
            $rows = $model->report($filters);
            $categories = $model->categoryOptions();
            $organizations = $model->organizations();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Laporan sedang tidak tersedia.', 503);
        }

        return $this->view('admin/operations/reports', $this->layoutData($request, 'reports') + [
            'rows' => $rows,
            'filters' => $filters,
            'categories' => $categories,
            'organizations' => $organizations,
        ]);
    }

    public function exportReports(Request $request): Response
    {
        $model = $this->model();
        if ($model instanceof Response) {
            return $model;
        }

        try {
            $rows = $model->report($this->reportFilters($request));
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Laporan tidak dapat diekspor saat ini.', 503);
        }

        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            return Response::text('File laporan tidak dapat dibuat.', 500);
        }
        fputcsv($output, ['Event', 'Tanggal', 'Status', 'Organisasi', 'Kategori', 'Panitia', 'Kuota', 'Pendaftar disetujui', 'Hadir', 'Sertifikat']);
        foreach ($rows as $row) {
            fputcsv($output, array_map([$this, 'csvValue'], [
                $row['judul'],
                $row['tanggal'],
                $row['status'],
                $row['nama_organisasi'],
                $row['nama_kategori'],
                $row['nama_panitia'],
                $row['kuota'],
                $row['pendaftar_disetujui'],
                $row['hadir'],
                $row['sertifikat'],
            ]));
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return new Response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="campushub-laporan.csv"',
        ]);
    }

    public function settings(Request $request): Response
    {
        $id = $this->positiveId($request->session('user_id'));
        if ($id === null) {
            return Response::text('Akun admin tidak valid.', 403);
        }

        try {
            $user = (new AdminOperationsModel())->user($id);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Pengaturan akun sedang tidak tersedia.', 503);
        }

        if (!is_array($user) || $user['role'] !== ROLE_ADMIN) {
            return Response::text('Akun admin tidak ditemukan.', 404);
        }

        return $this->view('admin/operations/settings', $this->layoutData($request, 'settings') + [
            'user' => $user,
            'flash' => Flash::pull(),
        ]);
    }

    public function updateSettings(Request $request): Response
    {
        $id = $this->positiveId($request->session('user_id'));
        $name = trim($this->queryString($request, 'nama'));
        $email = trim($this->queryString($request, 'email'));
        $currentPassword = $request->post('current_password');
        $newPassword = $request->post('new_password');
        $confirmation = $request->post('confirm_password');
        $currentPassword = is_string($currentPassword) ? $currentPassword : '';
        $newPassword = is_string($newPassword) ? $newPassword : '';
        $confirmation = is_string($confirmation) ? $confirmation : '';

        if (
            $id === null
            || $name === ''
            || strlen($name) > 100
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || strlen($email) > 100
        ) {
            Flash::set('Danger', 'Nama atau email tidak valid.');
            return Response::redirect('/admin/settings');
        }

        $passwordUpdate = $newPassword !== '' || $confirmation !== '';
        if (
            $currentPassword === ''
            || ($passwordUpdate && (strlen($newPassword) < 8 || $newPassword !== $confirmation))
        ) {
            Flash::set('Danger', 'Masukkan password saat ini. Password baru minimal 8 karakter dan konfirmasi harus sama.');
            return Response::redirect('/admin/settings');
        }

        try {
            $result = (new AdminOperationsModel())->updateProfile(
                $id,
                $name,
                $email,
                $currentPassword,
                $passwordUpdate ? $newPassword : null
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            Flash::set('Danger', (int) $exception->getCode() === 1062
                ? 'Email sudah digunakan akun lain.'
                : 'Profil admin tidak dapat diperbarui.');
            return Response::redirect('/admin/settings');
        }

        if ($result !== 'updated') {
            Flash::set('Danger', 'Password saat ini tidak cocok atau akun admin tidak ditemukan.');
            return Response::redirect('/admin/settings');
        }

        $_SESSION['nama'] = $name;
        $_SESSION['email'] = $email;
        Flash::set('Success', 'Pengaturan akun berhasil diperbarui.');

        return Response::redirect('/admin/settings');
    }

    private function model(): AdminOperationsModel|Response
    {
        try {
            return new AdminOperationsModel();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logFailure($exception);
            return Response::text('Layanan administrasi sedang tidak tersedia.', 503);
        }
    }

    private function layoutData(Request $request, string $section): array
    {
        return [
            'userName' => (string) $request->session('nama', ''),
            'role' => (string) $request->session('role', ''),
            'photo' => (string) $request->session('foto', basename(DEFAULT_PROFILE)),
            'activeAdminSection' => $section,
        ];
    }

    private function registrationFilters(Request $request): array
    {
        return [
            'q' => $this->queryString($request, 'q'),
            'event' => $this->queryString($request, 'event'),
            'status' => $this->queryString($request, 'status'),
            'from' => $this->queryString($request, 'from'),
            'to' => $this->queryString($request, 'to'),
        ];
    }

    private function relatedFilters(Request $request): array
    {
        return [
            'q' => $this->queryString($request, 'q'),
            'event' => $this->queryString($request, 'event'),
            'from' => $this->queryString($request, 'from'),
            'to' => $this->queryString($request, 'to'),
        ];
    }

    private function reportFilters(Request $request): array
    {
        return $this->relatedFilters($request) + [
            'status' => $this->queryString($request, 'status'),
            'category' => $this->queryString($request, 'category'),
            'organization' => $this->queryString($request, 'organization'),
        ];
    }

    private function queryString(Request $request, string $key): string
    {
        $value = $request->input($key, '');

        return is_string($value) ? trim($value) : '';
    }

    private function pageNumber(mixed $value): int
    {
        $page = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($page) && $page > 0 ? min($page, 100000) : 1;
    }

    private function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function csvValue(mixed $value): string|int
    {
        if (!is_string($value)) {
            return is_int($value) ? $value : '';
        }

        return preg_match('/^[\t\r ]*[=+\-@]/', $value) === 1 ? "'" . $value : $value;
    }

    private function logFailure(mysqli_sql_exception|RuntimeException $exception): void
    {
        error_log('CampusHub admin operations failed (' . get_class($exception) . ').');
    }
}
