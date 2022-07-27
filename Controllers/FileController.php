<?php

class FileController
{
    /**
     * @var
     */
    protected $conn;


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
     * @return string
     */
    public function listFile():string
    {
       $fullLenPath = Validate::isDirectory(GET['path']);

       $arrFile = [];

       $files = scandir($fullLenPath);

       foreach ($files as $file)
       {
           if (is_file($fullLenPath . $file))
           {
               $arrFile[]=$file;
           }
       }

       http_response_code('200');
       return json_encode($arrFile);

    }

    /**
    * @return void
     */
    public function addFile():void
    {
        $fullLenPath = Validate::isDirectory(POST['path']);

        if (FILES['filename']['size'] <= 2147483648 )
        {
            $uploadFile =  $fullLenPath . Validate::checkFileName(basename(FILES['filename']['name']));

            if (move_uploaded_file(FILES['filename']['tmp_name'], $uploadFile))
            {

                http_response_code('201');

            }else{

                http_response_code('204');
            }

        }else{

            http_response_code('400');

        }

    }

    /**
    * @return void
    */
    public function fileDelete():void
    {

        $fullLenPath = Validate::isDirectory(DELETE['path']);
        $deletedFile =  $fullLenPath.Validate::checkFileName(DELETE['filename']);

        if(file_exists($deletedFile) && unlink($deletedFile))
        {

            http_response_code('204');

        }else{

            http_response_code('400');

        }
    }

    /**
     * @return void
     */
    public function fileRename()
    {
        $fullLenPath = Validate::isDirectory(PUT['path']);

        if(opendir($fullLenPath))
        {

            rename($fullLenPath . Validate::checkFileName(PUT['oldfilename']), $fullLenPath . Validate::checkFileName(PUT['newfilename']));
            http_response_code('201');

        }else{

            http_response_code('404');

        }
    }
    public function addFileAccessToSharedFile()
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

                http_response_code('201');

        }else{
            http_response_code('400');
        }

    }
    public function fileSharedListUsers()
    {
        if(defined('GET'))
        {
            $query = "SELECT `user_id` FROM sharedfiles WHERE file_id =:file_id";
            $param = [
                'file_id' => Validate::validateId(GET['file_id']),
            ];
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() > 0){
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row;
                http_response_code('200');
            }else{
                http_response_code('404');
            }
        }
    }
    public function deleteAccessToSharedFile()
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
                http_response_code('401');
            }else{

                http_response_code('204');
            }
        }
    }

}