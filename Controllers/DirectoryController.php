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
        $this->initialPath = $_SESSION['initialPath'];
    }

    /**
     * @param string $path
     * @return void
     */
    public function addDirectory(string $path):void
    {
        $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath;
        if (!is_dir($fullLenPath .'/'. $path))
        {
            if (!mkdir($fullLenPath.'/'. $path, 0777, true)) {
                die('Не удалось создать директории...');
            }else http_response_code('201');

        }else{
            http_response_code('404');
        }
    }

    /**
     * @param string $path
     * @return void
     */
    public function deleteDirectory(string $path):void
    {
        $src = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . '/' . $path;
        if (file_exists($src)) {
            $dir = opendir($src);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    $full = $src . '/' . $file;
                    if (is_dir($full)) {
                        rmdir($full);

                    } else {
                        unlink($full);
                    }
                }
            }
            closedir($dir);
            rmdir($src);
            http_response_code('204');
        }else{
            http_response_code('404');
        }
    }

    /**
     * @param string $path
     * @return string
     */
    public function getInformationDirectory(string $path):string
    {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . '/' . $path;
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
        http_response_code(200);
        return json_encode($result);
    }

    /**
     * @param string $oldDirectoryName
     * @param string $newDirectoryName
     * @return void
     */
    public function renameDirectory(string $oldDirectoryName, string $newDirectoryName):void
    {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . '/' . $oldDirectoryName;
        $newDir = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath . '/' . $newDirectoryName;
        if(is_dir($dir) && !is_dir($newDir))
        {
            if(rename($dir, $newDir))
            {
                http_response_code(201);
            }else{
                http_response_code(404);
            }
        }
    }
}