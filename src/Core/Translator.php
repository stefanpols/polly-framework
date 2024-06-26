<?php

namespace Polly\Core;

use Exception;
use Polly\Exceptions\MissingConfigKeyException;
use Polly\Helpers\FileSystem;

class Translator
{
    private static array $cachedFiles = [];

    private function __construct() { }

    public static function translate(string $key, string $locale = null)
    {
        $locale = $locale ?? App::getLocale();

        if(!array_key_exists($locale, static::$cachedFiles))
            static::addLocaleFile($locale);

        return static::searchKey($key,static::$cachedFiles[$locale]);
    }


    public static function all(string $locale = null)
    {
        $locale = $locale ?? App::getLocale();

        if(!array_key_exists($locale, static::$cachedFiles))
            static::addLocaleFile($locale);

        return static::$cachedFiles[$locale];
    }



    public static function findKey(string $value, string $locale = null)
    {
        $locale = $locale ?? App::getLocale();

        if(!array_key_exists($locale, static::$cachedFiles))
            static::addLocaleFile($locale);

        return static::searchValue($value,static::$cachedFiles[$locale]);
    }

    private static function addLocaleFile(string $locale)
    {
        self::$cachedFiles[$locale] = [];

        if(!Config::exists('path.locale'))
            throw new MissingConfigKeyException('path.locale');

        $localeDirectory = Config::get('path.locale');
        $localeFilePath = $localeDirectory.'/'.$locale.'.json';
        if(FileSystem::fileExists($localeFilePath))
        {
            try
            {
                $localeFile = file_get_contents($localeFilePath);
                $localeFileArray = json_decode($localeFile, true);
                if($localeFileArray === null || $localeFileArray === false)
                    throw new Exception();

                self::$cachedFiles[$locale] = $localeFileArray;
            }
            catch(Exception)
            {
                //It's oke, the key for the locale translation will be returned instead.
            }
        }
    }


    private static function searchKey(string $key, array $translations)
    {
        return $translations[$key] ?? $key;
    }

    private static function searchValue(string $value, array $translations)
    {
        return array_search($value, $translations) ?? null;
    }
}
