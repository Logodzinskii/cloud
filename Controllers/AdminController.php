<?php

class AdminController extends UserController
{
    /**
     * @var
     */
    private $role;

    /**
     * При создании класса производится проверка на предмет
     * наличия регистрации пользователя с ролью admin.
     * Если SESSION['role'] = admin, то пользователь является
     * администратором и ему доступны методы class AdminController,
     * Если SESSION['role'] не существует или != admin, то возвращается код 401
     * и исключение.
     * @param $db
     * @throws Exception
     */
    public function __construct($db){
        $this->connection = $db;
        if(isset($_SESSION) && $_SESSION['role'] !== 'admin')
        {
            http_response_code(401);
            throw new Exception('session role не существует (необходима авторизация как администратор)');
        }
    }

    /**
     * Метод выдает результат в зависимости от запроса GET,
     * если он содержит параметр id, то из базы данных выбирается
     * значения соответствующие данному id.
     * Если запрос не содержит id, то выбираются все значения
     * @return string
     * @throws Exception
     */
    public function showUsersByAdmin():string
    {

        if(defined('GET') && !isset(GET['id'])) {

            $query = 'SELECT * FROM users';
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
        }elseif(defined('GET') && strlen(GET['id']) > 0){
            $param = [
                'id'=>Validate::validateId(GET['id']),
            ];
            $query = 'SELECT * FROM users WHERE id=:id';
            $stmt = $this->connection->prepare($query);
            $stmt->execute($param);

        }else{
            http_response_code(400);
            throw new Exception('Некорректный запрос GET showUsersByAdmin');
        }
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
            throw new Exception('Некорректный запрос GET');
        }
        return json_encode($res);
    }

    /**
     * В запросе DELETE необходимо передать следующие значения:
     * id = int,
     * @return void
     * @throws Exception
     */
    public function delUserByAdmin():void
    {
        if(defined('DELETE')){
            $param = [
                'id'=>Validate::validateId(DELETE['id']),
            ];

            $query = 'SELECT initial_path FROM users WHERE id=:id';

            $sth = $this->connection->prepare($query);
            $sth->execute($param);
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            if(isset($result[0]['initial_path']))
            {
                $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $result[0]['initial_path'];

                Validate::remove_dir($fullLenPath);

                $query = 'DELETE FROM users WHERE id=:id';
                $stmt = $this->connection->prepare($query);
                $stmt->execute($param);
                if(!$stmt->rowCount()){
                    http_response_code(401);
                }else{
                    http_response_code(204);
                }
            }else{
                http_response_code(404);
                throw new Exception('Некорректный запрос DELETE delUserByAdmin');
            }


        }else{
            http_response_code(400);
        }

    }

     /**
      * В теле запроса PUT необходимо передать следующие значения:
      * id = int,
      * age = int (0-99),
      * first_name = string,
      * status = string (admin/users),
      * @return void
      * @throws
     */
    public function updateUserByAdmin():void
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
                'first_name' => $firstName,
                'users_status' => $status,
            ];

            try {

                $stmt = $this->connection->prepare($query);
                $stmt->execute($params);

            } catch (PDOException $e) {
                trigger_error($e->getMessage(), E_USER_WARNING);
            }
            http_response_code(202);

        }else{
            http_response_code(404);
            throw new Exception('Некорректный запрос PUT updateUserByAdmin');
        }
    }

}