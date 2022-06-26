<?php
    // необходимые HTTP-заголовки
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

    require_once 'autoload.php';
    include_once "Config/Database.php";

function myErrorHandler($errno, $errstr, $errfile, $errline)
{

    if (!(error_reporting() & $errno)) {
        // Этот код ошибки не включён в error_reporting,
        // так что пусть обрабатываются стандартным обработчиком ошибок PHP
        return false;
    }

    // может потребоваться экранирование $errstr:
    $errstr = htmlspecialchars($errstr);

    switch ($errno) {
        case E_USER_ERROR:
            http_response_code('404' . $errstr);


        case E_USER_WARNING:
            //echo "<div class='reportMessage1' style='display: block; width: 30vw; height: auto; border: solid 1px pink; background: pink; color: black;'>";

           http_response_code('404' . $errstr);
            //echo "</b></div>";
            //echo "<b>Пользовательское ПРЕДУПРЕЖДЕНИЕ</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            //echo "<div class='reportMessage1' style='display: block; width: 30vw; height: auto; border: solid 1px pink; background: lightgreen; color: black;'>";
            http_response_code('404' . $errstr);
            //echo "</b></div>";
            break;

        default:
            //echo "<div class='reportMessage1' style='display: block; width: 30vw; height: auto; border: solid 1px pink; background: pink; color: black;'>";
            http_response_code('404' . $errstr);
            //echo "</b></div>";
            break;
    }

    /* Не запускаем внутренний обработчик ошибок PHP */
    return true;
}

set_error_handler("myErrorHandler");

    $urlList = [
        '/user/'=>[
            'GET'=>'User::listUsers',
            'POST'=>'User::addUser',
            'PUT'=>'User::updateUser',
            'DELETE'=>'User::deleteUser',
        ],
        '/user/login/'=>[
            'GET'=>'User::loginUser',
        ],
        '/user/logout/'=>[
            'GET'=>'User::logoutUser',
        ],
        '/user/reset_password/'=>[
            'GET'=>'User::resetPasswordUser',
        ],
        '/admin/user/'=>[
            'GET'=>'Admin::showUsersByAdmin',
            'PUT'=>'Admin::updateUserByAdmin',
            'DELETE'=>'Admin::delUserByAdmin',
        ],
        '/file/'=>[
            'GET'=>'File::listFile',
            'POST'=>'File::addFile',
            'DELETE'=>'Admin::delUserByAdmin',
        ],

    ];

    $method = $_SERVER['REQUEST_METHOD'];
    $uri    = $_SERVER['REQUEST_URI'];

    if(strpos($uri, '?') > 0){

       $uri = substr($uri, 0, strpos($uri, '?'));//очистим uri от гет запроса

    }elseif (strpos($uri, 'file') > 0)
    {
       $pathOld = $uri;
       $uri = substr($uri, 0, 6);//очистим uri от пути к папкам и файлам пользователя

    }else{

    }

    $classMethod = $urlList[$uri][$method]; //получим из массива $urlList имя метода для class
    $className = substr($classMethod, 0, strpos($classMethod,':'));
    $met = substr($classMethod, strpos($classMethod,'::')+2);
    session_start();

    $database = new Database();
    $db = $database->getConn();
    $controller = new $className($db);

    if($className === 'Admin' && ($_SESSION['role'] === 'admin'))
    {
        loaderEntities($className);//подключим контроллер
        echo 'admin';

    }elseif($className === 'User'){

        loaderEntities($className);//подключим контроллер
        echo 'users';

    }elseif($className === 'File' && $method === 'GET'){

        loaderEntities($className);//подключим контроллер
        $path = substr($pathOld,5);

        print_r(json_decode(json_encode($controller->$met($path)),true));
        return false;

    }elseif($className === 'File' && $method === 'POST'){

        loaderEntities($className);//подключим контроллер
        $path = substr($pathOld,5);

        print_r(json_decode(json_encode($controller->$met($path, $_FILES)),true));
        return false;

    }else{
        http_response_code('500');
        return false;
    }

    switch ($method)
    {
        case 'GET':

            if(isset($_GET['id'])){

                print_r(json_decode(json_encode($controller->$met($_GET['id'])),true));

            }elseif(isset($_GET['email']) && isset($_GET['password'])){
                //session_start();
                print_r(json_decode(json_encode($controller->$met($_GET['email'], $_GET['password']),  true)));

            }elseif(isset($_GET['email'])){

                print_r(json_decode(json_encode($controller->$met($_GET['email'])),true));

            }else{

                print_r(json_decode(json_encode($controller->$met()),true));

            }
            break;
        case 'POST':

             $id = isset($_POST['id']) ? $_POST['id'] : null;
             if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['age']) && isset($_POST['status']) && isset($_POST['first_name'])){

                print_r(json_decode(json_encode($controller->$met($_POST['email'], $_POST['password'], $_POST['status'], $_POST['age'], $_POST['first_name']),true)));

             }elseif(isset($_POST['filename']))
             {
                 //проверить это form-data?

                 print_r(json_decode(json_encode($controller->$met($_POST['filename']))));

             }else{

                return false;

             }
             break;
        case 'PUT':

             parse_str(file_get_contents('php://input'), $_PUT);

             $id = isset($_PUT['id']) ? $_PUT['id'] : null;

             print_r(json_decode(json_encode($controller->$met($_PUT['id'], $_PUT['age'], $_PUT['first_name'], $_PUT['status']),true)));

             break;
        case 'DELETE':

             parse_str(file_get_contents('php://input'), $_DELETE);

             $id = isset($_DELETE['id']) ? $_DELETE['id'] : null;
             print_r(json_decode(json_encode($controller->$met($_DELETE['id'])),true));
             break;
        }

        header('Content-Type: application/json; charset=utf-8');

        //return http_response_code('200');