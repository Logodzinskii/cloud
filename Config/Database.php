<?php

class Database
{

    private $host = 'localhost:3307';
    private $dbname = 'cloudstudents';
    private $username = 'root';
    private $password = '';
    public  $connection;

    static private $_ins = NULL;

    static public function get_instance()
    {
        if(self::$_ins instanceof self)
        {
            return self::$_ins;
        }
        return self::$_ins = new self;
    }

    public function __construct()
    {
        $this->connection = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname . '', $this->username, $this->password);
        $this->connection->exec("set names utf8");
    }

    private function __clone()
    {

    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

}