<?php

class File
{
    protected $conn;
    protected $initialPath;

    // конструктор для соединения с базой данных
    public function __construct($db){

        $this->conn = $db;
        $this->initialPath = $_SESSION['initialPath'];

    }

    public function listFile($path)
    {
       $fullLenPath = 'C:/wamp64/www/cloud/UsersClouds/' . $this->initialPath . $path;
       $arrFile = [];
       if(is_dir($fullLenPath)){

            $files = scandir($fullLenPath);

       }elseif(is_file($fullLenPath)){

           $info = pathinfo($path);
           return json_encode($info);

       }else{

            http_response_code('404');
            die('файл не найден');
       }

       $files = scandir($fullLenPath);

       foreach ($files as $file){
           if (is_file($fullLenPath . $file))
           {
               $arrFile[]=$file;
           }
       }

       http_response_code('200');
       return json_encode($arrFile);
    }

    public function addFile($path, $filename)
    {
        $fullLenPath = 'C:/wamp64/www/cloud/UsersClouds/' . $this->initialPath . $path;
        $uploadFile =  $fullLenPath . basename($filename['filename']['name']);
        //проверить $filename на объем не более 2 гб
        if ($filename['filename']['size'] <= 2147483648){
            if (move_uploaded_file($filename['filename']['tmp_name'], $uploadFile)) {

                http_response_code('201');
            } else {

                http_response_code('204');
            }

        }else{
            http_response_code('400');
        }

    }

    public function fileDelete($path,$filename)
    {
        $fullLenPath = 'C:/wamp64/www/cloud/UsersClouds/' . $this->initialPath . $path;
        $deletedFile =  $fullLenPath.$filename;
        if(file_exists($deletedFile) && unlink($deletedFile))
        {
            http_response_code('204');
        }else{
            http_response_code('400');
        }
    }

    public function fileRename($path, $oldFilename, $newFilename)
    {
        $fullLenPath = 'C:/wamp64/www/cloud/UsersClouds/' . $this->initialPath . $path;

        if(opendir($fullLenPath) && is_file($fullLenPath . $oldFilename))
        {
            rename($fullLenPath . $oldFilename, $fullLenPath . $newFilename);
            http_response_code('201');
        }else{
            http_response_code('404');
        }
    }

}