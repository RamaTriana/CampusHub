<?php

declare(strict_types=1);

namespace CampusHub\Models;

use mysqli;

final class AuthModel
{
    private mysqli $connection;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $database = new \Database();
        $this->connection = $database->conn;
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id_user, id_organisasi, nama, nim, email, username, password, role, foto, status
             FROM users
             WHERE username = ?
             LIMIT 1'
        );

        try {
            $statement->bind_param('s', $username);
            $statement->execute();
            $user = $statement->get_result()->fetch_assoc();

            return is_array($user) ? $user : null;
        } finally {
            $statement->close();
        }
    }

    public function findIdentityConflicts(string $username, string $email): array
    {
        $statement = $this->connection->prepare(
            'SELECT username, email
             FROM users
             WHERE username = ? OR email = ?'
        );

        try {
            $statement->bind_param('ss', $username, $email);
            $statement->execute();

            return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $statement->close();
        }
    }

    public function createStudent(
        string $name,
        string $email,
        string $username,
        string $passwordHash,
        string $photo
    ): void {
        $organizationId = null;
        $studentNumber = null;
        $role = ROLE_STUDENT;
        $status = STATUS_ACTIVE;

        $statement = $this->connection->prepare(
            'INSERT INTO users
                (id_organisasi, nama, nim, email, username, password, role, foto, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        try {
            $statement->bind_param(
                'issssssss',
                $organizationId,
                $name,
                $studentNumber,
                $email,
                $username,
                $passwordHash,
                $role,
                $photo,
                $status
            );
            $statement->execute();
        } finally {
            $statement->close();
        }
    }
}
