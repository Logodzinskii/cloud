<?php

class DirectoryController
{
    /**
     * В SESSION['initialPath'] храниться информация о пути к корневой папке пользователя
     * Если сессия существует и имеется папка по указанному пути, то создается объект класса
     * если нет, то возвращается 401 и записывается сообщение в лог файл
     * @var
     */
    protected $conn;
    protected $initialPath;

    /**
     * @param $db
     * @throws Exception
     */
    public function __construct($db)
    {
        $this->conn = $db;
        if(strlen($_SESSION['initialPath']) > 0){
            $this->initialPath = $_SESSION['initialPath'];
        }else{
            http_response_code(401);
            throw new Exception('initialPath не найдена или не существует');
        }

    }

    /**
     * В теле запроса POST необходимо передать следующие значения:
     * path = string,
     * POST['path'] не должен содержать символы \|/:?"<>
     * @return void
     * @throws
     */
    public function addDirectory():void
    {

        $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $this->initialPath;
        if (!is_dir($fullLenPath .'/'. Validate::checkDirName(POST['path']))){
            if (!mkdir($fullLenPath .'/'. Validate::checkDirName(POST['path']), 0777, true))
            {

                http_response_code(400);

            }else{

                http_response_code(201);

            }
        }else{
            http_response_code(400);
            throw new Exception('addDirectory некорректный запрос');
        }

    }

    /**
     * В теле запросе DELETE необходимо передать следующие значения:
     * path = string,
     * @return void
     * @throws Exception
     */
    public function deleteDirectory():void
    {

        $dir = Validate::isDirectory(Validate::checkDirName(DELETE['path']));

        Validate::remove_dir($dir);
        http_response_code(204);

    }

     /**
      * В теле запросе GET необходимо передать следующие значения:
      * path = string
      * @return string
      * @throws Exception
     */
    public function getInformationDirectory():string
    {
        if(defined('GET') && strlen(GET['path']) > 0){
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

       http_response_code(200);
       return json_encode($result);

    }

    /**
     * В запросе PUT необходимо передать следующие параметры
     * oldDirName = string
     * newDirName = string
     * @return void
     * @throws Exception
     */
    public function renameDirectory():void
    {
        if(isset(PUT['oldDirName']) && isset(PUT['newDirName'])){
            $oldDirectoryName = Validate::checkDirName(PUT['oldDirName']);
            $newDirectoryName = Validate::checkDirName(PUT['newDirName']);

            $dir = Validate::isDirectory($oldDirectoryName);
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
        }else{
            http_response_code(400);
            throw new Exception('renameDirectory некорректный запрос');
        }

    }

}