<?php

namespace Polly\Core;

use Polly\Helpers\FileSystem;

class Autoload
{
    private static array $paths = [];

    private function __construct() { }

    public static function getKnownPaths() : array
    {
        return static::$paths;
    }

    public static function load($class)
    {
        $classPath = DIRECTORY_SEPARATOR.$class . '.php';
        foreach(static::$paths as $path)
        {
            $pathToFile = str_replace("\\", "/", $path.$classPath);
            $pathToFile = str_replace("//", "/", $pathToFile);
            $pathToFile = str_replace("/App/", "/app/", $pathToFile);

            //echo $pathToFile."<br />";
            if(FileSystem::fileExists($pathToFile))
            {
                require_once $pathToFile;
                return true;
            }
        }
        return false;
    }

    public static function register(string $basePath) : void
    {
        static::addAbsolute($basePath);
        spl_autoload_register('Polly\Core\Autoload::load');
    }

    public static function addAbsolute($path)
    {
        if($path && is_dir($path))
        {
            static::$paths[] = $path;
        }
    }

}
