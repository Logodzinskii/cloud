<?php

class AdminController extends UserController
{
    /**
     * @var
     */
    private $role;

    /**
     * @param $db
     */
    public function __construct($db){
        $this->conn = $db;
        if($_SESSION['role'] !== 'admin')
        {
            http_response_code('401');
            die();
        }
    }

    /**

     * @return string
     */
    public function showUsersByAdmin():string
    {

        if(!defined('GET')){

            $query = 'SELECT * FROM users';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

        }elseif(preg_match('/^[0-9]*$/',GET['id']) === 1){

            $param = [
                'id'=>GET['id'],
            ];
            $query = 'SELECT * FROM users WHERE id=:id';
            $stmt = $this->conn->prepare($query);
            $stmt->execute($param);

        }else{
            http_response_code('400');
            die();
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
        }else{
            http_response_code('404');
            die();
        }
        return json_encode($res);
    }

    /**

     */
    public function delUserByAdmin():void
    {
        if(defined('DELETE') && preg_match('/^[0-9]*$/', DELETE['id']) === 1){
            $param = [
                'id'=>DELETE['id'],
            ];

            $query = 'SELECT initial_path FROM users WHERE id=:id';

            $sth = $this->conn->prepare($query);
            $sth->execute($param);
            $result = $sth->fetchAll(PDO::FETCH_ASSOC);
            if(isset($result[0]['initial_path']))
            {
                $fullLenPath = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' . $result[0]['initial_path'];

                $this->removeDirUser($fullLenPath);

                $query = 'DELETE FROM users WHERE id=:id';
                $stmt = $this->conn->prepare($query);
                $stmt->execute($param);
                if(!$stmt->rowCount()){
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
    public function updateUserByAdmin():void
    {
        if(defined('PUT')){
            $check = preg_match('/^[0-9]*$/', PUT['id']);
            $check += (intval(PUT['age']) <= 99) ? 1 : 0;
            $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', PUT['first_name']);
            $check += preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', PUT['status']);

            if($check === 4) {

                $id = intval(PUT['id']);
                $age = intval(PUT['age']);
                $firstName = PUT['first_name'];
                $status = PUT['status'];

                $query = "UPDATE users SET age = :age, first_name = :first_name, users_status = :users_status WHERE id = :id";
                $params = [
                    'id' => $id,
                    'age' => $age,
                    'first_name' => $firstName,
                    'users_status' => $status,
                ];

                try {

                    $stmt = $this->conn->prepare($query);
                    $stmt->execute($params);

                } catch (PDOException $e) {
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

}