<?php

namespace Polly\Core;

use Polly\Exceptions\EmptyAuthenticationHandlerException;
use Polly\Interfaces\IAuthenticationAgent;
use Polly\Interfaces\IAuthenticationModel;

class Authentication
{
    private static ?IAuthenticationAgent $authHandler = null;

    private function __construct() { }

    public static function setHandler(IAuthenticationAgent $authHandler) : void
    {
        static::$authHandler = $authHandler;
    }

    public static function login(string $username, string $password) : bool
    {
        return static::getHandler()->login($username, $password);
    }

    public static function getHandler() : IAuthenticationAgent
    {
        if(!static::$authHandler) throw new EmptyAuthenticationHandlerException();
        return static::$authHandler;
    }

    public static function logout() : bool
    {
        return static::getHandler()->logout();
    }

    public static function check() : bool
    {
        return static::getHandler()->check();
    }

    public static function user() : IAuthenticationModel
    {
        return static::getHandler()->user();
    }

    public static function unauthenticated()
    {
        static::getHandler()->unauthenticated();
    }
}
