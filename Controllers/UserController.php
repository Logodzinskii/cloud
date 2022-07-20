<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class UserController extends exception
{
    protected $connection;
    /**
     * @param $db
     */
    public function __construct($db){
        $this->connection = $db;
    }

    /**

     * @return string
     */
    public function listUsers():string
    {

        if(is_null(GET['id'])){

            $query = 'SELECT * FROM users';
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            if($stmt->rowCount() > 0)
            {
                while ($row = $stmt->fetch(PDO::FETCH_LAZY))
                {
                    $res[] = [
                        'email'=>$row->user_email,
                        'age'=>$row->age,
                        'first_name'=>$row->first_name,
                        'users_status'=>$row->users_status,
                    ];
                }
            }else{
                http_response_code('404');
                return false;
            }

            return json_encode($res);

        }elseif(strlen(GET['id']) > 0){

            $param = [
                'id'=> ''.GET['id'].''  ,
            ];
            $query = 'SELECT * FROM users WHERE id=:id';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() > 0)
            {
                while ($row = $stmt->fetch(PDO::FETCH_LAZY))
                {
                    $res[] = [
                        'email'=>$row->user_email,
                        'age'=>$row->age,
                        'first_name'=>$row->first_name,
                        'users_status'=>$row->users_status,
                    ];
                }
            }else{
                http_response_code('404');
                return  false;
            }

            return json_encode($res);
        }
        else{
            http_response_code('404');
            return  false;
        }

    }

    /**

     */
    public function addUser():void
    {

        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", POST['email']);
        $check += (strlen(POST['password']) > 6) ? 1 : 0;
        $check += (intval(POST['age']) <= 99) ? 1 : 0;
        $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', POST['first_name']);
        $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', POST['status']);

        if($check === 5)
        {
            $email = POST['email'];
            $password = POST['password'];
            $age= intval(POST['age']);
            $firstName = POST['first_name'];
            $status = POST['status'];
            $salt = rand(0,888);
            $initial_path = md5(POST['email'].$salt);
            $param = [
                'user_email'=>$email,
            ];
            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() === 0){
                $sth = $this->connection->prepare("INSERT INTO `users` SET `user_email` = :user_email, `user_password` = :user_password, `users_status` = :users_status, `age`= :age, `first_name`=:first_name, `initial_path`=:initial_path");
                $sth->execute([
                    'user_email' => $email,
                    'user_password' => password_hash($password, PASSWORD_DEFAULT),
                    'users_status'=>$status,
                    'age'=>$age,
                    'first_name'=>$firstName,
                    'initial_path'=>$initial_path,
                ]);
                try{
                    if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/UsersClouds/'.$initial_path)){

                        mkdir($_SERVER['DOCUMENT_ROOT'].'/UsersClouds/'.$initial_path, 0777, true);

                    }

                }catch (\Exception $e){
                    echo $e->getMessage();
                    http_response_code('204');
                }
                http_response_code('201');
            }else{
                http_response_code('401');
            }
        }
    }

    /**

     */
    public function updateUser():void
    {
        if(defined('PUT')){
            $check = preg_match('/^[0-9]*$/', PUT['id']);
            $check += (intval(PUT['age']) <= 99) ? 1 : 0;
            $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', PUT['first_name']);
            $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', PUT['status']);

            if($check === 4){

                $id = intval(PUT['id']);
                $age = intval(PUT['age']);
                $firstName = PUT['first_name'];
                $status = PUT['status'];

                $query = "UPDATE users SET age = :age, first_name = :first_name, users_status = :users_status WHERE id = :id";
                $params = [
                    'id' => $id,
                    'age' => $age,
                    'first_name'=> $firstName,
                    'users_status'=>$status,
                ];

                try {

                    $stmt = $this->connection->prepare($query);
                    $stmt->execute($params);

                }catch (PDOException $e){
                    trigger_error($e->getMessage(), E_USER_WARNING);
                }
                http_response_code('202');
            }else{
                http_response_code('404');
            }

        }else{
            http_response_code('400');
        }

    }

    /**

     */
    public function deleteUser():void
    {
        if(preg_match('/^[0-9]*$/', DELETE['id']) === 1){
            $param = [
                'id'=>intval(DELETE['id']),
            ];
            /**
             * Удаление папки с файлами пользователя
             */
            $query = 'SELECT initial_path FROM users WHERE id=:id';

            $sth = $this->conn->prepare($query);
            $sth->execute($param);
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);

            if(isset($result[0]['initial_path']))
            {
                $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $result[0]['initial_path'];

                $this->removeDirUser($fullLenPath);
                /**
                 * Удаление пользователя из базы данных
                 */
                $query = 'DELETE FROM users WHERE id=:id';
                $stmt = $this->connection->prepare($query);
                $stmt->execute($param);
                if( ! $stmt->rowCount() ){
                    http_response_code('401');
                }else{

                    http_response_code('204');
                }
            }else{
                http_response_code('404');
                die();
            }

        }else{
            http_response_code('400');
        }

    }
    /**
     * @param string $path
     */
    protected function removeDirUser($path)
    {
        if ($objs = glob($path . '/*')) {
            foreach($objs as $obj) {
                is_dir($obj) ? $this->removeDirUser($obj) : unlink($obj);
            }
        }
        rmdir($path);
    }
    /**
     *@return void
     */

    public function loginUser():void
    {

        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", GET['email']);
        $check += (strlen(GET['password']) > 6) ? 1 : 0;

        if($check === 2)
        {

            $param = [
                'user_email'=>GET['email'],
            ];

            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() > 0)
            {
                while ($row = $stmt->fetch(PDO::FETCH_LAZY))
                {

                    if(password_verify(GET['password'], $row->user_password)){

                        setcookie("sessionId",  session_id(), time() + 60, '/');

                        $_SESSION['role'] = $row->users_status;
                        $_SESSION['initialPath'] = $row->initial_path;

                        http_response_code('200');

                    }else{

                        http_response_code('401');

                    }
                }
            }else{
                http_response_code('401');
            }

        }

    }

    /**
     ** удалим сессии при выходе пользователя
     */
    public function logoutUser():void
    {

        setcookie("sessionId", '',time()-86400,'/');
        $_SESSION['role'] = '';
        $_SESSION['initialPath'] = '';
        http_response_code('204');
    }

    /**
     * @throws Exception
     */
    public function resetPasswordUser():void
    {

        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", GET['email']);

        if($check === 1) {
            $email = GET['email'];
            $param = [
                'user_email' => $email,
            ];
            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);
            if ($stmt->rowCount() > 0) {

                $mail = Configuration::get_instance();
                $mail = $mail->getPHPMail();
                $mail->addAddress($email, 'Александр');

                $mail->isHTML(true);
                $author = 'Администратор';
                $text = 'Ссылка для восстановления пароля:';
                $mail->Subject = $text;
                $mail->Body = "Имя: {$author}<br> Email: {$email}<br> Сообщение: " . nl2br($text);
                $mail->AltBody = "Имя: {$author}\r\n Email: {$email}\r\n Сообщение: {$text}";
                $mail->send();

                http_response_code('200');
            }else{
                http_response_code('401');
                die();
            }
        }

    }

}