<?php

namespace Polly\Core;



use Dotenv\Dotenv;
use Polly\Exceptions\MissingConfigFileException;
use Polly\Helpers\FileSystem;

class Config
{
    private static array $items = [];

    private function __construct() { }

    public static function flush()
    {
        static::$items = [];
    }

    public static function all() : array
    {
        return static::$items;
    }

    public static function remove($key) : bool
    {
        if (static::exists($key))
        {
            unset(static::$items[$key]);
            return true;
        }
        return false;
    }

    public static function exists($key) : bool
    {
        return isset(static::$items[$key]);
    }

    public static function load(string $rootDir)
    {
        static::prepareDefaults($rootDir);

        //Include the path helpers
        require_once __DIR__ . '/../Helpers/GlobalsPath.php';

        //Load .env
        $dotenv = Dotenv::createImmutable($rootDir);
        $dotenv->load();

        $appConfig = static::get("path.config").'/'.'app.config.php';
        if(!FileSystem::fileExists($appConfig)) throw new MissingConfigFileException('app.config.php');
        static::combine(require $appConfig);
    }

    public static function prepareDefaults(string $rootDir)
    {
        static::push('path.root',       $rootDir);
        static::push('path.app',        $rootDir.'/app');
        static::push('path.config',     $rootDir.'/config');
        static::push('path.resources',  $rootDir.'/resources');
        static::push('path.public',     $rootDir.'/public');
        static::push('path.storage',    $rootDir.'/storage');

        static::push('path.locale',     static::get('path.resources').'/locale');
        static::push('path.views',      static::get('path.app').'/Views');
        static::push('path.views.base', 'Base');
    }

    public static function push($key, $value) : void
    {
        static::$items[$key] = $value;
    }

    public static function get($key, $fallback=null) : mixed
    {
        if (static::exists($key))
        {
            return static::$items[$key];
        }
        return $fallback;
    }

    public static function combine($array) : void
    {
        static::$items = $array + static::$items;
    }
}
