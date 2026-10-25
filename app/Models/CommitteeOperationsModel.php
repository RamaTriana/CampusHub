<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class CommitteeOperationsModel
{
    private mysqli $connection;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $database = new \Database();
        $this->connection = $database->conn;
    }

    public function events(int $committeeId, array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $conditions = ['e.id_panitia = ?'];
        $values = [$committeeId];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(e.judul LIKE ? OR o.nama_organisasi LIKE ? OR e.lokasi LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term);
        }
        if (in_array($filters['status'] ?? '', ['Draft', 'Publish', 'Selesai'], true)) {
            $conditions[] = 'e.status = ?';
            $values[] = $filters['status'];
        }
        $where = ' WHERE ' . implode(' AND ', $conditions);
        $total = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi' . $where,
            $values
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT e.id_event, e.judul, e.tanggal, e.jam, e.lokasi, e.kuota, e.status,
                    e.poster, e.deskripsi,
                    o.nama_organisasi, k.nama_kategori,
                    COUNT(DISTINCT p.id_pendaftaran) AS total_registrations,
                    COUNT(DISTINCT CASE WHEN p.status = \'Disetujui\' THEN p.id_pendaftaran END) AS approved_participants
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event k ON k.id_kategori = e.id_kategori
             LEFT JOIN pendaftaran p ON p.id_event = e.id_event' . $where . '
             GROUP BY e.id_event, e.judul, e.tanggal, e.jam, e.lokasi, e.kuota, e.status,
                      e.poster, e.deskripsi, o.nama_organisasi, k.nama_kategori
             ORDER BY CASE e.status WHEN \'Publish\' THEN 0 WHEN \'Draft\' THEN 1 ELSE 2 END,
                      e.tanggal ASC, e.jam ASC, e.id_event DESC
             LIMIT ? OFFSET ?',
            array_merge($values, [$limit, $offset])
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => (int) ($total['total'] ?? 0)];
    }

    public function event(int $committeeId, int $eventId): ?array
    {
        $event = PreparedQuery::execute(
            $this->connection,
            'SELECT e.id_event, e.judul, e.deskripsi, e.poster, e.tanggal, e.jam,
                    e.lokasi, e.kuota, e.status, o.nama_organisasi, o.logo,
                    k.nama_kategori,
                    COUNT(DISTINCT p.id_pendaftaran) AS total_registrations,
                    COUNT(DISTINCT CASE WHEN p.status = \'Disetujui\' THEN p.id_pendaftaran END) AS approved_participants,
                    COUNT(DISTINCT CASE WHEN p.status = \'Disetujui\' AND h.status_hadir = \'Hadir\' THEN h.id_kehadiran END) AS attended
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event k ON k.id_kategori = e.id_kategori
             LEFT JOIN pendaftaran p ON p.id_event = e.id_event
             LEFT JOIN kehadiran h ON h.id_pendaftaran = p.id_pendaftaran
             WHERE e.id_panitia = ? AND e.id_event = ?
             GROUP BY e.id_event, e.judul, e.deskripsi, e.poster, e.tanggal, e.jam,
                      e.lokasi, e.kuota, e.status, o.nama_organisasi, o.logo, k.nama_kategori',
            [$committeeId, $eventId]
        )->fetch_assoc();

        return is_array($event) ? $event : null;
    }

    public function eventOptions(int $committeeId): array
    {
        return PreparedQuery::execute(
            $this->connection,
            'SELECT id_event, judul FROM event WHERE id_panitia = ?
             ORDER BY tanggal DESC, id_event DESC',
            [$committeeId]
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function participants(
        int $committeeId,
        array $filters,
        int $limit = 25,
        int $offset = 0
    ): array {
        [$where, $values] = $this->participantFilterSql($committeeId, $filters);
        $total = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event' . $where,
            $values
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT p.id_pendaftaran, p.status, p.tanggal_daftar,
                    u.nama, u.nim, u.email, e.id_event, e.judul, e.tanggal AS tanggal_event,
                    COALESCE(k.status_hadir, \'Belum diperiksa\') AS status_hadir
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran'
                . $where .
            ' ORDER BY p.tanggal_daftar DESC, p.id_pendaftaran DESC
              LIMIT ? OFFSET ?',
            array_merge($values, [$limit, $offset])
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => (int) ($total['total'] ?? 0)];
    }

    public function participantExport(int $committeeId, array $filters): array
    {
        [$where, $values] = $this->participantFilterSql($committeeId, $filters);

        return PreparedQuery::execute(
            $this->connection,
            'SELECT p.id_pendaftaran, p.status, p.tanggal_daftar,
                    u.nama, u.nim, u.email, e.judul, e.tanggal AS tanggal_event,
                    COALESCE(k.status_hadir, \'Belum diperiksa\') AS status_hadir
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran'
                . $where .
            ' ORDER BY p.tanggal_daftar DESC, p.id_pendaftaran DESC',
            $values
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function attendance(int $committeeId, array $filters, int $limit, int $offset): array
    {
        $conditions = ['e.id_panitia = ?', 'p.status = ?'];
        $values = [$committeeId, STATUS_APPROVED];
        $eventId = filter_var($filters['event'] ?? '', FILTER_VALIDATE_INT);
        if (is_int($eventId) && $eventId > 0) {
            $conditions[] = 'e.id_event = ?';
            $values[] = $eventId;
        }
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(u.nama LIKE ? OR u.nim LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term);
        }
        $attendanceStatus = $filters['status'] ?? '';
        if (in_array($attendanceStatus, [ATTENDANCE_PRESENT, ATTENDANCE_ABSENT], true)) {
            $conditions[] = 'k.status_hadir = ?';
            $values[] = $attendanceStatus;
        } elseif ($attendanceStatus === 'Belum diperiksa') {
            $conditions[] = 'k.id_kehadiran IS NULL';
        }

        $where = ' WHERE ' . implode(' AND ', $conditions);
        $total = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran' . $where,
            $values
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT p.id_pendaftaran, p.id_event, u.nama, u.nim, u.email,
                    e.judul, e.tanggal AS tanggal_event,
                    k.id_kehadiran,
                    COALESCE(k.status_hadir, \'Belum diperiksa\') AS status_hadir,
                    k.waktu_checkin
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran
             ' . $where . '
             ORDER BY e.tanggal ASC, e.jam ASC, u.nama ASC
             LIMIT ? OFFSET ?',
            array_merge($values, [$limit, $offset])
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => (int) ($total['total'] ?? 0)];
    }

    public function attendanceSummary(int $committeeId, string $eventFilter = ''): array
    {
        $conditions = ['e.id_panitia = ?'];
        $values = [$committeeId];
        $eventId = filter_var($eventFilter, FILTER_VALIDATE_INT);
        if (is_int($eventId) && $eventId > 0) {
            $conditions[] = 'e.id_event = ?';
            $values[] = $eventId;
        }

        return PreparedQuery::execute(
            $this->connection,
            'SELECT e.id_event, e.judul,
                    COUNT(DISTINCT p.id_pendaftaran) AS approved,
                    COUNT(DISTINCT CASE WHEN k.status_hadir = \'Hadir\' THEN k.id_kehadiran END) AS present,
                    COUNT(DISTINCT CASE WHEN k.status_hadir = \'Tidak Hadir\' THEN k.id_kehadiran END) AS absent
             FROM event e
             LEFT JOIN pendaftaran p ON p.id_event = e.id_event AND p.status = ?
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran
             WHERE ' . implode(' AND ', $conditions) . '
             GROUP BY e.id_event, e.judul
             ORDER BY e.tanggal ASC, e.id_event DESC',
            array_merge([STATUS_APPROVED], $values)
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function updateAttendance(int $committeeId, int $registrationId, string $status): string
    {
        if (!in_array($status, [ATTENDANCE_PRESENT, ATTENDANCE_ABSENT], true)) {
            return 'invalid_status';
        }

        $this->connection->begin_transaction();
        try {
            $registration = PreparedQuery::execute(
                $this->connection,
                'SELECT p.id_pendaftaran
                 FROM pendaftaran p
                 INNER JOIN event e ON e.id_event = p.id_event
                 WHERE p.id_pendaftaran = ? AND e.id_panitia = ? AND p.status = ?
                 FOR UPDATE',
                [$registrationId, $committeeId, STATUS_APPROVED]
            )->fetch_assoc();
            if (!is_array($registration)) {
                $this->connection->rollback();
                return 'not_found';
            }

            $current = PreparedQuery::execute(
                $this->connection,
                'SELECT status_hadir FROM kehadiran WHERE id_pendaftaran = ? FOR UPDATE',
                [$registrationId]
            )->fetch_assoc();
            if (is_array($current) && $current['status_hadir'] === $status) {
                $this->connection->rollback();
                return 'unchanged';
            }

            PreparedQuery::execute(
                $this->connection,
                'INSERT INTO kehadiran (id_pendaftaran, status_hadir, waktu_checkin)
                 VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    status_hadir = VALUES(status_hadir),
                    waktu_checkin = VALUES(waktu_checkin)',
                [
                    $registrationId,
                    $status,
                    $status === ATTENDANCE_PRESENT ? date('Y-m-d H:i:s') : null,
                ]
            );
            $this->connection->commit();

            return 'updated';
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function documentation(int $committeeId, array $filters, int $limit, int $offset): array
    {
        $conditions = ['e.id_panitia = ?'];
        $values = [$committeeId];
        $eventId = filter_var($filters['event'] ?? '', FILTER_VALIDATE_INT);
        if (is_int($eventId) && $eventId > 0) {
            $conditions[] = 'e.id_event = ?';
            $values[] = $eventId;
        }
        $from = $filters['from'] ?? '';
        if ($this->validDate($from)) {
            $conditions[] = 'd.created_at >= ?';
            $values[] = $from . ' 00:00:00';
        }
        $to = $filters['to'] ?? '';
        if ($this->validDate($to)) {
            $conditions[] = 'd.created_at < DATE_ADD(?, INTERVAL 1 DAY)';
            $values[] = $to;
        }

        $where = ' WHERE ' . implode(' AND ', $conditions);
        $total = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total
             FROM dokumentasi d
             INNER JOIN event e ON e.id_event = d.id_event' . $where,
            $values
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT d.id_dokumentasi, d.id_event, d.foto, d.keterangan, d.created_at,
                    e.judul
             FROM dokumentasi d
             INNER JOIN event e ON e.id_event = d.id_event
             ' . $where . '
             ORDER BY d.created_at DESC, d.id_dokumentasi DESC
             LIMIT ? OFFSET ?',
            array_merge($values, [$limit, $offset])
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => (int) ($total['total'] ?? 0)];
    }

    public function saveDocumentation(
        int $committeeId,
        ?int $documentationId,
        int $eventId,
        ?string $filename,
        string $caption,
        string $createdAt
    ): array {
        $event = PreparedQuery::execute(
            $this->connection,
            'SELECT id_event FROM event WHERE id_event = ? AND id_panitia = ?',
            [$eventId, $committeeId]
        )->fetch_assoc();
        if (!is_array($event)) {
            return ['status' => 'event_not_found', 'old_filename' => null];
        }

        if ($documentationId === null) {
            if ($filename === null) {
                return ['status' => 'file_required', 'old_filename' => null];
            }
            PreparedQuery::execute(
                $this->connection,
                'INSERT INTO dokumentasi (id_event, foto, keterangan, created_at) VALUES (?, ?, ?, ?)',
                [$eventId, $filename, $caption, $createdAt]
            );

            return ['status' => 'created', 'old_filename' => null];
        }

        $existing = PreparedQuery::execute(
            $this->connection,
            'SELECT d.foto
             FROM dokumentasi d
             INNER JOIN event e ON e.id_event = d.id_event
             WHERE d.id_dokumentasi = ? AND e.id_panitia = ?',
            [$documentationId, $committeeId]
        )->fetch_assoc();
        if (!is_array($existing)) {
            return ['status' => 'not_found', 'old_filename' => null];
        }

        $query = $filename === null
            ? 'UPDATE dokumentasi SET id_event = ?, keterangan = ?, created_at = ? WHERE id_dokumentasi = ?'
            : 'UPDATE dokumentasi SET id_event = ?, foto = ?, keterangan = ?, created_at = ? WHERE id_dokumentasi = ?';
        $parameters = $filename === null
            ? [$eventId, $caption, $createdAt, $documentationId]
            : [$eventId, $filename, $caption, $createdAt, $documentationId];
        PreparedQuery::execute($this->connection, $query, $parameters);

        return [
            'status' => 'updated',
            'old_filename' => $filename === null ? null : basename((string) $existing['foto']),
        ];
    }

    public function deleteDocumentation(int $committeeId, int $documentationId): ?string
    {
        $existing = PreparedQuery::execute(
            $this->connection,
            'SELECT d.foto
             FROM dokumentasi d
             INNER JOIN event e ON e.id_event = d.id_event
             WHERE d.id_dokumentasi = ? AND e.id_panitia = ?',
            [$documentationId, $committeeId]
        )->fetch_assoc();
        if (!is_array($existing)) {
            return null;
        }

        PreparedQuery::execute(
            $this->connection,
            'DELETE d FROM dokumentasi d
             INNER JOIN event e ON e.id_event = d.id_event
             WHERE d.id_dokumentasi = ? AND e.id_panitia = ?',
            [$documentationId, $committeeId]
        );

        return basename((string) $existing['foto']);
    }

    public function certificates(int $committeeId, array $filters, int $limit, int $offset): array
    {
        $conditions = ['e.id_panitia = ?', 'p.status = ?'];
        $values = [$committeeId, STATUS_APPROVED];
        $eventId = filter_var($filters['event'] ?? '', FILTER_VALIDATE_INT);
        if (is_int($eventId) && $eventId > 0) {
            $conditions[] = 'e.id_event = ?';
            $values[] = $eventId;
        }
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(u.nama LIKE ? OR u.nim LIKE ? OR e.judul LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term);
        }
        $status = $filters['status'] ?? '';
        if ($status === 'Belum diterbitkan') {
            $conditions[] = 's.id_sertifikat IS NULL';
        } elseif (in_array($status, ['Terbit', 'Dicabut'], true)) {
            $conditions[] = 's.status = ?';
            $values[] = $status;
        }

        $where = ' WHERE ' . implode(' AND ', $conditions);
        $total = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran
             LEFT JOIN sertifikat s ON s.id_kehadiran = k.id_kehadiran' . $where,
            $values
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT u.nama, u.nim, e.id_event, e.judul,
                    p.id_pendaftaran,
                    COALESCE(k.status_hadir, \'Belum diperiksa\') AS status_hadir,
                    s.id_sertifikat, s.status AS status_sertifikat,
                    s.file_sertifikat, s.tanggal_upload, s.skkm_points
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran
             LEFT JOIN sertifikat s ON s.id_kehadiran = k.id_kehadiran
             ' . $where . '
             ORDER BY e.tanggal DESC, u.nama ASC
             LIMIT ? OFFSET ?',
            array_merge($values, [$limit, $offset])
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => (int) ($total['total'] ?? 0)];
    }

    public function issueCertificate(
        int $committeeId,
        int $registrationId,
        string $skkmPoints,
        string $filename
    ): string {
        $this->connection->begin_transaction();

        try {
            $attendance = PreparedQuery::execute(
                $this->connection,
                'SELECT k.id_kehadiran
                 FROM pendaftaran p
                 INNER JOIN event e ON e.id_event = p.id_event
                 INNER JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran
                 WHERE p.id_pendaftaran = ?
                   AND p.status = ?
                   AND k.status_hadir = ?
                   AND e.id_panitia = ?
                 FOR UPDATE',
                [$registrationId, STATUS_APPROVED, ATTENDANCE_PRESENT, $committeeId]
            )->fetch_assoc();

            if (!is_array($attendance)) {
                $this->connection->rollback();
                return 'not_eligible';
            }

            $existing = PreparedQuery::execute(
                $this->connection,
                'SELECT id_sertifikat FROM sertifikat WHERE id_kehadiran = ? FOR UPDATE',
                [(int) $attendance['id_kehadiran']]
            )->fetch_assoc();
            if (is_array($existing)) {
                $this->connection->rollback();
                return 'already_issued';
            }

            PreparedQuery::execute(
                $this->connection,
                'INSERT INTO sertifikat (id_kehadiran, file_sertifikat, skkm_points, status)
                 VALUES (?, ?, ?, \'Terbit\')',
                [(int) $attendance['id_kehadiran'], $filename, $skkmPoints]
            );
            $this->connection->commit();

            return 'issued';
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function profile(int $committeeId): ?array
    {
        $user = PreparedQuery::execute(
            $this->connection,
            'SELECT u.id_user, u.nama, u.nim, u.email, u.username, u.foto, u.status,
                    u.created_at, o.nama_organisasi
             FROM users u
             LEFT JOIN organisasi o ON o.id_organisasi = u.id_organisasi
             WHERE u.id_user = ? AND u.role = ?',
            [$committeeId, ROLE_COMMITTEE]
        )->fetch_assoc();

        return is_array($user) ? $user : null;
    }

    public function updateProfile(
        int $committeeId,
        string $name,
        string $email,
        string $currentPassword,
        ?string $newPassword
    ): string {
        $user = PreparedQuery::execute(
            $this->connection,
            'SELECT password FROM users WHERE id_user = ? AND role = ?',
            [$committeeId, ROLE_COMMITTEE]
        )->fetch_assoc();
        if (!is_array($user)) {
            return 'not_found';
        }
        if (!password_verify($currentPassword, (string) $user['password'])) {
            return 'invalid_password';
        }

        $query = $newPassword === null
            ? 'UPDATE users SET nama = ?, email = ? WHERE id_user = ? AND role = ?'
            : 'UPDATE users SET nama = ?, email = ?, password = ? WHERE id_user = ? AND role = ?';
        $parameters = $newPassword === null
            ? [$name, $email, $committeeId, ROLE_COMMITTEE]
            : [$name, $email, password_hash($newPassword, PASSWORD_DEFAULT), $committeeId, ROLE_COMMITTEE];
        PreparedQuery::execute($this->connection, $query, $parameters);

        return 'updated';
    }

    private function participantFilterSql(int $committeeId, array $filters): array
    {
        $conditions = ['e.id_panitia = ?'];
        $values = [$committeeId];
        $eventId = filter_var($filters['event'] ?? '', FILTER_VALIDATE_INT);
        if (is_int($eventId) && $eventId > 0) {
            $conditions[] = 'e.id_event = ?';
            $values[] = $eventId;
        }
        if (in_array($filters['status'] ?? '', [STATUS_PENDING, STATUS_APPROVED, STATUS_REJECTED], true)) {
            $conditions[] = 'p.status = ?';
            $values[] = $filters['status'];
        }
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(u.nama LIKE ? OR u.nim LIKE ? OR u.email LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term);
        }

        return [' WHERE ' . implode(' AND ', $conditions), $values];
    }

    private function validDate(mixed $value): bool
    {
        if (!is_string($value) || preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $value) !== 1) {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
