<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;
use RuntimeException;

final class AdminOperationsModel
{
    private mysqli $connection;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $database = new \Database();
        $this->connection = $database->conn;
    }

    public function dashboard(): array
    {
        $summary = $this->connection->query(
            'SELECT
                (SELECT COUNT(*) FROM event) AS events,
                (SELECT COUNT(*) FROM event WHERE status = \'Publish\') AS published_events,
                (SELECT COUNT(*) FROM organisasi) AS organizations,
                (SELECT COUNT(*) FROM users) AS users,
                (SELECT COUNT(*) FROM pendaftaran) AS registrations,
                (SELECT COUNT(*) FROM pendaftaran WHERE status = \'Menunggu\') AS pending_registrations,
                (SELECT COUNT(*) FROM event WHERE status = \'Draft\') AS draft_events'
        )->fetch_assoc();

        if (!is_array($summary)) {
            throw new RuntimeException('Admin dashboard summary query returned no result.');
        }

        $recentEvents = $this->connection->query(
            'SELECT e.id_event, e.judul, e.tanggal, e.status, e.created_at,
                    o.nama_organisasi, k.nama_kategori
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event k ON k.id_kategori = e.id_kategori
             ORDER BY e.created_at DESC, e.id_event DESC
             LIMIT 6'
        )->fetch_all(MYSQLI_ASSOC);

        $upcomingEvents = $this->connection->query(
            'SELECT e.id_event, e.judul, e.tanggal, e.jam, e.lokasi,
                    o.nama_organisasi
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             WHERE e.status = \'Publish\' AND e.tanggal >= CURRENT_DATE()
             ORDER BY e.tanggal ASC, e.jam ASC
             LIMIT 5'
        )->fetch_all(MYSQLI_ASSOC);

        return [
            'stats' => array_map('intval', $summary),
            'recentEvents' => $recentEvents,
            'upcomingEvents' => $upcomingEvents,
        ];
    }

    public function users(array $filters, int $limit, int $offset): array
    {
        [$where, $types, $values] = $this->userFilterSql($filters);
        $countStatement = PreparedQuery::execute($this->connection, 
            'SELECT COUNT(*) AS total FROM users u' . $where,
            $values
        );
        $total = (int) $countStatement->fetch_assoc()['total'];
        $statement = $this->connection->prepare(
            'SELECT u.id_user, u.nama, u.nim, u.email, u.username, u.role, u.status,
                    u.created_at, o.nama_organisasi
             FROM users u
             LEFT JOIN organisasi o ON o.id_organisasi = u.id_organisasi'
                . $where .
            ' ORDER BY u.created_at DESC, u.id_user DESC LIMIT ? OFFSET ?'
        );
        $values[] = $limit;
        $values[] = $offset;
        $this->bind($statement, $types . 'ii', $values);

        try {
            $statement->execute();

            return ['items' => $statement->get_result()->fetch_all(MYSQLI_ASSOC), 'total' => $total];
        } finally {
            $statement->close();
        }
    }

    public function user(int $id): ?array
    {
        $result = PreparedQuery::execute($this->connection, 
            'SELECT u.id_user, u.id_organisasi, u.nama, u.nim, u.email, u.username,
                    u.role, u.foto, u.status, u.created_at, o.nama_organisasi,
                    (SELECT COUNT(*) FROM event e WHERE e.id_panitia = u.id_user) AS assigned_events
             FROM users u
             LEFT JOIN organisasi o ON o.id_organisasi = u.id_organisasi
             WHERE u.id_user = ? LIMIT 1',
            [$id]
        );
        $user = $result->fetch_assoc();

        return is_array($user) ? $user : null;
    }

    public function organizations(): array
    {
        return $this->connection->query(
            'SELECT id_organisasi, nama_organisasi FROM organisasi ORDER BY nama_organisasi'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function eventOptions(): array
    {
        return $this->connection->query(
            'SELECT id_event, judul FROM event ORDER BY tanggal DESC, id_event DESC'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function categoryOptions(): array
    {
        return $this->connection->query(
            'SELECT id_kategori, nama_kategori FROM kategori_event ORDER BY nama_kategori'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function updateUser(int $id, int $actorId, string $role, string $status, ?int $organizationId): string
    {
        $this->connection->begin_transaction();

        try {
            $targetResult = PreparedQuery::execute($this->connection, 
                'SELECT id_user, role, status FROM users WHERE id_user = ? FOR UPDATE',
                [$id]
            );
            $target = $targetResult->fetch_assoc();

            if (!is_array($target)) {
                $this->connection->rollback();
                return 'not_found';
            }

            if ($id === $actorId && ($role !== ROLE_ADMIN || $status !== STATUS_ACTIVE)) {
                $this->connection->rollback();
                return 'self_change';
            }

            if (($target['role'] === ROLE_ADMIN && $target['status'] === STATUS_ACTIVE)
                && ($role !== ROLE_ADMIN || $status !== STATUS_ACTIVE)) {
                $admins = $this->connection->query(
                    'SELECT id_user FROM users WHERE role = \'Admin\' AND status = \'Aktif\' FOR UPDATE'
                )->fetch_all(MYSQLI_ASSOC);

                if (count($admins) <= 1) {
                    $this->connection->rollback();
                    return 'last_admin';
                }
            }

            $statement = $this->connection->prepare(
                'UPDATE users SET role = ?, status = ?, id_organisasi = ? WHERE id_user = ?'
            );
            $statement->bind_param('ssii', $role, $status, $organizationId, $id);
            $statement->execute();
            $statement->close();
            $this->connection->commit();

            return 'updated';
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function registrations(array $filters, int $limit, int $offset): array
    {
        [$where, $values] = $this->registrationFilterSql($filters);
        $countResult = PreparedQuery::execute($this->connection, 
            'SELECT COUNT(*) AS total
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user'
                . $where,
            $values
        );
        $total = (int) $countResult->fetch_assoc()['total'];
        $statement = $this->connection->prepare(
            'SELECT p.id_pendaftaran, p.id_event, p.id_user, p.status,
                    p.tanggal_daftar, e.judul, e.tanggal AS tanggal_event,
                    u.nama, u.nim, u.email,
                    COALESCE(k.status_hadir, \'Belum check-in\') AS status_hadir,
                    k.waktu_checkin
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran'
                . $where .
            ' ORDER BY p.tanggal_daftar DESC, p.id_pendaftaran DESC LIMIT ? OFFSET ?'
        );
        $values[] = $limit;
        $values[] = $offset;
        $this->bind($statement, str_repeat('s', count($values) - 2) . 'ii', $values);

        try {
            $statement->execute();

            return ['items' => $statement->get_result()->fetch_all(MYSQLI_ASSOC), 'total' => $total];
        } finally {
            $statement->close();
        }
    }

    public function registrationExport(array $filters): array
    {
        [$where, $values] = $this->registrationFilterSql($filters);

        return PreparedQuery::execute($this->connection, 
            'SELECT p.id_pendaftaran, p.status, p.tanggal_daftar,
                    e.judul, e.tanggal AS tanggal_event,
                    u.nama, u.nim, u.email,
                    COALESCE(k.status_hadir, \'Belum check-in\') AS status_hadir,
                    k.waktu_checkin
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN users u ON u.id_user = p.id_user
             LEFT JOIN kehadiran k ON k.id_pendaftaran = p.id_pendaftaran'
                . $where .
            ' ORDER BY p.tanggal_daftar DESC, p.id_pendaftaran DESC',
            $values
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function updateRegistrationStatus(int $registrationId, string $status): string
    {
        $this->connection->begin_transaction();

        try {
            $result = PreparedQuery::execute($this->connection, 
                'SELECT p.id_event, p.status, e.kuota
                 FROM pendaftaran p
                 INNER JOIN event e ON e.id_event = p.id_event
                 WHERE p.id_pendaftaran = ? FOR UPDATE',
                [$registrationId]
            );
            $registration = $result->fetch_assoc();

            if (!is_array($registration)) {
                $this->connection->rollback();
                return 'not_found';
            }

            if ($status === STATUS_APPROVED && $registration['status'] !== STATUS_APPROVED) {
                $eventId = (int) $registration['id_event'];
                PreparedQuery::execute($this->connection, 
                    'SELECT id_event FROM event WHERE id_event = ? FOR UPDATE',
                    [$eventId]
                );
                $count = PreparedQuery::execute($this->connection, 
                    'SELECT COUNT(*) AS total FROM pendaftaran
                     WHERE id_event = ? AND status = ?',
                    [$eventId, STATUS_APPROVED]
                )->fetch_assoc();

                if ((int) $count['total'] >= (int) $registration['kuota']) {
                    $this->connection->rollback();
                    return 'capacity';
                }
            }

            PreparedQuery::execute($this->connection, 
                'UPDATE pendaftaran SET status = ? WHERE id_pendaftaran = ?',
                [$status, $registrationId]
            );
            $this->connection->commit();

            return 'updated';
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function documentation(array $filters): array
    {
        [$where, $values] = $this->relatedDateFilterSql('d.created_at', $filters, 'e.id_event');

        return PreparedQuery::execute($this->connection, 
            'SELECT d.id_dokumentasi, d.id_event, d.foto, d.keterangan, d.created_at,
                    e.judul, u.nama AS nama_panitia
             FROM dokumentasi d
             INNER JOIN event e ON e.id_event = d.id_event
             INNER JOIN users u ON u.id_user = e.id_panitia'
                . $where .
            ' ORDER BY d.created_at DESC, d.id_dokumentasi DESC',
            $values
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteDocumentation(int $id): ?string
    {
        $result = PreparedQuery::execute($this->connection, 
            'SELECT foto FROM dokumentasi WHERE id_dokumentasi = ?',
            [$id]
        );
        $record = $result->fetch_assoc();

        if (!is_array($record)) {
            return null;
        }

        PreparedQuery::execute($this->connection, 
            'DELETE FROM dokumentasi WHERE id_dokumentasi = ?',
            [$id]
        );

        return basename((string) $record['foto']);
    }

    public function certificates(array $filters): array
    {
        [$where, $values] = $this->relatedDateFilterSql('s.tanggal_upload', $filters, 'e.id_event');
        $status = $filters['status'] ?? '';
        if (in_array($status, ['Terbit', 'Dicabut'], true)) {
            $where .= $where === '' ? ' WHERE ' : ' AND ';
            $where .= 's.status = ?';
            $values[] = $status;
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $where .= $where === '' ? ' WHERE ' : ' AND ';
            $where .= '(u.nama LIKE ? OR u.nim LIKE ? OR u.email LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term);
        }

        return PreparedQuery::execute($this->connection,
            'SELECT s.id_sertifikat, s.file_sertifikat, s.status, s.tanggal_upload,
                    u.nama, u.nim, u.email, e.id_event, e.judul,
                    k.status_hadir, k.waktu_checkin
             FROM sertifikat s
             INNER JOIN kehadiran k ON k.id_kehadiran = s.id_kehadiran
             INNER JOIN pendaftaran p ON p.id_pendaftaran = k.id_pendaftaran
             INNER JOIN users u ON u.id_user = p.id_user
             INNER JOIN event e ON e.id_event = p.id_event'
                . $where .
            ' ORDER BY s.tanggal_upload DESC, s.id_sertifikat DESC',
            $values
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function revokeCertificate(int $id): array
    {
        $this->connection->begin_transaction();

        try {
            $certificate = PreparedQuery::execute(
                $this->connection,
                'SELECT file_sertifikat, status FROM sertifikat WHERE id_sertifikat = ? FOR UPDATE',
                [$id]
            )->fetch_assoc();

            if (!is_array($certificate)) {
                $this->connection->rollback();
                return ['status' => 'not_found', 'filename' => null];
            }

            if ($certificate['status'] === 'Dicabut') {
                $this->connection->rollback();
                return ['status' => 'already_revoked', 'filename' => null];
            }

            PreparedQuery::execute(
                $this->connection,
                'UPDATE sertifikat SET status = ? WHERE id_sertifikat = ?',
                ['Dicabut', $id]
            );
            $this->connection->commit();

            return [
                'status' => 'revoked',
                'filename' => basename((string) $certificate['file_sertifikat']),
            ];
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function report(array $filters): array
    {
        [$dateWhere, $dateValues] = $this->dateFilterSql('e.tanggal', $filters);

        return PreparedQuery::execute($this->connection, 
            'SELECT e.id_event, e.judul, e.tanggal, e.status, e.kuota,
                    o.nama_organisasi, k.nama_kategori, u.nama AS nama_panitia,
                    COUNT(DISTINCT CASE WHEN p.status = \'Disetujui\' THEN p.id_pendaftaran END) AS pendaftar_disetujui,
                    COUNT(DISTINCT CASE WHEN h.status_hadir = \'Hadir\' THEN h.id_kehadiran END) AS hadir,
                    COUNT(DISTINCT CASE WHEN s.status = \'Terbit\' THEN s.id_sertifikat END) AS sertifikat
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event k ON k.id_kategori = e.id_kategori
             INNER JOIN users u ON u.id_user = e.id_panitia
             LEFT JOIN pendaftaran p ON p.id_event = e.id_event
             LEFT JOIN kehadiran h ON h.id_pendaftaran = p.id_pendaftaran
             LEFT JOIN sertifikat s ON s.id_kehadiran = h.id_kehadiran'
                . $dateWhere .
            ' GROUP BY e.id_event, e.judul, e.tanggal, e.status, e.kuota,
                      o.nama_organisasi, k.nama_kategori, u.nama
              ORDER BY e.tanggal DESC, e.id_event DESC',
            $dateValues
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function updateProfile(int $id, string $name, string $email, string $currentPassword, ?string $newPassword): string
    {
        $result = PreparedQuery::execute($this->connection, 
            'SELECT password FROM users WHERE id_user = ? AND role = ?',
            [$id, ROLE_ADMIN]
        );
        $user = $result->fetch_assoc();

        if (!is_array($user)) {
            return 'not_found';
        }

        if (!password_verify($currentPassword, (string) $user['password'])) {
            return 'password';
        }

        $hash = $newPassword === null ? null : password_hash($newPassword, PASSWORD_DEFAULT);
        PreparedQuery::execute($this->connection, 
            'UPDATE users
             SET nama = ?, email = ?, password = COALESCE(?, password)
             WHERE id_user = ? AND role = ?',
            [$name, $email, $hash, $id, ROLE_ADMIN]
        );

        return 'updated';
    }

    private function userFilterSql(array $filters): array
    {
        $conditions = [];
        $values = [];
        $types = '';
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $conditions[] = '(u.nama LIKE ? OR u.nim LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR u.role LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term, $term, $term);
            $types .= 'sssss';
        }

        foreach (['role', 'status'] as $field) {
            if (in_array($filters[$field] ?? '', $field === 'role'
                ? [ROLE_ADMIN, ROLE_COMMITTEE, ROLE_STUDENT]
                : [STATUS_ACTIVE, STATUS_INACTIVE], true)) {
                $conditions[] = 'u.' . $field . ' = ?';
                $values[] = $filters[$field];
                $types .= 's';
            }
        }

        $organizationId = filter_var($filters['organization'] ?? null, FILTER_VALIDATE_INT);
        if (is_int($organizationId) && $organizationId > 0) {
            $conditions[] = 'u.id_organisasi = ?';
            $values[] = $organizationId;
            $types .= 'i';
        }

        return [$conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions), $types, $values];
    }

    private function registrationFilterSql(array $filters): array
    {
        $conditions = [];
        $values = [];
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $conditions[] = '(u.nama LIKE ? OR u.nim LIKE ? OR u.email LIKE ? OR e.judul LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term, $term);
        }

        $eventId = filter_var($filters['event'] ?? null, FILTER_VALIDATE_INT);
        if (is_int($eventId) && $eventId > 0) {
            $conditions[] = 'p.id_event = ?';
            $values[] = $eventId;
        }

        if (in_array($filters['status'] ?? '', [STATUS_PENDING, STATUS_APPROVED, STATUS_REJECTED], true)) {
            $conditions[] = 'p.status = ?';
            $values[] = $filters['status'];
        }

        foreach (['from' => 'DATE(p.tanggal_daftar) >= ?', 'to' => 'DATE(p.tanggal_daftar) <= ?'] as $key => $condition) {
            if ($this->validDate((string) ($filters[$key] ?? ''))) {
                $conditions[] = $condition;
                $values[] = $filters[$key];
            }
        }

        return [$conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions), $values];
    }

    private function relatedDateFilterSql(string $dateColumn, array $filters, string $eventIdColumn): array
    {
        $conditions = [];
        $values = [];
        $eventId = filter_var($filters['event'] ?? null, FILTER_VALIDATE_INT);

        if (is_int($eventId) && $eventId > 0) {
            $conditions[] = $eventIdColumn . ' = ?';
            $values[] = $eventId;
        }

        foreach (['from' => ' >= ?', 'to' => ' <= ?'] as $key => $operator) {
            if ($this->validDate((string) ($filters[$key] ?? ''))) {
                $conditions[] = 'DATE(' . $dateColumn . ')' . $operator;
                $values[] = $filters[$key];
            }
        }

        return [$conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions), $values];
    }

    private function dateFilterSql(string $dateColumn, array $filters): array
    {
        $conditions = [];
        $values = [];
        $status = $filters['status'] ?? '';

        if (in_array($status, ['Draft', 'Publish', 'Selesai'], true)) {
            $conditions[] = 'e.status = ?';
            $values[] = $status;
        }

        if (($filters['category'] ?? '') !== '') {
            $categoryId = filter_var($filters['category'], FILTER_VALIDATE_INT);
            if (is_int($categoryId) && $categoryId > 0) {
                $conditions[] = 'e.id_kategori = ?';
                $values[] = $categoryId;
            }
        }

        $organizationId = filter_var($filters['organization'] ?? null, FILTER_VALIDATE_INT);
        if (is_int($organizationId) && $organizationId > 0) {
            $conditions[] = 'e.id_organisasi = ?';
            $values[] = $organizationId;
        }

        foreach (['from' => ' >= ?', 'to' => ' <= ?'] as $key => $operator) {
            if ($this->validDate((string) ($filters[$key] ?? ''))) {
                $conditions[] = $dateColumn . $operator;
                $values[] = $filters[$key];
            }
        }

        return [$conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions), $values];
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function bind(\mysqli_stmt $statement, string $types, array $values): void
    {
        if ($types === '') {
            return;
        }

        $arguments = [$types];
        foreach ($values as $index => &$value) {
            $arguments[] = &$value;
        }
        unset($value);
        call_user_func_array([$statement, 'bind_param'], $arguments);
    }
}
