<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteea12d58de8bbf7e18a5ab6e47dd3955
{
    public static $prefixLengthsPsr4 = array (
        'i' => 
        array (
            'iutnc\\nrv\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'iutnc\\nrv\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticIniteea12d58de8bbf7e18a5ab6e47dd3955::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteea12d58de8bbf7e18a5ab6e47dd3955::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticIniteea12d58de8bbf7e18a5ab6e47dd3955::$classMap;

        }, null, ClassLoader::class);
    }
}
