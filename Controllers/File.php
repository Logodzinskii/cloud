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
        //проверить $filename на объем не более 2 гб и на то что это файл, а не код
        $fullLenPath = 'C:/wamp64/www/cloud/UsersClouds/' . $this->initialPath . $path;
        $uploadFile =  $fullLenPath . basename($filename['filename']['name']);


        if (move_uploaded_file($filename['filename']['tmp_name'], $uploadFile)) {
            echo "Файл корректен и был успешно загружен.\n";
            http_response_code('201');
        } else {
            echo "Возможная атака с помощью файловой загрузки!\n";
            http_response_code('204');
        }

        echo 'Некоторая отладочная информация:';
        //print_r($_FILES);
    }

}