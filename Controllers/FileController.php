<?php

class FileController
{
    /**
     * @var
     */
    protected $conn;
    protected $initialPath;

    /**
     * @param $db
     */
    public function __construct($db)
    {
        $this->conn = $db;
        $this->initialPath = $_SESSION['initialPath'];
    }

    /**

     * @return string
     */
    public function listFile():string
    {
       $path = GET['path'];

       $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . $path;
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

    /**

     */
    public function addFile():void
    {
        $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . POST['path'];
        if(is_dir($fullLenPath)){
            $filename = FILES;
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
        }else{
            http_response_code('404');
        }

    }

    /**

     */
    public function fileDelete():void
    {
        $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . DELETE['path'];
        $deletedFile =  $fullLenPath.DELETE['filename'];
        if(file_exists($deletedFile) && unlink($deletedFile))
        {
            http_response_code('204');
        }else{
            http_response_code('400');
        }
    }

    /**

     */

    public function fileRename()
    {
        $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . PUT['path'];

        if(opendir($fullLenPath) && is_file($fullLenPath . PUT['oldfilename']))
        {
            rename($fullLenPath . PUT['oldfilename'], $fullLenPath . PUT['newfilename']);
            http_response_code('201');
        }else{
            http_response_code('404');
        }
    }
}