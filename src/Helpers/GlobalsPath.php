<?php


use Polly\Core\App;
use Polly\Core\Config;
use Polly\Core\Router;
use Polly\Core\Translator;

function storage_path($path = '') : string
{
    return Config::get('path.storage').($path ? '/'.$path : $path);
}

function site_url($path = '', $targetLocale=null, $relative=false) : string
{
    if(!$targetLocale) $targetLocale = App::getLocale();
    $locale = ($targetLocale != 'nl') ? $targetLocale.'/' : "";
    return ($relative ? "" : Router::getCurrentBaseUrl()).$locale.($path);
}

function portal_url($path = '') : string
{
    return Config::get().($path);
}

function env($key, $fallback=null) : ?string
{
    return App::environment($key, $fallback);
}

function config($key, $fallback=null) : ?string
{
    return Config::get($key, $fallback);
}

function translate($key,$locale=null) : string
{
    return Translator::translate($key,$locale);
}


function translate_by_value($value,$locale=null) : string
{
    $key = Translator::findKey($value);
    return Translator::translate($key,$locale);
}
