<?php

namespace Polly\Core;


use Exception;
use Locale;
use Polly\Exceptions\InvalidBasePathException;
use Polly\Helpers\Arr;
use Polly\Helpers\FileSystem;
use Polly\ORM\EntityManager;

class App
{
    private static string $basePath;
    private static ?string $locale = null;

    private function __construct() { }

    public static function initialize(string $basePath)
    {
        try
        {
            static::loadBasePath($basePath);
            static::loadEnvironment();
            static::prepareCore();
        }
        catch(Exception $e)
        {
            self::handleException($e);
        }
    }

    private static function loadBasePath(string $basePath) : void
    {
        if(!FileSystem::directoryExists($basePath))
        {
            throw new InvalidBasePathException($basePath);
        }

        static::$basePath = trim($basePath);

        Autoload::register(static::getBasePath());
        Config::load(static::getBasePath());
    }

    public static function getBasePath() : string
    {
        return static::$basePath;
    }

    private static function loadEnvironment()
    {
        //Error reporting
        ini_set('display_errors', static::isDebug());
        error_reporting(Config::get('error_reporting', E_ALL));

        //Set the defined locale
        static::setLocale(Config::get('locale', Locale::getDefault()));

        //Set the defined timezone
        if(Config::exists('timezone'))
            static::setTimezone(Config::get('timezone'));
    }

    public static function isDebug() : bool
    {
        return Config::get("debug", false);
    }

    public static function setTimezone(string $timezone)
    {
        date_default_timezone_set($timezone);
    }

    private static function prepareCore()
    {
        Logger::prepare();
        Shutdown::prepare();
        Router::prepare();
        Database::prepare();
        EntityManager::prepare();
    }

    public static function environment(string $key, ?string $fallback=null) : ?string
    {
        return $_ENV[$key] ?? $fallback;
    }

    public static function getLocale()
    {
        return static::$locale;
    }

    public static function setLocale(string $locale)
    {
        $locales        = Arr::shiftKeys(Arr::toArray(static::environment('LOCALES', ''), ['|', '=']));
        $machineLocale  = $locales[$locale] ?? $locale;

        static::$locale = $locale;
        if(!setlocale(LC_TIME, $machineLocale))
        {
            setlocale(LC_TIME, Locale::getDefault());
        }
    }

    public static function getTimezone() : string
    {
        return date_default_timezone_get();
    }

    public static function handleException(Exception $exception) : void  { ExceptionHandler::process($exception);  }

    public static function handleRequest() : void { RequestHandler::process(); }

    public static function handleResponse(Response $response) : void { ResponseHandler::process($response); }

    public static function handleFatal(Exception $exception) : void { ResponseHandler::fatal($exception); }

}
