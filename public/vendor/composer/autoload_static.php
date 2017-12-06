<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitab185a8445c5417341271d5ce3ace61b
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

    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'Httpful' => 
            array (
                0 => __DIR__ . '/..' . '/nategood/httpful/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitab185a8445c5417341271d5ce3ace61b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitab185a8445c5417341271d5ce3ace61b::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitab185a8445c5417341271d5ce3ace61b::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
