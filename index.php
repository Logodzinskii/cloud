<?php
    // необходимые HTTP-заголовки
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");

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
            'GET'=>'UserController::listUsers',
            'POST'=>'UserController::addUser',
            'PUT'=>'UserController::updateUser',
            'DELETE'=>'UserController::deleteUser',
        ],
        '/user/login/'=>[
            'GET'=>'UserController::loginUser',
        ],
        '/user/logout/'=>[
            'GET'=>'UserController::logoutUser',
        ],
        '/user/reset_password/'=>[
            'GET'=>'UserController::resetPasswordUser',
        ],
        '/admin/user/'=>[
            'GET'=>'AdminController::showUsersByAdmin',
            'PUT'=>'AdminController::updateUserByAdmin',
            'DELETE'=>'AdminController::delUserByAdmin',
        ],
        '/file/'=>[
            'GET'=>'FileController::listFile',
            'POST'=>'FileController::addFile',
            'DELETE'=>'FileController::fileDelete',
            'PUT'=>'FileController::fileRename',
        ],
        '/directory/'=>[
            'GET'=>'DirectoryController::getInformationDirectory',
            'POST'=>'DirectoryController::addDirectory',
            'DELETE'=>'DirectoryController::deleteDirectory',
            'PUT'=>'DirectoryController::renameDirectory',
        ],

    ];

    function start_controller($urlList)
    {
        require_once 'autoload.php';
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // /admin/user/
        $method = parse_url($_SERVER['REQUEST_METHOD']); //GET
        $id = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY); // id=6
        $res = $urlList[$path];
        /**
         * $controller - string название Класса
         */
        $classNameAndMethod = preg_split('/::/', $res[$method["path"]]);

        session_start();

        $database = Database::get_instance();

        $db = $database->getConnection();
        /**
         * Подключим необходимый класс в контроллере
         */

        loaderEntities($classNameAndMethod[0]);
        $className = $classNameAndMethod[0];
        $method = $classNameAndMethod[1];
        /**
         * Создадим экземпляр класса по названию, полученному из переменной $path
         */

        $controller = new $className($db);
        /**
         * Если существуют GET POST и тд
         * занесем их данные в константы для дальнейшего использования в методах класса
         */
        if(count($_POST) !== 0){
            define("POST", $_POST);
            var_dump(POST);
        }elseif (count($_GET) !== 0){
            define("GET", $_GET);
            print_r(GET);
        }elseif(strlen(file_get_contents('php://input'))>0 && parse_url($_SERVER['REQUEST_METHOD'])['path'] === 'PUT'){
            parse_str(file_get_contents('php://input'), $_PUT);
            define("PUT", $_PUT);
            print_r(PUT);
            var_dump(parse_url($_SERVER['REQUEST_METHOD'])['path']);
        }elseif(strlen(file_get_contents('php://input'))>0 && parse_url($_SERVER['REQUEST_METHOD'])['path'] === 'DELETE'){
            parse_str(file_get_contents('php://input'), $_DELETE);
            define("DELETE", $_DELETE);
            print_r(DELETE);
            var_dump(parse_url($_SERVER['REQUEST_METHOD'])['path']);
        }else{
            http_response_code('404');
        }

    if (count($_FILES) !== 0){
    define("FILES", $_FILES);
        print_r(FILES);
    }
        /**
         * Вызовем метод для класса
         */

        print_r(json_decode(json_encode($controller->$method(),true)));
    }

    start_controller($urlList);