<?php

class Configuration
{
    public $phpMail;
    static private $_ins = NULL;

    static public function get_instance()
    {
        if(self::$_ins instanceof self)
        {
            return self::$_ins;
        }
        return self::$_ins = new self;
    }

    public function __construct()
    {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        //$mail->SMTPDebug = 1;
        $mail->Host = 'ssl://smtp.mail.ru';
        $mail->SMTPAuth = true;                                          //Send using SMTP
        //Enable SMTP authentication
        $mail->Username = '';                     //SMTP username
        $mail->Password = '';                    //SMTP password
        $mail->SMTPSecure = 'SSL';
        $mail->Port = '465';

        $mail->CharSet = 'UTF-8';
        $mail->From = '';  // адрес почты, с которой идет отправка
        $mail->FromName = 'Александр'; // имя отправителя

        $this->phpMail = $mail;

    }

    private function __clone()
    {

    }

    /**
     * @return mixed
     */
    public function getPHPMail()
    {
        return $this->phpMail;
    }

}
