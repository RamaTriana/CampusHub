<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class EventModel
{
    private mysqli $connection;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $database = new \Database();
        $this->connection = $database->conn;
    }

    public function all(): array
    {
        $statement = $this->connection->prepare(
            'SELECT e.id_event, e.id_organisasi, e.id_panitia, e.id_kategori,
                    e.judul, e.deskripsi, e.poster, e.tanggal, e.jam, e.lokasi,
                    e.kuota, e.status, e.created_at,
                    o.nama_organisasi, u.nama AS nama_panitia,
                    k.nama_kategori
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN users u ON u.id_user = e.id_panitia
             INNER JOIN kategori_event k ON k.id_kategori = e.id_kategori
             ORDER BY e.created_at DESC, e.id_event DESC'
        );

        try {
            $statement->execute();

            return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $statement->close();
        }
    }

    public function adminList(array $filters, int $limit, int $offset): array
    {
        $conditions = [];
        $values = [];
        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(e.judul LIKE ? OR e.lokasi LIKE ? OR o.nama_organisasi LIKE ? OR k.nama_kategori LIKE ? OR u.nama LIKE ?)';
            $term = '%' . $search . '%';
            array_push($values, $term, $term, $term, $term, $term);
        }

        if (in_array($filters['status'] ?? '', ['Draft', 'Publish', 'Selesai'], true)) {
            $conditions[] = 'e.status = ?';
            $values[] = $filters['status'];
        }
        foreach (['category' => 'e.id_kategori', 'organization' => 'e.id_organisasi'] as $key => $column) {
            $id = filter_var($filters[$key] ?? null, FILTER_VALIDATE_INT);
            if (is_int($id) && $id > 0) {
                $conditions[] = $column . ' = ?';
                $values[] = $id;
            }
        }
        foreach (['from' => 'e.tanggal >= ?', 'to' => 'e.tanggal <= ?'] as $key => $condition) {
            $date = (string) ($filters[$key] ?? '');
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($parsed !== false && $parsed->format('Y-m-d') === $date) {
                $conditions[] = $condition;
                $values[] = $date;
            }
        }

        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $sort = match ($filters['sort'] ?? '') {
            'title' => 'e.judul ASC, e.id_event DESC',
            'date_asc' => 'e.tanggal ASC, e.jam ASC, e.id_event DESC',
            'date_desc' => 'e.tanggal DESC, e.jam DESC, e.id_event DESC',
            default => 'e.created_at DESC, e.id_event DESC',
        };
        $joins = ' FROM event e
                   INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
                   INNER JOIN users u ON u.id_user = e.id_panitia
                   INNER JOIN kategori_event k ON k.id_kategori = e.id_kategori';
        $total = (int) PreparedQuery::execute($this->connection, 
            'SELECT COUNT(*) AS total' . $joins . $where,
            $values
        )->fetch_assoc()['total'];
        $values[] = $limit;
        $values[] = $offset;
        $items = PreparedQuery::execute($this->connection, 
            'SELECT e.id_event, e.id_organisasi, e.id_panitia, e.id_kategori,
                    e.judul, e.deskripsi, e.poster, e.tanggal, e.jam, e.lokasi,
                    e.kuota, e.status, e.created_at,
                    o.nama_organisasi, u.nama AS nama_panitia, k.nama_kategori,
                    (SELECT COUNT(*) FROM pendaftaran p WHERE p.id_event = e.id_event) AS jumlah_pendaftar
             ' . $joins . $where . ' ORDER BY ' . $sort . ' LIMIT ? OFFSET ?',
            $values
        )->fetch_all(MYSQLI_ASSOC);

        return ['items' => $items, 'total' => $total];
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id_event, id_organisasi, id_panitia, id_kategori,
                    judul, deskripsi, poster, tanggal, jam, lokasi,
                    kuota, status, created_at
             FROM event
             WHERE id_event = ?
             LIMIT 1'
        );

        try {
            $statement->bind_param('i', $id);
            $statement->execute();
            $event = $statement->get_result()->fetch_assoc();

            return is_array($event) ? $event : null;
        } finally {
            $statement->close();
        }
    }

    public function adminDetail(int $id): ?array
    {
        $result = PreparedQuery::execute($this->connection, 
            'SELECT e.id_event, e.judul, e.deskripsi, e.poster, e.tanggal, e.jam,
                    e.lokasi, e.kuota, e.status, e.created_at,
                    o.nama_organisasi, u.nama AS nama_panitia, k.nama_kategori,
                    (SELECT COUNT(*) FROM pendaftaran p WHERE p.id_event = e.id_event) AS jumlah_pendaftar,
                    (SELECT COUNT(*) FROM pendaftaran p WHERE p.id_event = e.id_event AND p.status = ?) AS jumlah_disetujui
             FROM event e
             INNER JOIN organisasi o ON o.id_organisasi = e.id_organisasi
             INNER JOIN users u ON u.id_user = e.id_panitia
             INNER JOIN kategori_event k ON k.id_kategori = e.id_kategori
             WHERE e.id_event = ? LIMIT 1',
            [STATUS_APPROVED, $id]
        )->fetch_assoc();

        return is_array($result) ? $result : null;
    }

    public function deleteIfUnused(int $id): array
    {
        $this->connection->begin_transaction();

        try {
            $event = PreparedQuery::execute($this->connection, 
                'SELECT poster FROM event WHERE id_event = ? FOR UPDATE',
                [$id]
            )->fetch_assoc();
            if (!is_array($event)) {
                $this->connection->rollback();
                return ['status' => 'not_found', 'poster' => null];
            }

            $usage = PreparedQuery::execute($this->connection, 
                'SELECT
                    (SELECT COUNT(*) FROM pendaftaran WHERE id_event = ?) AS registrations,
                    (SELECT COUNT(*) FROM dokumentasi WHERE id_event = ?) AS documentation',
                [$id, $id]
            )->fetch_assoc();
            if ((int) $usage['registrations'] > 0 || (int) $usage['documentation'] > 0) {
                $this->connection->rollback();
                return ['status' => 'in_use', 'poster' => null];
            }

            PreparedQuery::execute($this->connection, 'DELETE FROM event WHERE id_event = ?', [$id]);
            $this->connection->commit();

            return ['status' => 'deleted', 'poster' => basename((string) $event['poster'])];
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function categories(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id_kategori, nama_kategori
             FROM kategori_event
             ORDER BY nama_kategori ASC, id_kategori ASC'
        );

        try {
            $statement->execute();

            return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $statement->close();
        }
    }

    public function organizations(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id_organisasi, nama_organisasi
             FROM organisasi
             ORDER BY nama_organisasi ASC, id_organisasi ASC'
        );

        try {
            $statement->execute();

            return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $statement->close();
        }
    }

    public function committees(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id_user, nama
             FROM users
             WHERE role = ? AND status = ?
             ORDER BY nama ASC, id_user ASC'
        );

        try {
            $role = ROLE_COMMITTEE;
            $status = STATUS_ACTIVE;
            $statement->bind_param('ss', $role, $status);
            $statement->execute();

            return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $statement->close();
        }
    }

    public function referenceOptions(): array
    {
        return [
            'categories' => $this->categories(),
            'organizations' => $this->organizations(),
            'committees' => $this->committees(),
        ];
    }

    public function create(array $event): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO event (
                id_organisasi, id_panitia, id_kategori, judul, deskripsi,
                poster, tanggal, jam, lokasi, kuota, status
             ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        try {
            $statement->bind_param(
                'iiissssssis',
                $event['id_organisasi'],
                $event['id_panitia'],
                $event['id_kategori'],
                $event['judul'],
                $event['deskripsi'],
                $event['poster'],
                $event['tanggal'],
                $event['jam'],
                $event['lokasi'],
                $event['kuota'],
                $event['status']
            );
            $statement->execute();
        } finally {
            $statement->close();
        }
    }

    public function update(int $id, array $event): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE event
             SET id_organisasi = ?, id_panitia = ?, id_kategori = ?,
                 judul = ?, deskripsi = ?, tanggal = ?, jam = ?, lokasi = ?,
                 kuota = ?, status = ?, poster = COALESCE(?, poster)
             WHERE id_event = ?'
        );

        try {
            $statement->bind_param(
                'iiisssssissi',
                $event['id_organisasi'],
                $event['id_panitia'],
                $event['id_kategori'],
                $event['judul'],
                $event['deskripsi'],
                $event['tanggal'],
                $event['jam'],
                $event['lokasi'],
                $event['kuota'],
                $event['status'],
                $event['poster'],
                $id
            );
            $statement->execute();

            if ($statement->affected_rows > 0) {
                return true;
            }
        } finally {
            $statement->close();
        }

        return $this->findById($id) !== null;
    }
}
