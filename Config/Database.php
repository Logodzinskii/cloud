<?php

class Database
{

    private $host = 'localhost';
    private $dbname = 'cloud';
    private $username = 'root';
    private $password = '';
    public $conn;

    /**
     * @return mixed
     */
    public function getConn()
    {
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname . '', $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $e) {
            trigger_error("Conn error: " . $e->getMessage(), E_USER_WARNING);
        }
        return $this->conn;
    }
}