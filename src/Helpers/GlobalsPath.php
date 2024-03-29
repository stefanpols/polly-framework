<?php


use Polly\Core\App;
use Polly\Core\Config;
use Polly\Core\Router;
use Polly\Core\Translator;

function storage_path($path = '') : string
{
    return Config::get('path.storage').($path ? '/'.$path : $path);
}

function site_url($path = '') : string
{
    return Config::get("site_url").($path);
}

function env($key, $fallback=null) : ?string
{
    return App::environment($key, $fallback);
}

function config($key, $fallback=null) : ?string
{
    return Config::get($key, $fallback);
}

function translate($key) : string
{
    return Translator::translate($key);
}
