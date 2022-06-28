<?php

require_once 'vendor/autoload.php';
require_once 'Config/Database.php';
require_once 'Config/Configuration.php';

function loaderEntities($className){

    require_once './Controllers/' . $className . '.php';
}

spl_autoload_register('loaderEntities');
