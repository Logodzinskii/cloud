<?php

class CloudConfig
{
    const HOST = 'localhost';
    const DBUSERNAME = 'root';
    const DBUSERPASSWORD = '';
    const DBNAME = 'cloud';

    protected $dbh;

    public function __construct()
    {

        try {
            $this->dbh = new PDO('mysql:host=' . CloudConfig::HOST . ';dbname=' . CloudConfig::DBNAME . '', CloudConfig::DBUSERNAME, CloudConfig::DBUSERPASSWORD);
        } catch (PDOException $e) {
            trigger_error("Report.php select: " . $e->getMessage(), E_USER_WARNING);
        }
    }

    /**
     * @return PDO
     */
    public function getDbh(): PDO
    {
        return $this->dbh;
    }

}