<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Flash;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Core\Session;
use CampusHub\Models\StudentOperationsModel;
use CampusHub\Models\StudentProfileImage;
use mysqli_sql_exception;
use RuntimeException;

final class StudentController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $studentId = $this->studentId($request);
        if ($studentId === null) {
            return Response::text('Identitas akun mahasiswa tidak valid. Silakan login kembali.', 403);
        }
        try {
            $data = (new StudentOperationsModel())->dashboard($studentId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Data dashboard mahasiswa sedang tidak tersedia.', 503);
        }

        return $this->view('dashboard/student', $this->layoutData($request, 'dashboard') + [
            'stats' => $data['stats'],
            'events' => $data['events'],
            'registrations' => $data['registrations'],
        ]);
    }

    public function events(Request $request): Response
    {
        $studentId = $this->studentId($request);
        if ($studentId === null) {
            return Response::text('Identitas akun mahasiswa tidak valid. Silakan login kembali.', 403);
        }
        $filters = $this->eventFilters($request);
        $page = $this->pageNumber($request->input('page'));
        try {
            $model = new StudentOperationsModel();
            $result = $model->events($studentId, $filters, 12, ($page - 1) * 12);
            $categories = $model->categoryOptions();
            $organizations = $model->organizationOptions();
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Daftar event yang dapat diakses sedang tidak tersedia.', 503);
        }

        return $this->view('student/events', $this->layoutData($request, 'events') + [
            'events' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 12)),
            'filters' => $filters,
            'categories' => $categories,
            'organizations' => $organizations,
            'flash' => Flash::pull(),
        ]);
    }

    public function eventDetail(Request $request): Response
    {
        $studentId = $this->studentId($request);
        $eventId = $this->positiveId($request->input('id'));
        if ($studentId === null || $eventId === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }
        try {
            $event = (new StudentOperationsModel())->eventDetail($studentId, $eventId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Detail event sedang tidak tersedia.', 503);
        }
        if ($event === null) {
            return Response::text('Event tidak ditemukan atau belum dipublikasikan.', 404);
        }

        return $this->view('student/event-detail', $this->layoutData($request, 'events') + [
            'event' => $event,
            'flash' => Flash::pull(),
        ]);
    }

    public function register(Request $request): Response
    {
        $studentId = $this->studentId($request);
        $eventId = $this->positiveId($request->post('id_event'));
        if ($studentId === null || $eventId === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }
        try {
            $result = (new StudentOperationsModel())->register($studentId, $eventId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Pendaftaran event tidak dapat diproses.');
            return Response::redirect('/student/events/detail?id=' . $eventId);
        }
        $messages = [
            'registered' => ['Success', 'Pendaftaran berhasil. Kuota event Anda telah diamankan.'],
            'already_registered' => ['Warning', 'Anda sudah memiliki pendaftaran untuk event ini.'],
            'full' => ['Danger', 'Kuota event sudah penuh.'],
            'closed' => ['Warning', 'Pendaftaran event ini sudah ditutup atau event tidak lagi tersedia.'],
        ];
        [$type, $message] = $messages[$result] ?? ['Danger', 'Pendaftaran event tidak dapat diproses.'];
        Flash::set($type, $message);

        return Response::redirect('/student/events/detail?id=' . $eventId);
    }

    public function registrations(Request $request): Response
    {
        $studentId = $this->studentId($request);
        if ($studentId === null) {
            return Response::text('Identitas akun mahasiswa tidak valid. Silakan login kembali.', 403);
        }
        $page = $this->pageNumber($request->input('page'));
        try {
            $model = new StudentOperationsModel();
            $items = $model->myRegistrations($studentId, 20, ($page - 1) * 20);
            $total = $model->registrationCount($studentId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Daftar pendaftaran Anda sedang tidak tersedia.', 503);
        }

        return $this->view('student/registrations', $this->layoutData($request, 'registrations') + [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => max(1, (int) ceil($total / 20)),
            'flash' => Flash::pull(),
        ]);
    }

    public function cancelRegistration(Request $request): Response
    {
        $studentId = $this->studentId($request);
        $registrationId = $this->positiveId($request->post('id_pendaftaran'));
        if ($studentId === null || $registrationId === null) {
            return Response::text('Pendaftaran tidak ditemukan.', 404);
        }
        try {
            $result = (new StudentOperationsModel())->cancelRegistration($studentId, $registrationId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Pendaftaran tidak dapat dibatalkan.');
            return Response::redirect('/student/registrations');
        }
        Flash::set(
            $result === 'cancelled' ? 'Success' : 'Warning',
            $result === 'cancelled'
                ? 'Pendaftaran berhasil dibatalkan.'
                : 'Pendaftaran tidak dapat dibatalkan. Pendaftaran event mendatang yang masih dipublikasikan dapat dibatalkan.'
        );

        return Response::redirect('/student/registrations');
    }

    public function schedule(Request $request): Response
    {
        $studentId = $this->studentId($request);
        if ($studentId === null) {
            return Response::text('Identitas akun mahasiswa tidak valid. Silakan login kembali.', 403);
        }
        $upcomingPage = $this->pageNumber($request->input('upcoming_page'));
        $completedPage = $this->pageNumber($request->input('completed_page'));
        try {
            $model = new StudentOperationsModel();
            $upcoming = $model->schedule($studentId, 'upcoming', 20, ($upcomingPage - 1) * 20);
            $completed = $model->schedule($studentId, 'completed', 20, ($completedPage - 1) * 20);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Jadwal event Anda sedang tidak tersedia.', 503);
        }

        return $this->view('student/schedule', $this->layoutData($request, 'schedule') + [
            'upcoming' => $upcoming['items'],
            'upcomingTotal' => $upcoming['total'],
            'upcomingPage' => $upcomingPage,
            'upcomingPages' => max(1, (int) ceil($upcoming['total'] / 20)),
            'completed' => $completed['items'],
            'completedTotal' => $completed['total'],
            'completedPage' => $completedPage,
            'completedPages' => max(1, (int) ceil($completed['total'] / 20)),
        ]);
    }

    public function certificates(Request $request): Response
    {
        $studentId = $this->studentId($request);
        if ($studentId === null) {
            return Response::text('Identitas akun mahasiswa tidak valid. Silakan login kembali.', 403);
        }
        $page = $this->pageNumber($request->input('page'));
        try {
            $result = (new StudentOperationsModel())->certificates($studentId, 20, ($page - 1) * 20);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Daftar sertifikat Anda sedang tidak tersedia.', 503);
        }

        return $this->view('student/certificates', $this->layoutData($request, 'certificates') + [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 20)),
        ]);
    }

    public function profile(Request $request): Response
    {
        $studentId = $this->studentId($request);
        if ($studentId === null) {
            return Response::text('Identitas akun mahasiswa tidak valid. Silakan login kembali.', 403);
        }
        try {
            $user = (new StudentOperationsModel())->profile($studentId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Profil mahasiswa sedang tidak tersedia.', 503);
        }
        if ($user === null) {
            return Response::text('Akun mahasiswa tidak ditemukan.', 404);
        }

        return $this->view('student/profile', $this->layoutData($request, 'profile') + [
            'user' => $user,
            'flash' => Flash::pull(),
        ]);
    }

    public function updateProfile(Request $request): Response
    {
        $studentId = $this->studentId($request);
        $name = trim($this->queryString($request, 'nama'));
        $nim = trim($this->queryString($request, 'nim'));
        $email = trim($this->queryString($request, 'email'));
        $currentPassword = $request->post('current_password');
        $newPassword = $request->post('new_password');
        $confirmation = $request->post('confirm_password');
        $currentPassword = is_string($currentPassword) ? $currentPassword : '';
        $newPassword = is_string($newPassword) ? $newPassword : '';
        $confirmation = is_string($confirmation) ? $confirmation : '';
        $photoUpload = new StudentProfileImage();
        $file = $photoUpload->validate($request->file('foto'));
        if ($studentId === null || $name === '' || strlen($name) > 100
            || strlen($nim) > 20 || ($nim !== '' && preg_match('/\A[a-zA-Z0-9./-]+\z/', $nim) !== 1)
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 100
            || $file['error'] !== null) {
            Flash::set('Danger', $file['error'] ?? 'Nama, NIM, atau email tidak valid.');
            return Response::redirect('/student/profile');
        }
        $passwordUpdate = $newPassword !== '' || $confirmation !== '';
        if ($currentPassword === '' || ($passwordUpdate && (strlen($newPassword) < 8 || $newPassword !== $confirmation))) {
            Flash::set('Danger', 'Masukkan password saat ini. Password baru minimal 8 karakter dan konfirmasi harus sama.');
            return Response::redirect('/student/profile');
        }

        $newPhoto = null;
        try {
            if ($file['temporary_path'] !== null) {
                $newPhoto = $photoUpload->store($file['temporary_path'], $file['extension']);
            }
            $result = (new StudentOperationsModel())->updateProfile(
                $studentId,
                $name,
                $nim === '' ? null : $nim,
                $email,
                $currentPassword,
                $passwordUpdate ? $newPassword : null,
                $newPhoto
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if ($newPhoto !== null) {
                $photoUpload->removeStored($newPhoto);
            }
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', (int) $exception->getCode() === 1062
                ? 'Email atau NIM sudah digunakan akun lain.'
                : 'Profil mahasiswa tidak dapat diperbarui.');
            return Response::redirect('/student/profile');
        }
        if ($result['status'] !== 'updated') {
            if ($newPhoto !== null) {
                $photoUpload->removeStored($newPhoto);
            }
            Flash::set('Danger', $result['status'] === 'not_found'
                ? 'Akun mahasiswa tidak ditemukan.'
                : 'Password saat ini tidak cocok.');
            return Response::redirect('/student/profile');
        }

        Session::updateProfile($name, $email, $newPhoto);
        if (is_string($result['old_photo'])) {
            $photoUpload->removeStored($result['old_photo']);
        }
        Flash::set('Success', 'Profil mahasiswa berhasil diperbarui.');

        return Response::redirect('/student/profile');
    }

    private function studentId(Request $request): ?int
    {
        $id = filter_var($request->session('user_id'), FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function layoutData(Request $request, string $section): array
    {
        return [
            'userName' => (string) $request->session('nama', ''),
            'role' => ROLE_STUDENT,
            'photo' => (string) $request->session('foto', DEFAULT_PROFILE),
            'activeStudentSection' => $section,
        ];
    }

    private function eventFilters(Request $request): array
    {
        return [
            'q' => $this->queryString($request, 'q'),
            'category' => $this->queryString($request, 'category'),
            'organization' => $this->queryString($request, 'organization'),
            'from' => $this->dateString($request, 'from'),
            'to' => $this->dateString($request, 'to'),
        ];
    }

    private function queryString(Request $request, string $key): string
    {
        $value = $request->input($key, '');

        return is_string($value) ? trim($value) : '';
    }

    private function positiveId(mixed $value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function pageNumber(mixed $value): int
    {
        $page = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($page) && $page > 0 ? min($page, 100000) : 1;
    }

    private function dateString(Request $request, string $key): string
    {
        $value = $this->queryString($request, $key);
        if (preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $value) !== 1) {
            return '';
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $value : '';
    }

    private function logDatabaseFailure(mysqli_sql_exception|RuntimeException $exception): void
    {
        error_log('CampusHub Student operation failed (' . get_class($exception) . ').');
    }
}
