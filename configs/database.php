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

        $this->conn = new mysqli(

            $this->host,

            $this->username,

            $this->password,

            $this->database

        );

        if ($this->conn->connect_error) {

            die("Koneksi Database Gagal : " . $this->conn->connect_error);

        }

        $this->conn->set_charset("utf8mb4");

    }

}