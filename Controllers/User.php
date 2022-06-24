<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class User extends exception
{

    // конструктор для соединения с базой данных
    public function __construct($db){
        $this->conn = $db;
    }

    public function listUsers(int $id=null):string //должен быть массив с пользователями
    {

        if(is_null($id)){

            $query = 'SELECT * FROM users';
            $stmt = $this->conn->prepare($query);
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
            $stmt = $this->conn->prepare($query);
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

    public function addUser($email, $password, $status, int $age, $firstName):void
    {
        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email);
        $check += (strlen($password) > 6) ? 1 : 0;
        $check += ($age <= 99) ? 1 : 0;
        $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', $firstName);
        $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', $status);

        if($check === 5)
        {
            $param = [
                'user_email'=>$email,
            ];
            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() === 0){
                $sth = $this->conn->prepare("INSERT INTO `users` SET `user_email` = :user_email, `user_password` = :user_password, `users_status` = :users_status, `age`= :age, `first_name`=:first_name");
                $sth->execute([
                    'user_email' => $email,
                    'user_password' => password_hash($password, PASSWORD_DEFAULT),
                    'users_status'=>$status,
                    'age'=>$age,
                    'first_name'=>$firstName,

                ]);
                http_response_code('201');
            }else{
                http_response_code('401');
            }
        }
    }

    public function updateUser(int $id, int $age, $firstName, $status):void
    {
        $query = "UPDATE users SET age = :age, first_name = :first_name, users_status = :users_status WHERE id = :id";
        $params = [
            'id' => $id,
            'age' => $age,
            'first_name'=> $firstName,
            'users_status'=>$status,
        ];

        try {

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

        }catch (PDOException $e){
            trigger_error($e->getMessage(), E_USER_WARNING);
        }
        http_response_code('202');
    }

    public function deleteUser(int $id):void
    {
        $param = [
            'id'=>$id,
        ];
        $query = 'DELETE FROM users WHERE id=:id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($param);
        if( ! $stmt->rowCount() ){
            http_response_code('401');
        }else{
            http_response_code('204');
        }

    }

    public function loginUser($email, $password):void
    {

        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email);
        $check += (strlen($password) > 6) ? 1 : 0;

        if($check === 2)
        {

            $param = [
                'user_email'=>$email,
            ];

            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() > 0)
            {
                while ($row = $stmt->fetch(PDO::FETCH_LAZY))
                {

                    if(password_verify($password, $row->user_password)){

                        setcookie("sessionId",  session_id(), time() + 60, '/');
                        if($row->users_status === 'admin'){
                            $_SESSION['role'] = $row->users_status;
                        }
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

    public function logoutUser():void
    {

        setcookie("sessionId", '',time()-86400,'/');
        $_SESSION['role'] = '';
    }

    public function resetPasswordUser($email):void
    {
//Load Composer's autoloader
        require 'vendor/autoload.php';

        $check = preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $email);

        if($check === 1) {

            $param = [
                'user_email' => $email,
            ];
            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);
            if ($stmt->rowCount() > 0) {

                //Create an instance; passing `true` enables exceptions
                $mail = new PHPMailer(true);

                $mail->isSMTP();
                $author = 'Администратор';
                $text = 'Ссылка для восстановления пароля:';
                //$mail->SMTPDebug = 1;
                $mail->Host = 'ssl://smtp.mail.ru';
                $mail->SMTPAuth = true;                                          //Send using SMTP
                //Enable SMTP authentication
                $mail->Username = 'chelae1@mail.ru';                     //SMTP username
                $mail->Password = 'GspLPbApTXMBxqrQeybm';                    //SMTP password
                $mail->SMTPSecure = 'SSL';
                $mail->Port = '465';

                $mail->CharSet = 'UTF-8';
                $mail->From = 'chelae1@mail.ru';  // адрес почты, с которой идет отправка
                $mail->FromName = 'Александр'; // имя отправителя
                $mail->addAddress($email, 'Александр');

                $mail->isHTML(true);

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