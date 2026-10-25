<?php

declare(strict_types=1);

namespace CampusHub\Controllers;

use CampusHub\Core\Controller;
use CampusHub\Core\Flash;
use CampusHub\Core\Request;
use CampusHub\Core\Response;
use CampusHub\Core\Session;
use CampusHub\Models\CommitteeCertificateFile;
use CampusHub\Models\CommitteeDashboardModel;
use CampusHub\Models\CommitteeDocumentationImage;
use CampusHub\Models\CommitteeOperationsModel;
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

        return $this->view('dashboard/committee', $this->layoutData($request, 'dashboard') + [
            'userName' => (string) $request->session('nama', ''),
            'stats' => $dashboardData['stats'],
            'events' => $dashboardData['events'],
            'documentation' => $dashboardData['documentation'],
        ]);
    }

    public function events(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        if ($committeeId === null) {
            return Response::text('Identitas akun panitia tidak valid. Silakan login kembali.', 403);
        }

        $filters = ['q' => $this->queryString($request, 'q'), 'status' => $this->queryString($request, 'status')];
        $page = $this->pageNumber($request->input('page'));
        try {
            $result = (new CommitteeOperationsModel())->events($committeeId, $filters, 20, ($page - 1) * 20);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Daftar event panitia sedang tidak tersedia.', 503);
        }

        return $this->view('committee/events', $this->layoutData($request, 'events') + [
            'events' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 20)),
            'filters' => $filters,
            'flash' => Flash::pull(),
        ]);
    }

    public function eventDetail(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        $eventId = $this->positiveId($request->input('id'));
        if ($committeeId === null || $eventId === null) {
            return Response::text('Event tidak ditemukan.', 404);
        }

        try {
            $event = (new CommitteeOperationsModel())->event($committeeId, $eventId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Detail event sedang tidak tersedia.', 503);
        }
        if ($event === null) {
            return Response::text('Event tidak ditemukan atau bukan penugasan akun ini.', 404);
        }

        return $this->view('committee/event-detail', $this->layoutData($request, 'events') + [
            'event' => $event,
        ]);
    }

    public function participants(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        if ($committeeId === null) {
            return Response::text('Identitas akun panitia tidak valid. Silakan login kembali.', 403);
        }

        $filters = $this->participantFilters($request);
        $page = $this->pageNumber($request->input('page'));
        try {
            $model = new CommitteeOperationsModel();
            $result = $model->participants($committeeId, $filters, 25, ($page - 1) * 25);
            $events = $model->eventOptions($committeeId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Data peserta sedang tidak tersedia.', 503);
        }

        return $this->view('committee/participants', $this->layoutData($request, 'participants') + [
            'participants' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 25)),
            'filters' => $filters,
            'events' => $events,
            'exportQuery' => http_build_query(array_filter($filters, static fn (string $value): bool => $value !== '')),
        ]);
    }

    public function exportParticipants(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        if ($committeeId === null) {
            return Response::text('Identitas akun panitia tidak valid.', 403);
        }
        try {
            $rows = (new CommitteeOperationsModel())->participantExport($committeeId, $this->participantFilters($request));
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Daftar peserta tidak dapat diekspor saat ini.', 503);
        }

        $output = fopen('php://temp', 'r+');
        if ($output === false) {
            return Response::text('File export tidak dapat dibuat.', 500);
        }
        fputcsv($output, ['Nama', 'NIM', 'Email', 'Event', 'Tanggal event', 'Tanggal daftar', 'Status pendaftaran', 'Kehadiran']);
        foreach ($rows as $row) {
            fputcsv($output, array_map([$this, 'csvValue'], [
                $row['nama'],
                $row['nim'],
                $row['email'],
                $row['judul'],
                $row['tanggal_event'],
                $row['tanggal_daftar'],
                $row['status'],
                $row['status_hadir'],
            ]));
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return new Response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="campushub-peserta-panitia.csv"',
        ]);
    }

    public function attendance(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        if ($committeeId === null) {
            return Response::text('Identitas akun panitia tidak valid. Silakan login kembali.', 403);
        }
        $filters = [
            'q' => $this->queryString($request, 'q'),
            'event' => $this->queryString($request, 'event'),
            'status' => $this->queryString($request, 'status'),
        ];
        $page = $this->pageNumber($request->input('page'));
        try {
            $model = new CommitteeOperationsModel();
            $result = $model->attendance($committeeId, $filters, 25, ($page - 1) * 25);
            $summary = $model->attendanceSummary($committeeId, $filters['event']);
            $events = $model->eventOptions($committeeId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Data kehadiran sedang tidak tersedia.', 503);
        }

        return $this->view('committee/attendance', $this->layoutData($request, 'attendance') + [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 25)),
            'summary' => $summary,
            'events' => $events,
            'filters' => $filters,
            'flash' => Flash::pull(),
        ]);
    }

    public function updateAttendance(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        $registrationId = $this->positiveId($request->post('id_pendaftaran'));
        $status = $this->queryString($request, 'status');
        if ($committeeId === null || $registrationId === null || !in_array($status, [ATTENDANCE_PRESENT, ATTENDANCE_ABSENT], true)) {
            Flash::set('Danger', 'Data kehadiran tidak valid.');
            return Response::redirect('/committee/attendance');
        }

        try {
            $result = (new CommitteeOperationsModel())->updateAttendance($committeeId, $registrationId, $status);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Kehadiran tidak dapat diperbarui.');
            return Response::redirect('/committee/attendance');
        }
        $messages = [
            'updated' => ['Success', 'Kehadiran berhasil diperbarui.'],
            'unchanged' => ['Warning', 'Status kehadiran peserta sudah tercatat seperti itu.'],
            'not_found' => ['Danger', 'Pendaftaran tidak ditemukan atau bukan event yang ditugaskan kepada Anda.'],
            'invalid_status' => ['Danger', 'Status kehadiran tidak valid.'],
        ];
        [$type, $message] = $messages[$result] ?? ['Danger', 'Kehadiran tidak dapat diperbarui.'];
        Flash::set($type, $message);

        return Response::redirect('/committee/attendance');
    }

    public function documentation(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        if ($committeeId === null) {
            return Response::text('Identitas akun panitia tidak valid. Silakan login kembali.', 403);
        }
        $filters = [
            'event' => $this->queryString($request, 'event'),
            'from' => $this->dateString($request, 'from'),
            'to' => $this->dateString($request, 'to'),
        ];
        $page = $this->pageNumber($request->input('page'));
        try {
            $model = new CommitteeOperationsModel();
            $result = $model->documentation($committeeId, $filters, 25, ($page - 1) * 25);
            $events = $model->eventOptions($committeeId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Dokumentasi event sedang tidak tersedia.', 503);
        }

        return $this->view('committee/documentation', $this->layoutData($request, 'documentation') + [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 25)),
            'events' => $events,
            'filters' => $filters,
            'flash' => Flash::pull(),
        ]);
    }

    public function saveDocumentation(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        $eventId = $this->positiveId($request->post('id_event'));
        $documentationId = $this->positiveId($request->post('id_dokumentasi'));
        $caption = trim($this->queryString($request, 'keterangan'));
        $createdAt = $this->documentationDate($this->queryString($request, 'created_at'));
        $upload = new CommitteeDocumentationImage();
        $file = $upload->validate($request->file('foto'));

        if ($committeeId === null || $eventId === null || $createdAt === null || strlen($caption) > 255 || $file['error'] !== null) {
            Flash::set('Danger', $file['error'] ?? 'Event, keterangan, atau tanggal dokumentasi tidak valid.');
            return Response::redirect('/committee/documentation');
        }
        if ($documentationId === null && $file['temporary_path'] === null) {
            Flash::set('Danger', 'Pilih foto dokumentasi untuk unggahan baru.');
            return Response::redirect('/committee/documentation');
        }

        $filename = null;
        try {
            if ($file['temporary_path'] !== null) {
                $filename = $upload->store($file['temporary_path'], $file['extension']);
            }
            $result = (new CommitteeOperationsModel())->saveDocumentation(
                $committeeId,
                $documentationId,
                $eventId,
                $filename,
                $caption,
                $createdAt
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if ($filename !== null) {
                $upload->removeStored($filename);
            }
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Dokumentasi tidak dapat disimpan.');
            return Response::redirect('/committee/documentation');
        }

        if (!in_array($result['status'], ['created', 'updated'], true)) {
            if ($filename !== null) {
                $upload->removeStored($filename);
            }
            Flash::set('Danger', $result['status'] === 'event_not_found'
                ? 'Event tidak ditemukan atau bukan penugasan akun ini.'
                : 'Dokumentasi tidak ditemukan atau file wajib diunggah.');
            return Response::redirect('/committee/documentation');
        }
        if (is_string($result['old_filename'])) {
            $upload->removeStored($result['old_filename']);
        }
        Flash::set('Success', $result['status'] === 'created' ? 'Dokumentasi berhasil diunggah.' : 'Dokumentasi berhasil diperbarui.');

        return Response::redirect('/committee/documentation');
    }

    public function deleteDocumentation(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        $documentationId = $this->positiveId($request->post('id_dokumentasi'));
        if ($committeeId === null || $documentationId === null) {
            return Response::text('Dokumentasi tidak ditemukan.', 404);
        }
        try {
            $filename = (new CommitteeOperationsModel())->deleteDocumentation($committeeId, $documentationId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Dokumentasi tidak dapat dihapus.');
            return Response::redirect('/committee/documentation');
        }
        if ($filename === null) {
            return Response::text('Dokumentasi tidak ditemukan atau bukan event yang ditugaskan kepada Anda.', 404);
        }
        (new CommitteeDocumentationImage())->removeStored($filename);
        Flash::set('Success', 'Dokumentasi berhasil dihapus.');

        return Response::redirect('/committee/documentation');
    }

    public function certificates(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        if ($committeeId === null) {
            return Response::text('Identitas akun panitia tidak valid. Silakan login kembali.', 403);
        }
        $filters = [
            'q' => $this->queryString($request, 'q'),
            'event' => $this->queryString($request, 'event'),
            'status' => $this->queryString($request, 'status'),
        ];
        $page = $this->pageNumber($request->input('page'));
        try {
            $model = new CommitteeOperationsModel();
            $result = $model->certificates($committeeId, $filters, 25, ($page - 1) * 25);
            $events = $model->eventOptions($committeeId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Data sertifikat sedang tidak tersedia.', 503);
        }

        return $this->view('committee/certificates', $this->layoutData($request, 'certificates') + [
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, (int) ceil($result['total'] / 25)),
            'events' => $events,
            'filters' => $filters,
        ]);
    }

    public function issueCertificate(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        $registrationId = $this->positiveId($request->post('id_pendaftaran'));
        $points = $this->queryString($request, 'skkm_points');
        $upload = new CommitteeCertificateFile();
        $file = $upload->validate($request->file('certificate'));

        if (
            $committeeId === null
            || $registrationId === null
            || preg_match('/\A\d{1,6}(?:\.\d{1,2})?\z/', $points) !== 1
            || (float) $points > 999999.99
            || $file['error'] !== null
        ) {
            Flash::set('Danger', $file['error'] ?? 'Nomor pendaftaran atau poin SKKM tidak valid.');
            return Response::redirect('/committee/certificates');
        }

        $filename = null;
        try {
            $filename = $upload->store($file['temporary_path']);
            $result = (new CommitteeOperationsModel())->issueCertificate(
                $committeeId,
                $registrationId,
                $points,
                $filename
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if ($filename !== null) {
                $upload->removeStored($filename);
            }
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', 'Sertifikat tidak dapat diterbitkan.');
            return Response::redirect('/committee/certificates');
        }

        if ($result !== 'issued') {
            if ($filename !== null) {
                $upload->removeStored($filename);
            }
            Flash::set(
                'Warning',
                $result === 'already_issued'
                    ? 'Sertifikat untuk peserta ini sudah diterbitkan.'
                    : 'Sertifikat hanya dapat diterbitkan kepada peserta hadir pada event yang Anda pegang.'
            );
            return Response::redirect('/committee/certificates');
        }

        Flash::set('Success', 'Sertifikat dan poin SKKM berhasil diterbitkan.');

        return Response::redirect('/committee/certificates');
    }

    public function profile(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        if ($committeeId === null) {
            return Response::text('Identitas akun panitia tidak valid. Silakan login kembali.', 403);
        }
        try {
            $user = (new CommitteeOperationsModel())->profile($committeeId);
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            return Response::text('Profil panitia sedang tidak tersedia.', 503);
        }
        if ($user === null) {
            return Response::text('Akun panitia tidak ditemukan.', 404);
        }

        return $this->view('committee/profile', $this->layoutData($request, 'profile') + [
            'user' => $user,
            'flash' => Flash::pull(),
        ]);
    }

    public function updateProfile(Request $request): Response
    {
        $committeeId = $this->committeeId($request);
        $name = trim($this->queryString($request, 'nama'));
        $email = trim($this->queryString($request, 'email'));
        $currentPassword = $request->post('current_password');
        $newPassword = $request->post('new_password');
        $confirmation = $request->post('confirm_password');
        $currentPassword = is_string($currentPassword) ? $currentPassword : '';
        $newPassword = is_string($newPassword) ? $newPassword : '';
        $confirmation = is_string($confirmation) ? $confirmation : '';
        if ($committeeId === null || $name === '' || strlen($name) > 100
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 100) {
            Flash::set('Danger', 'Nama atau email tidak valid.');
            return Response::redirect('/committee/profile');
        }
        $passwordUpdate = $newPassword !== '' || $confirmation !== '';
        if ($currentPassword === '' || ($passwordUpdate && (strlen($newPassword) < 8 || $newPassword !== $confirmation))) {
            Flash::set('Danger', 'Masukkan password saat ini. Password baru minimal 8 karakter dan konfirmasi harus sama.');
            return Response::redirect('/committee/profile');
        }
        try {
            $result = (new CommitteeOperationsModel())->updateProfile(
                $committeeId,
                $name,
                $email,
                $currentPassword,
                $passwordUpdate ? $newPassword : null
            );
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $this->logDatabaseFailure($exception);
            Flash::set('Danger', (int) $exception->getCode() === 1062
                ? 'Email sudah digunakan akun lain.'
                : 'Profil panitia tidak dapat diperbarui.');
            return Response::redirect('/committee/profile');
        }
        if ($result !== 'updated') {
            Flash::set('Danger', $result === 'not_found' ? 'Akun panitia tidak ditemukan.' : 'Password saat ini tidak cocok.');
            return Response::redirect('/committee/profile');
        }
        Session::updateProfile($name, $email);
        Flash::set('Success', 'Profil panitia berhasil diperbarui.');

        return Response::redirect('/committee/profile');
    }

    private function committeeId(Request $request): ?int
    {
        $id = filter_var($request->session('user_id'), FILTER_VALIDATE_INT);

        return is_int($id) && $id > 0 ? $id : null;
    }

    private function layoutData(Request $request, string $section): array
    {
        return [
            'userName' => (string) $request->session('nama', ''),
            'role' => ROLE_COMMITTEE,
            'photo' => (string) $request->session('foto', DEFAULT_PROFILE),
            'activeCommitteeSection' => $section,
        ];
    }

    private function participantFilters(Request $request): array
    {
        return [
            'q' => $this->queryString($request, 'q'),
            'event' => $this->queryString($request, 'event'),
            'status' => $this->queryString($request, 'status'),
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

    private function documentationDate(string $value): ?string
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);

        return $date !== false && $date->format('Y-m-d\TH:i') === $value
            ? $date->format('Y-m-d H:i:s')
            : null;
    }

    private function csvValue(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/\A[=+\-@\t\r]/', $value) === 1 ? "'" . $value : $value;
    }

    private function logDatabaseFailure(mysqli_sql_exception|RuntimeException $exception): void
    {
        error_log('CampusHub committee dashboard failed (' . get_class($exception) . ').');
    }
}
