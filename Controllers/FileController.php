<?php

class FileController
{
    /**
     * @var
     */
    protected $conn;


    /**
     * В SESSION['initialPath'] храниться информация о пути к корневой папке пользователя
     * Если сессия существует и имеется папка по указанному пути, то создается объект класса
     * если нет, то возвращается 401 и записывается сообщение в лог файл
     * @param $db
     * @throws Exception
     */
    public function __construct($db)
    {
        $this->conn = $db;
        if(strlen($_SESSION['initialPath']) > 0 && is_dir($_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' .  $_SESSION['initialPath'] . '/')){
            $this->initialPath = $_SESSION['initialPath'];
        }else{
            http_response_code(401);
            throw new Exception('initialPath не найдена или не существует');
        }

    }

    /**
     * Метод выдает результат в зависимости от запроса GET,
     * если он содержит параметр path, то путь до директории = path.
     * Если запрос не содержит path, то выводится корневая директория
     * @return string
     */
    public function listFile():string
    {
        if(defined('GET') && strlen(GET['path']) > 0)
        {
            $fullLenPath = Validate::isDirectory(GET['path']);
        }else
        {
            $fullLenPath = Validate::isDirectory('/');
        }

       $arrFile = [];

       $files = scandir($fullLenPath);

       foreach ($files as $file)
       {
           if (is_file($fullLenPath . $file))
           {
               $arrFile[]=$file;
           }
       }

       http_response_code(200);
       return json_encode($arrFile);

    }

    /**
     * В теле запроса POST необходимо передать слудющие значения:
     * path = string, пример /test/
     * В запросе методом FILES необходимо передать filename = file
    * @return void
     * @throws Exception
     */
    public function addFile():void
    {
        if(defined('POST') && strlen(POST['path']) > 0){
            $fullLenPath = Validate::isDirectory(POST['path']);

            if (FILES['filename']['size'] <= 2147483648 )
            {
                $uploadFile =  $fullLenPath . Validate::checkFileName(basename(FILES['filename']['name']));

                if (move_uploaded_file(FILES['filename']['tmp_name'], $uploadFile))
                {

                    http_response_code(201);

                }else{

                    http_response_code(204);
                }

            }else{

                http_response_code(400);
                throw new Exception('Некорректный запрос addFile', 0);
            }
        }else{
            http_response_code(400);
            throw new Exception('Некорректный запрос addFile', 0);
        }


    }

    /**
    * В запросе DELETE необходимо передать следующие значения:
     * path = string,
     * filename = string,
    * @return void
    */
    public function fileDelete():void
    {

        $fullLenPath = Validate::isDirectory(DELETE['path']);
        $deletedFile =  $fullLenPath.Validate::checkFileName(DELETE['filename']);

        if(file_exists($deletedFile) && unlink($deletedFile))
        {

            http_response_code(204);

        }else{

            http_response_code(400);

        }
    }

    /**
     * В теле запроса PUT необходимо передать следующие значения:
     * path = string,
     * oldfilename = string,
     * newfilename = string,
     * @return void
     */
    public function fileRename()
    {
        $fullLenPath = Validate::isDirectory(PUT['path']);

        if(opendir($fullLenPath))
        {

            rename($fullLenPath . Validate::checkFileName(PUT['oldfilename']), $fullLenPath . Validate::checkFileName(PUT['newfilename']));
            http_response_code(201);

        }else{

            http_response_code(404);

        }
    }

    /**
     * В теле запроса PUT необходимо передать следующие значения:
     * file_id = int,
     * user_id = int,
     * @throws Exception
     * @return void
     */
    public function addFileAccessToSharedFile():void
    {
        if(defined('PUT'))
        {

            $query = "INSERT INTO `sharedfiles` SET `file_id` =:file_id, `user_id` =:user_id, `count` =:count";
            $sth = $this->conn->prepare($query);
            $sth->execute([
                'file_id'=>Validate::validateId(PUT['file_id']),
                'user_id'=>Validate::validateId(PUT['user_id']),
                'count'=>0
            ]);

                http_response_code(201);

        }else{
            http_response_code(400);
        }

    }

    /**
     * В запросе GET необходимо передать следующие параметры:
     * file_id = int
     * @throws Exception
     * @return string
     */
    public function fileSharedListUsers():string
    {
        if(defined('GET'))
        {
            $query = "SELECT `user_id` FROM sharedfiles WHERE file_id =:file_id";
            $param = [
                'file_id' => Validate::validateId(GET['file_id']),
            ];
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);
            $res = '';
            if($stmt->rowCount() > 0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                http_response_code(200);
                $res = $row;

            }else{
                http_response_code(404);
            }
            return $res;
        }else{
            throw new Exception('Некорректный запрос');
        }
    }
    /**
     * В запросе DELETE необходимо передать следующие значения:
     * file_id = int,
     * user_id = int,
     * @return void
     * @throws Exception
     */
    public function deleteAccessToSharedFile():void
    {
        if(defined('DELETE'))
        {
            $query = "DELETE FROM sharedfiles WHERE user_id =:user_id AND file_id =:file_id";
            $param = [
                'file_id' => Validate::validateId(DELETE['file_id']),
                'user_id' => Validate::validateId(DELETE['user_id']),
            ];
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);
            if( ! $stmt->rowCount() ){
                http_response_code(401);
            }else{

                http_response_code(204);
            }
        }else{
            throw new Exception('Некорректный запрос');
        }
    }

}