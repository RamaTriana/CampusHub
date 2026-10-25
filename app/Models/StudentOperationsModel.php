<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class StudentOperationsModel
{
    private mysqli $connection;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $database = new \Database();
        $this->connection = $database->conn;
    }

    public function dashboard(int $studentId): array
    {
        $summary = PreparedQuery::execute(
            $this->connection,
            'SELECT
                (SELECT COUNT(*) FROM pendaftaran WHERE id_user = ?) AS registrations,
                (SELECT COUNT(*)
                 FROM sertifikat s
                 INNER JOIN kehadiran h ON h.id_kehadiran = s.id_kehadiran
                 INNER JOIN pendaftaran p ON p.id_pendaftaran = h.id_pendaftaran
                 WHERE p.id_user = ? AND s.status = \'Terbit\') AS certificates,
                (SELECT COUNT(*)
                 FROM pendaftaran p
                 INNER JOIN event e ON e.id_event = p.id_event
                 WHERE p.id_user = ? AND p.status = \'Disetujui\'
                   AND e.tanggal >= CURRENT_DATE() AND e.status = \'Publish\') AS upcoming',
            [$studentId, $studentId, $studentId]
        )->fetch_assoc();

        return [
            'stats' => [
                'registrations' => (int) ($summary['registrations'] ?? 0),
                'certificates' => (int) ($summary['certificates'] ?? 0),
                'upcoming' => (int) ($summary['upcoming'] ?? 0),
            ],
            'events' => $this->events($studentId, ['available_only' => true], 6, 0)['items'],
            'registrations' => $this->myRegistrations($studentId, 6),
        ];
    }

    public function events(int $studentId, array $filters, int $limit = 25, int $offset = 0): array
    {
        [$where, $values] = $this->eventFilterSql($filters);
        $count = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event c ON c.id_kategori = e.id_kategori' . $where,
            $values
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT e.id_event, e.judul, e.deskripsi, e.tanggal, e.jam, e.lokasi, e.kuota,
                    e.poster, e.status, o.nama_organisasi, c.nama_kategori,
                    COUNT(DISTINCT CASE WHEN approved.status IN (\'Menunggu\', \'Disetujui\') THEN approved.id_pendaftaran END) AS approved_count,
                    own.id_pendaftaran, own.status AS registration_status
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event c ON c.id_kategori = e.id_kategori
             LEFT JOIN pendaftaran approved ON approved.id_event = e.id_event
             LEFT JOIN pendaftaran own ON own.id_event = e.id_event AND own.id_user = ?'
                . $where .
            ' GROUP BY e.id_event, e.judul, e.deskripsi, e.tanggal, e.jam, e.lokasi, e.kuota,
                      e.poster, e.status, o.nama_organisasi, c.nama_kategori,
                      own.id_pendaftaran, own.status
              ORDER BY e.tanggal ASC, e.jam ASC, e.id_event DESC
              LIMIT ? OFFSET ?',
            array_merge([$studentId], $values, [$limit, $offset])
        )->fetch_all(MYSQLI_ASSOC);

        foreach ($items as &$item) {
            $item['remaining'] = max(0, (int) $item['kuota'] - (int) $item['approved_count']);
            $item['poster_url'] = $this->posterUrl((string) ($item['poster'] ?? ''));
        }
        unset($item);

        return ['items' => $items, 'total' => (int) ($count['total'] ?? 0)];
    }

    public function eventOptions(): array
    {
        return PreparedQuery::execute(
            $this->connection,
            'SELECT id_event, judul FROM event WHERE status = \'Publish\'
             ORDER BY tanggal ASC, jam ASC'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function categoryOptions(): array
    {
        return PreparedQuery::execute(
            $this->connection,
            'SELECT id_kategori, nama_kategori
             FROM kategori_event
             WHERE id_kategori IN (
                 SELECT DISTINCT id_kategori FROM event
                 WHERE status = \'Publish\' AND tanggal >= CURRENT_DATE()
             )
             ORDER BY nama_kategori'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function organizationOptions(): array
    {
        return PreparedQuery::execute(
            $this->connection,
            'SELECT DISTINCT o.id_organisasi, o.nama_organisasi
             FROM organisasi o
             INNER JOIN event e ON e.id_organisasi = o.id_organisasi
             WHERE e.status = \'Publish\' AND e.tanggal >= CURRENT_DATE()
             ORDER BY o.nama_organisasi'
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function eventDetail(int $studentId, int $eventId): ?array
    {
        $event = PreparedQuery::execute(
            $this->connection,
            'SELECT e.id_event, e.judul, e.deskripsi, e.poster, e.tanggal,
                    e.jam, e.lokasi, e.kuota, o.nama_organisasi, o.logo,
                    c.nama_kategori, own.id_pendaftaran,
                    own.status AS registration_status,
                    own.tanggal_daftar,
                    COUNT(DISTINCT CASE WHEN approved.status IN (\'Menunggu\', \'Disetujui\') THEN approved.id_pendaftaran END) AS approved_count
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event c ON c.id_kategori = e.id_kategori
             LEFT JOIN pendaftaran approved ON approved.id_event = e.id_event
             LEFT JOIN pendaftaran own ON own.id_event = e.id_event AND own.id_user = ?
             WHERE e.id_event = ? AND e.status = \'Publish\'
             GROUP BY e.id_event, e.judul, e.deskripsi, e.poster, e.tanggal,
                      e.jam, e.lokasi, e.kuota, o.nama_organisasi, o.logo,
                      c.nama_kategori, own.id_pendaftaran, own.status, own.tanggal_daftar',
            [$studentId, $eventId]
        )->fetch_assoc();
        if (!is_array($event)) {
            return null;
        }

        $event['remaining'] = max(0, (int) $event['kuota'] - (int) $event['approved_count']);
        $event['poster_url'] = $this->posterUrl((string) ($event['poster'] ?? ''));
        $event['organization_logo_url'] = $this->organizationLogoUrl((string) ($event['logo'] ?? ''));
        $event['registration_open'] = $event['tanggal'] >= date('Y-m-d')
            && $event['remaining'] > 0;

        return $event;
    }

    public function register(int $studentId, int $eventId): string
    {
        $this->connection->begin_transaction();
        try {
            $event = PreparedQuery::execute(
                $this->connection,
                'SELECT tanggal, status, kuota FROM event WHERE id_event = ? FOR UPDATE',
                [$eventId]
            )->fetch_assoc();
            if (!is_array($event) || $event['status'] !== 'Publish' || $event['tanggal'] < date('Y-m-d')) {
                $this->connection->rollback();
                return 'closed';
            }

            $existing = PreparedQuery::execute(
                $this->connection,
                'SELECT id_pendaftaran FROM pendaftaran WHERE id_event = ? AND id_user = ? FOR UPDATE',
                [$eventId, $studentId]
            )->fetch_assoc();
            if (is_array($existing)) {
                $this->connection->rollback();
                return 'already_registered';
            }

            $registered = PreparedQuery::execute(
                $this->connection,
                'SELECT COUNT(*) AS total FROM pendaftaran
                 WHERE id_event = ? AND status IN (?, ?)',
                [$eventId, STATUS_PENDING, STATUS_APPROVED]
            )->fetch_assoc();
            if ((int) ($registered['total'] ?? 0) >= (int) $event['kuota']) {
                $this->connection->rollback();
                return 'full';
            }

            PreparedQuery::execute(
                $this->connection,
                'INSERT INTO pendaftaran (id_event, id_user, status) VALUES (?, ?, ?)',
                [$eventId, $studentId, STATUS_APPROVED]
            );
            $this->connection->commit();

            return 'registered';
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            if ((int) $exception->getCode() === 1062) {
                return 'already_registered';
            }
            throw $exception;
        }
    }

    public function myRegistrations(int $studentId, ?int $limit = null, int $offset = 0): array
    {
        $limitSql = $limit === null
            ? ''
            : ' LIMIT ' . max(1, min(100, $limit)) . ' OFFSET ' . max(0, $offset);

        return PreparedQuery::execute(
            $this->connection,
            'SELECT p.id_pendaftaran, p.id_event, p.status, p.tanggal_daftar,
                    e.judul, e.tanggal, e.jam, e.lokasi, e.status AS event_status,
                    e.kuota, e.poster, o.nama_organisasi, c.nama_kategori,
                    COUNT(DISTINCT CASE WHEN approved.status IN (\'Menunggu\', \'Disetujui\') THEN approved.id_pendaftaran END) AS approved_count,
                    h.status_hadir
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event c ON c.id_kategori = e.id_kategori
             LEFT JOIN pendaftaran approved ON approved.id_event = e.id_event
             LEFT JOIN kehadiran h ON h.id_pendaftaran = p.id_pendaftaran
             WHERE p.id_user = ?
             GROUP BY p.id_pendaftaran, p.id_event, p.status, p.tanggal_daftar,
                      e.judul, e.tanggal, e.jam, e.lokasi, e.status,
                      e.kuota, e.poster, o.nama_organisasi, c.nama_kategori, h.status_hadir
             ORDER BY p.tanggal_daftar DESC, p.id_pendaftaran DESC' . $limitSql,
            [$studentId]
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function registrationCount(int $studentId): int
    {
        $count = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total FROM pendaftaran WHERE id_user = ?',
            [$studentId]
        )->fetch_assoc();

        return (int) ($count['total'] ?? 0);
    }

    public function cancelRegistration(int $studentId, int $registrationId): string
    {
        $result = PreparedQuery::execute(
            $this->connection,
            'DELETE p FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             WHERE p.id_pendaftaran = ?
               AND p.id_user = ?
               AND p.status IN (?, ?)
               AND e.status = \'Publish\'
               AND e.tanggal >= CURRENT_DATE()',
            [$registrationId, $studentId, STATUS_PENDING, STATUS_APPROVED]
        );

        return $result === true && $this->connection->affected_rows > 0 ? 'cancelled' : 'not_cancellable';
    }

    public function schedule(int $studentId, string $period, int $limit, int $offset): array
    {
        $periodCondition = $period === 'completed'
            ? '(e.tanggal < CURRENT_DATE() OR e.status = \'Selesai\')'
            : '(e.tanggal >= CURRENT_DATE() AND e.status <> \'Selesai\')';
        $total = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             WHERE p.id_user = ? AND p.status = ? AND ' . $periodCondition,
            [$studentId, STATUS_APPROVED]
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT p.id_pendaftaran, p.status, e.id_event, e.judul, e.tanggal, e.jam,
                    e.lokasi, o.nama_organisasi, c.nama_kategori, h.status_hadir
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN kategori_event c ON c.id_kategori = e.id_kategori
             LEFT JOIN kehadiran h ON h.id_pendaftaran = p.id_pendaftaran
             WHERE p.id_user = ? AND p.status = ? AND ' . $periodCondition . '
             ORDER BY e.tanggal ASC, e.jam ASC, e.id_event ASC
             LIMIT ? OFFSET ?',
            [$studentId, STATUS_APPROVED, $limit, $offset]
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => (int) ($total['total'] ?? 0)];
    }

    public function certificates(int $studentId, int $limit, int $offset): array
    {
        $total = PreparedQuery::execute(
            $this->connection,
            'SELECT COUNT(*) AS total
             FROM pendaftaran p
             LEFT JOIN kehadiran h ON h.id_pendaftaran = p.id_pendaftaran
             LEFT JOIN sertifikat s ON s.id_kehadiran = h.id_kehadiran
             WHERE p.id_user = ? AND p.status = ?',
            [$studentId, STATUS_APPROVED]
        )->fetch_assoc();
        $items = PreparedQuery::execute(
            $this->connection,
            'SELECT p.id_event, p.status AS registration_status,
                    e.judul, e.tanggal, e.jam, e.lokasi,
                    h.status_hadir, s.id_sertifikat, s.status AS certificate_status,
                    s.file_sertifikat, s.tanggal_upload, s.skkm_points
             FROM pendaftaran p
             INNER JOIN event e ON e.id_event = p.id_event
             LEFT JOIN kehadiran h ON h.id_pendaftaran = p.id_pendaftaran
             LEFT JOIN sertifikat s ON s.id_kehadiran = h.id_kehadiran
             WHERE p.id_user = ? AND p.status = ?
             ORDER BY e.tanggal DESC, e.id_event DESC
             LIMIT ? OFFSET ?',
            [$studentId, STATUS_APPROVED, $limit, $offset]
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => (int) ($total['total'] ?? 0)];
    }

    public function profile(int $studentId): ?array
    {
        $profile = PreparedQuery::execute(
            $this->connection,
            'SELECT id_user, nama, nim, email, username, foto, status, created_at
             FROM users WHERE id_user = ? AND role = ?',
            [$studentId, ROLE_STUDENT]
        )->fetch_assoc();

        return is_array($profile) ? $profile : null;
    }

    public function updateProfile(
        int $studentId,
        string $name,
        ?string $nim,
        string $email,
        string $currentPassword,
        ?string $newPassword,
        ?string $newPhoto
    ): array {
        $this->connection->begin_transaction();
        try {
            $user = PreparedQuery::execute(
                $this->connection,
                'SELECT password, foto FROM users
                 WHERE id_user = ? AND role = ? FOR UPDATE',
                [$studentId, ROLE_STUDENT]
            )->fetch_assoc();
            if (!is_array($user)) {
                $this->connection->rollback();
                return ['status' => 'not_found', 'old_photo' => null];
            }
            if (!password_verify($currentPassword, (string) $user['password'])) {
                $this->connection->rollback();
                return ['status' => 'invalid_password', 'old_photo' => null];
            }

            $hash = $newPassword === null ? null : password_hash($newPassword, PASSWORD_DEFAULT);
            PreparedQuery::execute(
                $this->connection,
                'UPDATE users
                 SET nama = ?, nim = ?, email = ?, password = COALESCE(?, password),
                     foto = COALESCE(?, foto)
                 WHERE id_user = ? AND role = ?',
                [$name, $nim, $email, $hash, $newPhoto, $studentId, ROLE_STUDENT]
            );
            $this->connection->commit();

            return [
                'status' => 'updated',
                'old_photo' => $newPhoto === null ? null : basename((string) $user['foto']),
            ];
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    private function eventFilterSql(array $filters): array
    {
        $conditions = ['e.status = \'Publish\'', 'e.tanggal >= CURRENT_DATE()'];
        $values = [];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $conditions[] = 'e.judul LIKE ?';
            $values[] = '%' . $search . '%';
        }
        $categoryId = filter_var($filters['category'] ?? '', FILTER_VALIDATE_INT);
        if (is_int($categoryId) && $categoryId > 0) {
            $conditions[] = 'e.id_kategori = ?';
            $values[] = $categoryId;
        }
        $organizationId = filter_var($filters['organization'] ?? '', FILTER_VALIDATE_INT);
        if (is_int($organizationId) && $organizationId > 0) {
            $conditions[] = 'e.id_organisasi = ?';
            $values[] = $organizationId;
        }
        foreach (['from' => '>=', 'to' => '<='] as $filter => $operator) {
            $date = $filters[$filter] ?? '';
            if ($this->validDate($date)) {
                $conditions[] = 'e.tanggal ' . $operator . ' ?';
                $values[] = $date;
            }
        }
        if (($filters['available_only'] ?? false) === true) {
            $conditions[] = 'e.kuota > (
                SELECT COUNT(*)
                FROM pendaftaran approved
                WHERE approved.id_event = e.id_event
                  AND approved.status IN (\'Menunggu\', \'Disetujui\')
            )';
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

    private function posterUrl(string $poster): ?string
    {
        $filename = basename($poster);
        if ($filename === ''
            || preg_match('/\A[a-zA-Z0-9._-]+\.(?:jpg|jpeg|png)\z/i', $filename) !== 1
            || !is_file(dirname(__DIR__, 2) . '/assets/uploads/poster/' . $filename)) {
            return null;
        }

        return '/assets/uploads/poster/' . rawurlencode($filename);
    }

    private function organizationLogoUrl(string $logo): ?string
    {
        $filename = basename($logo);
        if ($filename === ''
            || preg_match('/\Aorganization_[a-f0-9]{32}\.(?:jpg|jpeg|png)\z/i', $filename) !== 1
            || !is_file(dirname(__DIR__, 2) . '/assets/uploads/organisasi/' . $filename)) {
            return null;
        }

        return '/assets/uploads/organisasi/' . rawurlencode($filename);
    }
}
