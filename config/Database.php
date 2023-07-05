<?php

class Database
{
    // DB Params
    private $host = 'localhost';
    private $db_name = 'rest_api_2';
    private $username = 'root';
    private $password = 'root';
    private $conn;

    //DB connect
    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection Error: ' . $e->getMessage();
            exit;
        }

        return $this->conn;
    }
}
