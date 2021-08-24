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
    return Router::getCurrentBaseUrl().($path);
}

function env($key, $fallback=null) : ?string
{
    return App::environment($key, $fallback);
}

function translate($key) : string
{
    return Translator::translate($key);
}
