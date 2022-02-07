<?php

namespace Polly\Core;


use Polly\Helpers\Str;

class Request
{
    private function __construct() { }

    public static function get(string $key, mixed $fallback=null) : mixed
    {
        return $_GET[$key] ?? $fallback;
    }

    public static function post(string $key, mixed $fallback=null) : mixed
    {
        return $_POST[$key] ?? $fallback;
    }

    public static function cookie(string $key, mixed $fallback=null) : mixed
    {
        return $_COOKIE[$key] ?? $fallback;
    }

    public static function body()
    {
        return file_get_contents('php://input');
    }


    public static function getUrl() : string
    {
        $protocol = (static::server('HTTPS') && static::server('HTTPS') != "off") ? "https" : "http";
        return $protocol.'://'.static::server('SERVER_NAME').strtok(static::server('REQUEST_URI'), '?');
    }

    public static function server(string $key) : mixed
    {
        return $_SERVER[$key] ?? null;
    }

    public static function getFullUrl() : string
    {
        $protocol = (static::server('HTTPS') && static::server('HTTPS') != "off") ? "https" : "http";
        return $protocol.'://'.static::server('SERVER_NAME').static::server('REQUEST_URI');
    }

    public static function expectJson() : bool
    {
        return Str::contains(strtolower(static::headers()['Accept'] ?? ""), 'application/json');
    }

    public static function headers() : mixed
    {
        return getallheaders();
    }

    public static function isAjax() : bool
    {
        if(isset(static::headers()['Polly-Ajax-Request'])) return true;
        return !empty(static::server('HTTP_X_REQUESTED_WITH')) && strtolower(static::server('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest';
    }

}
