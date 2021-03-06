<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit404c6fa0fd792dd96abcfe021c6b1409
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit404c6fa0fd792dd96abcfe021c6b1409::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit404c6fa0fd792dd96abcfe021c6b1409::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit404c6fa0fd792dd96abcfe021c6b1409::$classMap;

        }, null, ClassLoader::class);
    }
}
