<?php

class Database
{
    private $host = "localhost";
    private $username = "root";
    private $password = "komangtriana12";
    private $database = "campushub";

    public $conn;

    public function __construct()
    {
        try {
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );

            if ($this->conn->connect_error) {
                throw new \RuntimeException('Database connection failed.');
            }

            $this->conn->set_charset("utf8mb4");
        } catch (mysqli_sql_exception $exception) {
            throw new \RuntimeException('Database connection failed.', 0, $exception);
        }
    }
}