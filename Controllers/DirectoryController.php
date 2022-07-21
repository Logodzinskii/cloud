<?php

class DirectoryController
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
        if(strlen($_SESSION['initialPath']) > 0){
            $this->initialPath = $_SESSION['initialPath'];
        }else{
            http_response_code('401');
            die();
        }

    }

    /**
     * POST['path'] не должен содержать символы \|/:?"<>
     * @return void
     */
    public function addDirectory():void
    {

        $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath;
        if (!is_dir($fullLenPath .'/'. Validate::checkDirName(POST['path']))){
            if (!mkdir($fullLenPath .'/'. Validate::checkDirName(POST['path']), 0777, true))
            {

                http_response_code('400');

            }else{

                http_response_code('201');

            }
        }else{
            http_response_code('400');
        }

    }

    /**
     * @return void
     */
    public function deleteDirectory():void
    {

        $dir = Validate::isDirectory(Validate::checkDirName(DELETE['path']));

        Validate::remove_dir($dir);
        http_response_code('204');

    }

     /**
     * @return string
     */
    public function getInformationDirectory():string
    {
        if(defined('GET')){
            $dir = Validate::isDirectory(Validate::checkDirName(GET['path']));
        }else{
            $dir = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath;
        }

        $result = [];
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value)
        {
            if (!in_array($value,array(".","..")))
            {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
                {
                    $result[$value] = '/' . $value;
                }
                else
                {
                    $result[] = $value;
                }
            }
        }

       http_response_code('200');
       return json_encode($result);

    }

    /**
     * @return void
     */
    public function renameDirectory():void
    {
        $oldDirectoryName = Validate::checkDirName(PUT['oldDirName']);
        $newDirectoryName = Validate::checkDirName(PUT['newDirName']);

        $dir = Validate::isDirectory($oldDirectoryName);
        $newDir = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . '/' . $newDirectoryName;
        if(is_dir($dir) && !is_dir($newDir))
        {
            if(rename($dir, $newDir))
            {
                http_response_code('201');

            }else{

                http_response_code('404');

            }
        }
    }

}