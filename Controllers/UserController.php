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
     * Метод выдает результат в зависимости от запроса GET,
     * если он содержит параметр id, то из базы данных выбирается
     * значения соответствующие данному id.
     * Если запрос не содержит id, то выбираются все значения
     * @return string email, age, first_name, users_status
     * @throws Exception
     */
    public function listUsers():string
    {

        if(defined('GET') && !isset(GET['id'])){

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
                http_response_code(200);
            }else{
                http_response_code(404);
                return false;
            }

            return json_encode($res);

        }elseif(defined('GET') && strlen(GET['id']) > 0){

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
                http_response_code(200);
            }else{
                http_response_code(404);
                return  false;
            }

            return json_encode($res);
        }
        else{
            http_response_code(404);
            throw new Exception('Неверный запрос GET listUsers', 0);
        }

    }

    /**
     * В теле запроса POST необходимо передать слудющие значения:
     * email = string, (в виде example@mail.ru)
     * password = string (количество символов не менее 6),
     * age = int (0-99),
     * first_name = string,
     * status = admin / users
     * @return void
     * @throws Exception
     */
    public function addUser():void
    {
        if(defined('POST')){
            $email = Validate::validateEmail(POST['email']);
            $password = Validate::validatePassword(POST['password']);
            $age= Validate::validateAge(intval(POST['age']));
            $firstName = Validate::validateText(POST['first_name']);
            $status = Validate::validateText(POST['status']);
            $salt = rand(0,888);
            $initial_path = md5($email.$salt);
            $param = [
                'user_email'=>$email,
            ];
            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);
            if($stmt->rowCount() === 0) {
                $sth = $this->connection->prepare("INSERT INTO `users` SET `user_email` = :user_email, `user_password` = :user_password, `users_status` = :users_status, `age`= :age, `first_name`=:first_name, `initial_path`=:initial_path");
                $sth->execute([
                    'user_email' => $email,
                    'user_password' => password_hash($password, PASSWORD_DEFAULT),
                    'users_status' => $status,
                    'age' => $age,
                    'first_name' => $firstName,
                    'initial_path' => $initial_path,
                ]);

                    if(!mkdir($_SERVER['DOCUMENT_ROOT'] . '/cloud/UsersClouds/' . $initial_path, 0777, true))
                    {

                        file_put_contents('direrror.txt', 'error');

                    }

                http_response_code(201);
            }else{
                http_response_code(400);
            }
        }else{
            throw new Exception('Неверный запрос POST addUser', 0);
        }

    }

    /**
     * В теле запроса PUT необходимо передать следующие значения:
     * id = int,
     * age = int (0-99),
     * first_name = string,
     * status = string (admin/users),
     * @return void
     * @throws Exception
     */
    public function updateUser():void
    {
        if(defined('PUT')){

            $id = Validate::validateId(PUT['id']);
            $age = Validate::validateAge(PUT['age']);
            $firstName = Validate::validateText(PUT['first_name']);
            $status = Validate::validateText(PUT['status']);

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
                http_response_code(202);

            }catch (PDOException $e){
                trigger_error($e->getMessage(), E_USER_WARNING);
            }

        }else{
            http_response_code(400);
            throw new Exception('Неверный запрос PUT updateUser', 0);
        }

    }

    /**
     * В запросе DELETE необходимо передать следующие значения:
     * id = int,
     * @return void
     * @throws Exception
     */
    public function deleteUser():void
    {
        if(defined('DELETE')){
            $param = [
                'id'=>Validate::validateId(DELETE['id']),
            ];
            /**
             * Удаление папки с файлами пользователя
             */
            $query = 'SELECT initial_path FROM users WHERE id=:id';

            $sth = $this->connection->prepare($query);
            $sth->execute($param);
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);

            if(isset($result[0]['initial_path']))
            {
                $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $result[0]['initial_path'];

                Validate::remove_dir($fullLenPath);
                /**
                 * Удаление пользователя из базы данных
                 */
                $query = 'DELETE FROM users WHERE id=:id';
                $stmt = $this->connection->prepare($query);
                $stmt->execute($param);
                if( ! $stmt->rowCount() ){
                    http_response_code(401);
                }else{

                    http_response_code(204);
                }
            }else{
                http_response_code(404);
                throw new Exception('Неверный запрос DELETE deleteUser', 0);
            }

        }else{
            http_response_code(400);
        }

    }

    /**
     * В запросе GET необходимо передать следующие значения:
     * email = string, (в виде example@mail.ru)
     * password = string (количество символов не менее 6),
     * @return void
     * @throws Exception
     */

    public function loginUser():void
    {

        if(defined('GET'))
        {
            $email = Validate::validateEmail(GET['email']);
            $password = Validate::validatePassword(GET['password']);

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

                        http_response_code(200);

                    }else{

                        http_response_code(401);

                    }
                }
            }else{
                http_response_code(401);
            }

        }else{
            throw new Exception('Неверный запрос GET loginUser', 0);
        }

    }

    /**
     ** удалим сессии при выходе пользователя
     * @return void
     */
    public function logoutUser():void
    {

        setcookie("sessionId", '',time()-86400,'/');
        $_SESSION['role'] = '';
        $_SESSION['initialPath'] = '';
        http_response_code(204);
    }

    /**
     * * В запросе GET необходимо передать следующие значения:
     * email = string, (в виде example@mail.ru)
     * @throws Exception
     */
    public function resetPasswordUser():void
    {

        if(defined('GET')) {
            $email = Validate::validateEmail(GET['email']);
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

                http_response_code(200);
            }else{
                http_response_code(401);

            }
        }else{
            throw new Exception('Неверный запрос GET resetPasswordUser', 0);
        }

    }
    /**
     * В запросе GET необходимо передать следующие значения:
     * email = string, (в виде example@mail.ru)
     * @return void
     * @throws \Exception
     */

    public function findUserByEmail():void
    {
        if(defined('GET')) {
            $email = Validate::validateEmail(GET['email']);
            $param = [
                'user_email' => $email,
            ];
            $query = 'SELECT * FROM users WHERE user_email=:user_email';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_LAZY))
                {
                    $foundEmail = $row->user_email;
                }

            }else{
                http_response_code(404);
                throw new Exception('Пользователь' . $email . ' не найден.', 0);

            }
            http_response_code(200);

        }else{
            http_response_code(400);
            throw new Exception('Неверный запрос GET findUserByEmail', 0);
        }
    }

}