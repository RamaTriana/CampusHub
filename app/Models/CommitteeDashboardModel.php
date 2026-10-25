<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class CommitteeDashboardModel
{
    private mysqli $connection;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $database = new \Database();
        $this->connection = $database->conn;
    }

    public function dashboardData(int $committeeId): array
    {
        return [
            'stats' => $this->stats($committeeId),
            'events' => $this->events($committeeId),
            'documentation' => $this->documentation($committeeId),
        ];
    }

    private function stats(int $committeeId): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                (SELECT COUNT(*)
                 FROM event e
                 WHERE e.id_panitia = ?) AS events,
                (SELECT COUNT(*)
                 FROM pendaftaran p
                 INNER JOIN event e ON e.id_event = p.id_event
                 WHERE e.id_panitia = ?
                   AND e.tanggal = CURRENT_DATE()
                   AND p.status = ?) AS participants_today,
                (SELECT COUNT(*)
                 FROM sertifikat s
                 INNER JOIN kehadiran k ON k.id_kehadiran = s.id_kehadiran
                 INNER JOIN pendaftaran p ON p.id_pendaftaran = k.id_pendaftaran
                 INNER JOIN event e ON e.id_event = p.id_event
                 WHERE e.id_panitia = ?
                   AND s.status = \'Terbit\') AS certificates'
        );

        try {
            $approvedStatus = STATUS_APPROVED;
            $statement->bind_param('iisi', $committeeId, $committeeId, $approvedStatus, $committeeId);
            $statement->execute();
            $stats = $statement->get_result()->fetch_assoc();

            if (!is_array($stats)) {
                throw new \RuntimeException('Committee dashboard summary query returned no result.');
            }

            return [
                'events' => (int) $stats['events'],
                'participants_today' => (int) $stats['participants_today'],
                'certificates' => (int) $stats['certificates'],
            ];
        } finally {
            $statement->close();
        }
    }

    private function events(int $committeeId): array
    {
        $statement = $this->connection->prepare(
            'SELECT e.id_event, e.judul, e.deskripsi, e.poster, e.tanggal, e.jam, e.lokasi, e.status,
                o.nama_organisasi,
                (SELECT COUNT(*)
                 FROM pendaftaran p
                 WHERE p.id_event = e.id_event) AS total_registrations,
                (SELECT COUNT(*)
                 FROM pendaftaran p
                 WHERE p.id_event = e.id_event
                   AND p.status = ?) AS approved_participants,
                (SELECT COUNT(*)
                 FROM kehadiran k
                 INNER JOIN pendaftaran p ON p.id_pendaftaran = k.id_pendaftaran
                 WHERE p.id_event = e.id_event
                   AND p.status = ?
                   AND k.status_hadir = ?
                   AND k.waktu_checkin IS NOT NULL) AS checked_in,
                (SELECT COUNT(*)
                 FROM sertifikat s
                 INNER JOIN kehadiran k ON k.id_kehadiran = s.id_kehadiran
                 INNER JOIN pendaftaran p ON p.id_pendaftaran = k.id_pendaftaran
                 WHERE p.id_event = e.id_event
                   AND s.status = \'Terbit\') AS certificates
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             WHERE e.id_panitia = ?
               AND e.tanggal >= CURRENT_DATE()
             ORDER BY
                CASE e.status WHEN \'Publish\' THEN 0 WHEN \'Draft\' THEN 1 ELSE 2 END,
                CASE WHEN e.status = \'Selesai\' THEN e.tanggal END DESC,
                CASE WHEN e.status <> \'Selesai\' THEN e.tanggal END ASC,
                e.jam ASC,
                e.id_event DESC
             LIMIT 6'
        );

        try {
            $approvedStatus = STATUS_APPROVED;
            $presentStatus = ATTENDANCE_PRESENT;
            $statement->bind_param(
                'sssi',
                $approvedStatus,
                $approvedStatus,
                $presentStatus,
                $committeeId
            );
            $statement->execute();

            $events = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
            $posterDirectory = dirname(__DIR__, 2) . '/assets/uploads/poster';
            foreach ($events as &$event) {
                $filename = basename((string) ($event['poster'] ?? ''));
                $event['poster_url'] = $filename !== ''
                    && preg_match('/\A[a-zA-Z0-9._-]+\.(?:jpg|jpeg|png)\z/i', $filename) === 1
                    && is_file($posterDirectory . DIRECTORY_SEPARATOR . $filename)
                    ? '/assets/uploads/poster/' . rawurlencode($filename)
                    : null;
            }
            unset($event);

            return $events;
        } finally {
            $statement->close();
        }
    }

    private function documentation(int $committeeId): array
    {
        $statement = $this->connection->prepare(
            'SELECT d.foto, d.keterangan, d.created_at, e.judul
             FROM dokumentasi d
             INNER JOIN event e ON e.id_event = d.id_event
             WHERE e.id_panitia = ?
             ORDER BY d.created_at DESC, d.id_dokumentasi DESC
             LIMIT 12'
        );

        try {
            $statement->bind_param('i', $committeeId);
            $statement->execute();
            $records = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $statement->close();
        }

        $documentation = [];
        $uploadDirectory = dirname(__DIR__, 2) . '/assets/uploads/dokumentasi';

        foreach ($records as $record) {
            $filename = basename((string) $record['foto']);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (
                $filename === ''
                || !in_array($extension, ['jpg', 'jpeg', 'png'], true)
                || !is_file($uploadDirectory . DIRECTORY_SEPARATOR . $filename)
            ) {
                continue;
            }

            $record['url'] = '/assets/uploads/dokumentasi/' . rawurlencode($filename);
            $documentation[] = $record;

            if (count($documentation) === 4) {
                break;
            }
        }

        return $documentation;
    }
}
