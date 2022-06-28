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
    }

    /**
     * @param int|null $id
     * @return string
     */
    public function showUsersByAdmin(int $id=null):string
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
            }

            return json_encode($res);
        }
    }

    /**
     * @param int $id
     */
    public function delUserByAdmin(int $id):void
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
            http_response_code('202');
        }

    }

    /**
     * @param int $id
     * @param int $age
     * @param $firstName
     * @param $status
     */
    public function updateUserByAdmin(int $id, int $age, $firstName, $status):void
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

}