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
     * @param int|null $id
     * @return string
     */
    public function listUsers(int $id=null):string //должен быть массив с пользователями
    {

        if(is_null($id)){

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

        }else{
            $param = [
                'id'=>$id,
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

    }

    /**
     * @param string $email
     * @param string $password
     * @param string $status
     * @param int $age
     * @param string $firstName
     */
    public function addUser(string $email, string $password, string $status, int $age, string $firstName):void
    {
        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email);
        $check += (strlen($password) > 6) ? 1 : 0;
        $check += ($age <= 99) ? 1 : 0;
        $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', $firstName);
        $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', $status);

        if($check === 5)
        {
            $salt = rand(0,888);
            $initial_path = md5($email.$salt);
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
                    if(!file_exists('C:/wamp64/www/cloud/UsersClouds/'.$initial_path)){

                        mkdir('C:/wamp64/www/cloud/UsersClouds/'.$initial_path, 0777, true);

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
     * @param int $id
     * @param int $age
     * @param string $firstName
     * @param string $status
     */
    public function updateUser(int $id, int $age, string $firstName, string $status):void
    {
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
    }

    /**
     * @param int $id
     */
    public function deleteUser(int $id):void
    {
        $param = [
            'id'=>$id,
        ];
        $query = 'DELETE FROM users WHERE id=:id';
        $stmt = $this->connection->prepare($query);
        $stmt->execute($param);
        if( ! $stmt->rowCount() ){
            http_response_code('401');
        }else{
            http_response_code('204');
        }

    }

    /**
     * @param string $email
     * @param string $password
     */
    public function loginUser(string $email, string $password):void
    {

        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email);
        $check += (strlen($password) > 6) ? 1 : 0;

        if($check === 2)
        {

            $param = [
                'user_email'=>$email,
            ];

            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() > 0)
            {
                while ($row = $stmt->fetch(PDO::FETCH_LAZY))
                {

                    if(password_verify($password, $row->user_password)){

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
    }

    /**
     * @param string $email
     * @throws Exception
     */
    public function resetPasswordUser(string $email):void
    {

        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email);

        if($check === 1) {

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