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

            file_put_contents('EuserError.log', $errstr . ' Файл - ' . $errfile . ' Строка - ' . $errline . ' Код ошибки- ' . $errno);
            die();

        case E_USER_WARNING:
            file_put_contents('EuserError.log', $errstr . ' Файл - ' . $errfile . ' Строка - ' . $errline . ' Код ошибки - ' . $errno);
            break;

        case E_USER_NOTICE:
            file_put_contents('EuserError.log', $errstr . ' Файл - ' . $errfile . ' Строка - ' . $errline . ' Код ошибки - ' . $errno);
            break;

        default:
            file_put_contents('EuserError.log', $errstr . ' Файл - ' . $errfile . ' Строка - ' . $errline . ' Код ошибки - ' . $errno);
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
        '/user/search/'=>[
            'GET'=>'UserController::findUserByEmail',
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
        '/files/share/'=>[
            'GET'=>'FileController::fileSharedListUsers',
            'DELETE'=>'FileController::deleteAccessToSharedFile',
            'PUT'=>'FileController::addFileAccessToSharedFile',
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

        if (!array_key_exists($path, $urlList)) {
            http_response_code('400');
            throw new Exception('Такого метода ' . $path . ' не существует');

        }

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

        if(count($_POST) > 0){

            define("POST", $_POST);

        }else{

            http_response_code('404');

        }
        if(count($_GET) > 0){

            define("GET", $_GET);

        }else{

            http_response_code('404');

        }
        if(parse_url($_SERVER['REQUEST_METHOD'])['path'] === 'PUT'){

            define('PUT', json_decode(file_get_contents('php://input'),true));

        }else{

            http_response_code('404');

        }
        if(parse_url($_SERVER['REQUEST_METHOD'])['path'] === 'DELETE'){

            parse_str(file_get_contents('php://input'), $_DELETE);
            define("DELETE", $_DELETE);

        }else{

            http_response_code('404');

        }

        if (count($_FILES) !== 0){
        define("FILES", $_FILES);

        }
        /**
         * Вызовем метод для класса
         */

        print_r(json_decode(json_encode($controller->$method(),true)));
    }

try{
    start_controller($urlList);
}catch (Exception $e){
        file_put_contents('log_exept.txt', $e->getMessage(). $e->getCode(). $e->getFile(). $e->getLine());
}
