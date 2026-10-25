<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class CertificateAccessModel
{
    private mysqli $connection;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $database = new \Database();
        $this->connection = $database->conn;
    }

    public function filename(int $certificateId, int $userId, string $role): ?string
    {
        $conditions = [
            's.id_sertifikat = ?',
            's.status = \'Terbit\'',
            'p.status = ?',
        ];
        $values = [$certificateId, STATUS_APPROVED];
        if ($role === ROLE_COMMITTEE) {
            $conditions[] = 'e.id_panitia = ?';
            $values[] = $userId;
        } elseif ($role === ROLE_STUDENT) {
            $conditions[] = 'p.id_user = ?';
            $values[] = $userId;
        } elseif ($role !== ROLE_ADMIN) {
            return null;
        }

        $result = PreparedQuery::execute(
            $this->connection,
            'SELECT s.file_sertifikat
             FROM sertifikat s
             INNER JOIN kehadiran h ON h.id_kehadiran = s.id_kehadiran
             INNER JOIN pendaftaran p ON p.id_pendaftaran = h.id_pendaftaran
             INNER JOIN event e ON e.id_event = p.id_event
             WHERE ' . implode(' AND ', $conditions),
            $values
        )->fetch_assoc();

        return is_array($result) ? basename((string) $result['file_sertifikat']) : null;
    }
}
