<?php

class File
{
    // конструктор для соединения с базой данных
    public function __construct($db){
        $this->conn = $db;
    }

    public function listFile()
    {
       $files = scandir('C:/wamp64/www/cloud/UsersClouds/1/');

       return json_encode($files);
    }
}