<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class OrganizationModel
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
            'SELECT o.id_organisasi, o.nama_organisasi, o.logo, o.deskripsi, o.created_at,
                    (SELECT COUNT(*) FROM event e WHERE e.id_organisasi = o.id_organisasi) AS jumlah_event,
                    (SELECT COUNT(*) FROM users u WHERE u.id_organisasi = o.id_organisasi) AS jumlah_anggota
             FROM organisasi o
             ORDER BY o.nama_organisasi ASC, o.id_organisasi ASC'
        );

        try {
            $statement->execute();

            return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $statement->close();
        }
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id_organisasi, nama_organisasi, logo, deskripsi, created_at
             FROM organisasi
             WHERE id_organisasi = ?
             LIMIT 1'
        );

        try {
            $statement->bind_param('i', $id);
            $statement->execute();
            $organization = $statement->get_result()->fetch_assoc();

            return is_array($organization) ? $organization : null;
        } finally {
            $statement->close();
        }
    }

    public function create(string $name, string $description, ?string $logo): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO organisasi (nama_organisasi, deskripsi, logo) VALUES (?, ?, ?)'
        );

        try {
            $statement->bind_param('sss', $name, $description, $logo);
            $statement->execute();
        } finally {
            $statement->close();
        }
    }

    public function update(int $id, string $name, string $description, ?string $logo): void
    {
        $statement = $this->connection->prepare(
            'UPDATE organisasi
             SET nama_organisasi = ?, deskripsi = ?, logo = COALESCE(?, logo)
             WHERE id_organisasi = ?'
        );

        try {
            $statement->bind_param('sssi', $name, $description, $logo, $id);
            $statement->execute();
        } finally {
            $statement->close();
        }
    }

    public function deleteIfUnused(int $id): string
    {
        $this->connection->begin_transaction();

        try {
            $organization = PreparedQuery::execute($this->connection, 
                'SELECT id_organisasi FROM organisasi WHERE id_organisasi = ? FOR UPDATE',
                [$id]
            )->fetch_assoc();
            if (!is_array($organization)) {
                $this->connection->rollback();
                return 'not_found';
            }

            $usage = PreparedQuery::execute($this->connection, 
                'SELECT
                    (SELECT COUNT(*) FROM event WHERE id_organisasi = ?) AS events,
                    (SELECT COUNT(*) FROM users WHERE id_organisasi = ?) AS members',
                [$id, $id]
            )->fetch_assoc();
            if ((int) $usage['events'] > 0 || (int) $usage['members'] > 0) {
                $this->connection->rollback();
                return 'in_use';
            }

            PreparedQuery::execute($this->connection, 
                'DELETE FROM organisasi WHERE id_organisasi = ?',
                [$id]
            );
            $this->connection->commit();

            return 'deleted';
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }
}
