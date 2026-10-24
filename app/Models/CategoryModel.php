<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class CategoryModel
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
            'SELECT k.id_kategori, k.nama_kategori, k.deskripsi, k.created_at,
                    COUNT(e.id_event) AS jumlah_event
             FROM kategori_event k
             LEFT JOIN event e ON e.id_kategori = k.id_kategori
             GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi, k.created_at
             ORDER BY nama_kategori ASC, id_kategori ASC'
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
            'SELECT k.id_kategori, k.nama_kategori, k.deskripsi, k.created_at,
                    COUNT(e.id_event) AS jumlah_event
             FROM kategori_event k
             LEFT JOIN event e ON e.id_kategori = k.id_kategori
             WHERE k.id_kategori = ?
             GROUP BY k.id_kategori, k.nama_kategori, k.deskripsi, k.created_at
             LIMIT 1'
        );

        try {
            $statement->bind_param('i', $id);
            $statement->execute();
            $category = $statement->get_result()->fetch_assoc();

            return is_array($category) ? $category : null;
        } finally {
            $statement->close();
        }
    }

    public function create(string $name, string $description = ''): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO kategori_event (nama_kategori, deskripsi) VALUES (?, ?)'
        );

        try {
            $statement->bind_param('ss', $name, $description);
            $statement->execute();
        } finally {
            $statement->close();
        }
    }

    public function update(int $id, string $name, string $description = ''): void
    {
        $statement = $this->connection->prepare(
            'UPDATE kategori_event SET nama_kategori = ?, deskripsi = ? WHERE id_kategori = ?'
        );

        try {
            $statement->bind_param('ssi', $name, $description, $id);
            $statement->execute();
        } finally {
            $statement->close();
        }
    }

    public function deleteIfUnused(int $id): string
    {
        $this->connection->begin_transaction();

        try {
            $category = PreparedQuery::execute($this->connection, 
                'SELECT id_kategori FROM kategori_event WHERE id_kategori = ? FOR UPDATE',
                [$id]
            )->fetch_assoc();
            if (!is_array($category)) {
                $this->connection->rollback();
                return 'not_found';
            }

            $eventCount = PreparedQuery::execute($this->connection, 
                'SELECT COUNT(*) AS total FROM event WHERE id_kategori = ?',
                [$id]
            )->fetch_assoc();
            if ((int) $eventCount['total'] > 0) {
                $this->connection->rollback();
                return 'in_use';
            }

            PreparedQuery::execute($this->connection, 
                'DELETE FROM kategori_event WHERE id_kategori = ?',
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
