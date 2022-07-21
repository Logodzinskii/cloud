<?php

class Validate
{


    /**
     * Функция проверяет на наличие запрещенных символов \.|/:?"<>
     * @param $string
     * @return mixed|void
     */
    public static function checkDirName($string)
    {
        if((strlen($string > 0)) && (preg_match('/\.|\:|>|<|\\|\||\?|#/', $string, $output_array) === 0))
        {

            return $string;

        }else{

            http_response_code('400');
            die();

        }
    }
    /**
     * Функция проверяет на наличие запрещенных символов \|/:?"<>
     * @param $string
     * @return mixed|void
     */
    public static function checkFileName($string)
    {
        if((strlen($string > 0)) && (preg_match('/\|\:|>|<|\\|\||\?|#|\//', $string, $output_array) === 0))
        {

            return $string;

        }else{

            http_response_code('400');
            die();

        }
    }

    /**
     * @param string $path
     * @return string
     */
    public static function isDirectory(string $path):string
    {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/UsersClouds/' .  $_SESSION['initialPath'] . '/' . $path;

        if(is_dir($dir)) {
            return $dir;

        }else{

            http_response_code('404');
            die();

        }

    }

    public static function validateEmail(string $string): string
    {
        if(preg_match("/^(?:[a-z0-9]+(?:[-_.]?[a-z0-9]+)?@[a-z0-9_.-]+(?:\.?[a-z0-9]+)?\.[a-z]{2,5})$/i", $string) === 1)
        {
            return $string;
        }else{
            http_response_code('400');
            die();
        }

    }

    public static function validatePassword(string $password): string
    {
        if(strlen($password) > 6)
        {
            return $password;
        }else{
            http_response_code('400');
            die();
        }
    }

    public static function validateAge(string $age): int
    {
        if(intval($age) <= 99 && intval($age) > 0)
        {
           return $age;
        }else{
            http_response_code('400');
            die();
        }
    }

    public static function validateText(string $text): string
    {
        if(preg_match('/^[_0-9A-Za-zА-Яа-пр-яЁё]+$/', $text) === 1)
        {
            return $text;
        }else{
            http_response_code('400');
            die();
        }
    }

    public static function validateId(string $id): int
    {
        if(preg_match('/^[0-9]*$/', intval($id)) === 1)
        {
            return $id;

        }else{
            http_response_code('400');
            die();
        }
    }

    /**
     * @param string $dir
     * @return void
     */
    public static function remove_dir(string $dir):void
    {
        if ($objs = glob($dir . '/*'))
        {

            foreach($objs as $obj)
            {

                is_dir($obj) ? self::remove_dir($obj) : unlink($obj);

            }

        }
        rmdir($dir);
    }
}