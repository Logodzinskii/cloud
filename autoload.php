<?php

require_once 'vendor/autoload.php';
require_once 'Config/cloud_config.php';

function loaderEntities($className){

    require_once './Controllers/' . $className . '.php';
}

spl_autoload_register('loaderEntities');
